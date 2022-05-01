<?php

namespace App\Services;

use App\Components\DataComponent;
use App\Models\Database;
use App\Models\DatabaseAccount;
use App\Models\DatabaseAttempt;
use App\Models\DatabaseImport;
use App\Models\DatabaseImportAction;
use App\Repositories\DatabaseAccountRepository;
use App\Repositories\DatabaseAttemptRepository;
use App\Repositories\DatabaseImportActionRepository;
use App\Repositories\DatabaseImportRepository;
use App\Repositories\DatabaseRepository;
use App\Repositories\UserGroupRepository;
use App\Repositories\UserRepository;
use App\Repositories\WebsiteRepository;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use MongoDB\BSON\UTCDateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use stdClass;


class DatabaseService {


    public static function delete($request) {

        $result = new stdClass();
        $result->response = "Failed to delete database data";
        $result->result = false;

        $databaseById = DatabaseRepository::findOneById($request->id, $request->website["id"]);

        if(!empty($databaseById)) {

            DatabaseRepository::delete($databaseById);

            $result->response = "Database data deleted";
            $result->result = true;

        } else {

            $result->response = "Database doesn't exist";

        }

        return $result;

    }


    public static function findData($request) {

        $result = new stdClass();
        $result->response = "Failed to find database data";
        $result->result = false;

        $account = DataComponent::initializeAccount($request);

        $result->websites = WebsiteRepository::findByNucodeStatus($account->nucode, "Active");

        return $result;

    }


    public static function findTable($request) {

        $result = new stdClass();
        $result->draw = $request->draw;

        $database = new Database();
        $database->setTable("database_" . $request->columns[3]["search"]["value"]);

        $columns = $request->columns;
        unset($columns[3]);

        $defaultOrder = ["created.timestamp"];
        $databases = DataComponent::initializeTableQuery($database, DataComponent::initializeObject($columns), DataComponent::initializeObject($request->order), $defaultOrder);

        $result->recordsTotal = $databases->count("_id");
        $result->recordsFiltered = $result->recordsTotal;

        $result->data = $databases->forPage(DataComponent::initializePage($request->start, $request->length), $request->length)->get();

        return $result;

    }


    private static function generateDefaultName($websiteId) {

        $result = [
            "number" => 0,
            "prefix" => "Database " . date("d F Y")
        ];

        $databasesLikeName = DatabaseRepository::findOneLikeName("Database " . date("d F Y"), $websiteId);

        if(!empty($databasesLikeName)) {

            $names = explode(" ", $databasesLikeName->name);

            if(count($names) == 5) {

                $result = [
                    "number" => (int)$names[4],
                    "prefix" => $names[0] . " " . $names[1] . " " . $names[2] . " " . $names[3]
                ];

            }

        }

        return $result;

    }


    private static function importAdditionalData($account, $action, $database, $databaseAccount, $website) {

        if(!empty($databaseAccount)) {

            if($databaseAccount->database["_id"] != "0" && $database->_id != $databaseAccount->database["_id"]) {

                $databaseById = DatabaseRepository::findOneById($databaseAccount->database["_id"], $website->_id);

                if(!empty($databaseById)) {

                    DatabaseRepository::delete($databaseById);

                }

                $action["accounts"][count($action["phones"]) - 1] = true;

            }

            $databaseAccount->database = [
                "_id" => DataComponent::initializeObjectId($database->_id),
                "name" => $database->name
            ];

            if(empty($databaseAccount->_id)) {

                try {

                    DatabaseAccountRepository::insert($account, $databaseAccount, $website->_id);

                } catch(Exception $exception) {

                    if($exception->getCode() == 11000) {

                        self::replaceAccount($account, $database, $databaseAccount, $website);

                    }

                }

            } else {

                try {

                    DatabaseAccountRepository::update($account, $databaseAccount, $website->_id);

                } catch(Exception $exception) {

                    if($exception->getCode() == 11000) {

                        self::replaceAccount($account, $database, $databaseAccount, $website);

                    }

                }

            }

        }

        try {

            $databaseAttempt = new DatabaseAttempt();
            $databaseAttempt->contact = $database->contact;
            $databaseAttempt->total = 0;
            $databaseAttempt->website = [
                "ids" => [],
                "names" => [],
                "totals" => []
            ];
            DatabaseAttemptRepository::insert($account, $databaseAttempt);

        } catch(Exception $exception) {

            if($exception->getCode() != 11000) {

                Log::error($exception->getMessage());

            }

        }

        return $action;

    }


    public static function importData($request) {

        $result = new stdClass();
        $result->response = "Failed to import database data";
        $result->result = false;

        $account = DataComponent::initializeAccount($request);

        $website = WebsiteRepository::findOneByIdNucodeStatus($request->website, $account->nucode, "Active");

        if(!empty($website)) {

            $fileName = str_replace("." . $request->file->getClientOriginalExtension(), "", $request->file->getClientOriginalName());
            $i = 1;

            while(Storage::disk("import")->exists($fileName . "." . $request->file->getClientOriginalExtension())) {

                $fileName = str_replace("." . $request->file->getClientOriginalExtension(), "", $request->file->getClientOriginalName()) . "-" . $i;

                $i++;

            }

            $fileName .= "." . $request->file->getClientOriginalExtension();

            Storage::disk("import")->putFileAs("/", $request->file, $fileName);

            $group = [
                "_id" => "0",
                "name" => "System"
            ];

            $userGroupByIdStatus = UserGroupRepository::findOneByIdStatus($request->group, "Active");

            if(!empty($userGroupByIdStatus)) {

                $group = [
                    "_id" => DataComponent::initializeObjectId($userGroupByIdStatus->_id),
                    "name" => $userGroupByIdStatus->name
                ];

            }

            $databaseImport = new DatabaseImport();
            $databaseImport->file = $request->file->getClientOriginalName();
            $databaseImport->group = $group;
            $databaseImport->row = 0;
            $databaseImport->status = "Pending";
            $databaseImport->website = [
                "_id" => DataComponent::initializeObjectId($website->_id),
                "name" => $website->name
            ];
            $databaseImportLast = DatabaseImportRepository::insert($account, $databaseImport);

            $action = [
                "accounts" => [false],
                "databaseImport" => [
                    "_id" => "0",
                    "file" => "System"
                ],
                "crms" => [false],
                "inserts" => [false],
                "groups" => [false],
                "phones" => [],
                "telemarketers" => [false]
            ];

            if($databaseImportLast->_id != null) {

                $action["databaseImport"] = [
                    "_id" => DataComponent::initializeObjectId($databaseImportLast->_id),
                    "file" => $databaseImportLast->file
                ];

                $databaseFile = Excel::toArray(new \App\Imports\DatabaseImport(), $request->file);

                if(!empty($databaseFile)) {

                    $name = self::generateDefaultName($website->_id);

                    foreach($databaseFile[0] as $row) {

                        $database = new Database();

                        if(!empty($row[5]) && is_numeric($row[5])) {

                            if(empty($row[1])) {

                                $row[1] = $name["prefix"] . " " . $name["number"];

                                $name["number"]++;

                            }

                            $newAccount = self::initializeAccount($row[17], $row[13], $row[12], $row[11], $row[9], $row[10], $row[2], $website, $row[15], $row[14]);

                            $database->city = "";
                            $database->contact = [
                                "email" => $row[3],
                                "line" => $row[6],
                                "michat" => $row[8],
                                "phone" => $row[4],
                                "telegram" => "",
                                "wechat" => $row[7],
                                "whatsapp" => $row[5]
                            ];
                            $database->country = "";
                            $database->crm = [
                                "_id" => "0",
                                "avatar" => "",
                                "name" => "System",
                                "username" => "system"
                            ];
                            $database->gender = "";
                            $database->group = $group;
                            $database->import = [
                                "_id" => DataComponent::initializeObjectId($databaseImportLast->_id),
                                "file" => $databaseImportLast->file
                            ];
                            $database->language = "";
                            $database->name = $row[1];
                            $database->reference = "";
                            $database->state = "";
                            $database->status = "Available";
                            $database->street = "";
                            $database->telemarketer = [
                                "_id" => "0",
                                "avatar" => "",
                                "name" => "System",
                                "username" => "system"
                            ];
                            $database->zip = "";

                            array_push($action["phones"], $database->contact["phone"]);

                            $database->crm = self::initializeUser($row[17]);
                            $database->telemarketer = self::initializeUser($row[16]);

                            try {

                                $databaseLast = DatabaseRepository::insert($account, $database, $website->_id);

                                if($databaseLast->_id != null) {

                                    $action = self::importAdditionalData($account, $action, $databaseLast, $newAccount, $website);

                                }

                                $action["crms"][count($action["phones"]) - 1] = true;
                                $action["inserts"][count($action["phones"]) - 1] = true;
                                $action["groups"][count($action["phones"]) - 1] = true;
                                $action["telemarketers"][count($action["phones"]) - 1] = true;

                            } catch(Exception $exception) {

                                if($exception->getCode() == 11000) {

                                    $databaseByContactPhone = DatabaseRepository::findOneByContactPhone($database->contact["phone"], $website->_id);

                                    if(!empty($databaseByContactPhone)) {

                                        $action = self::importAdditionalData($account, $action, $databaseByContactPhone, $newAccount, $website);

                                        $databaseByContactPhone->status = $database->status;

                                        if($database->crm["_id"] != $databaseByContactPhone->crm["_id"]) {

                                            $databaseByContactPhone->crm = self::initializeUser($row[17]);

                                            $action["crms"][count($action["phones"]) - 1] = true;

                                        }

                                        if($database->group["_id"] != $databaseByContactPhone->group["_id"]) {

                                            $databaseByContactPhone->group = $group;

                                            $action["groups"][count($action["phones"]) - 1] = true;

                                        }

                                        if($database->telemarketer["_id"] != $databaseByContactPhone->telemarketer["_id"]) {

                                            $databaseByContactPhone->telemarketer = self::initializeUser($row[16]);

                                            $action["telemarketers"][count($action["phones"]) - 1] = true;

                                        }

                                        DatabaseRepository::update($account, $databaseByContactPhone, $website->_id);

                                        if(!empty($databaseAccount)) {

                                            $action["accounts"][count($action["phones"]) - 1] = true;

                                        }

                                    }

                                }

                            }

                        }

                        usleep(1000);

                    }

                    $databaseImportLast->row = count($action["phones"]);
                    $databaseImportLast->status = "Imported";
                    DatabaseImportRepository::update($account, $databaseImportLast);

                    $databaseImportAction = new DatabaseImportAction();
                    $databaseImportAction->accounts = $action["accounts"];
                    $databaseImportAction->databaseImport = $action["databaseImport"];
                    $databaseImportAction->crms = $action["crms"];
                    $databaseImportAction->inserts = $action["inserts"];
                    $databaseImportAction->groups = $action["groups"];
                    $databaseImportAction->phones = $action["phones"];
                    $databaseImportAction->telemarketers = $action["telemarketers"];
                    DatabaseImportActionRepository::insert($account, $databaseImportAction);

                }

            }

            $result->response = "Database data imported";
            $result->result = true;

        } else {

            $result->response = "Website data doesn't exist";

        }

        return $result;

    }


    private static function initializeAccount($crmId, $depositLastTimestamp, $depositTotalAmount, $loginLastTimestamp, $reference, $registerTimestamp, $username, $website, $withdrawalLastTimestamp, $withdrawalTotalAmount) {

        $result = null;

        if(!empty($crmId) && !empty($username)) {

            $result = DatabaseAccountRepository::findOneByUsername($username, $website->_id);

            if(empty($result)) {

                $result = new DatabaseAccount();

            }

            $result->deposit = [
                "average" => [
                    "amount" => "0",
                ],
                "first" => [
                    "amount" => "0",
                    "timestamp" => ""
                ],
                "last" => [
                    "amount" => "0",
                    "timestamp" => new UTCDateTime(Carbon::instance(Date::excelToDateTimeObject($depositLastTimestamp, config("app.timezone")))->format("U") * 1000)
                ],
                "total" => [
                    "amount" => strval($depositTotalAmount)
                ]
            ];
            $result->games = [];
            $result->login = [
                "average" => [
                    "daily" => 0,
                    "monthly" => 0,
                    "weekly" => 0,
                    "yearly" => 0
                ],
                "first" => [
                    "timestamp" => ""
                ],
                "last" => [
                    "timestamp" => new UTCDateTime(Carbon::instance(Date::excelToDateTimeObject($loginLastTimestamp, config("app.timezone")))->format("U") * 1000)
                ],
                "total" => [
                    "amount" => "0"
                ]
            ];
            $result->reference = $reference;
            $result->register = [
                "timestamp" => new UTCDateTime(Carbon::instance(Date::excelToDateTimeObject($registerTimestamp, config("app.timezone")))->format("U") * 1000)
            ];
            $result->username = $username;
            $result->withdrawal = [
                "average" => [
                    "amount" => "0",
                ],
                "first" => [
                    "amount" => "0",
                    "timestamp" => ""
                ],
                "last" => [
                    "amount" => "0",
                    "timestamp" => new UTCDateTime(Carbon::instance(Date::excelToDateTimeObject($withdrawalLastTimestamp, config("app.timezone")))->format("U") * 1000)
                ],
                "total" => [
                    "amount" => strval($withdrawalTotalAmount)
                ]
            ];

        }

        return $result;

    }


    private static function initializeUser($username) {

        $result = [
            "_id" => "0",
            "avatar" => "",
            "name" => "System",
            "username" => "system"
        ];

        if(!empty($username)) {

            $userByUsername = UserRepository::findOneByUsername($username);

            if(!empty($userByUsername)) {

                $result = [
                    "_id" => DataComponent::initializeObjectId($userByUsername->_id),
                    "avatar" => $userByUsername->avatar,
                    "name" => $userByUsername->name,
                    "username" => $userByUsername->username
                ];

            }

        }

        return $result;

    }


    private static function replaceAccount($account, $database, $databaseAccount, $website) {

        $databaseAccountByDatabaseId = DatabaseAccountRepository::findOneByDatabaseId($databaseAccount->database["_id"], $website->_id);

        if(!empty($databaseAccountByDatabaseId)) {

            $databaseAccountByDatabaseId->database = [
                "_id" => DataComponent::initializeObjectId($databaseAccountByDatabaseId->_id),
                "name" => $database->_id
            ];
            DatabaseAccountRepository::update($account, $databaseAccountByDatabaseId, $website->_id);
            DatabaseAccountRepository::insert($account, $databaseAccount, $website->_id);

        }

    }


}
