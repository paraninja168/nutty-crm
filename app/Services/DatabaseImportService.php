<?php

namespace App\Services;

use App\Components\DataComponent;
use App\Models\DatabaseImport;
use App\Repositories\DatabaseAccountRepository;
use App\Repositories\DatabaseImportActionRepository;
use App\Repositories\DatabaseImportRepository;
use App\Repositories\DatabaseLogRepository;
use App\Repositories\DatabaseRepository;
use App\Repositories\UserGroupRepository;
use App\Repositories\WebsiteRepository;
use MongoDB\BSON\UTCDateTime;
use stdClass;


class DatabaseImportService {


    public static function historyDelete($request) {

        $result = new stdClass();
        $result->response = "Failed to delete database import history data";
        $result->result = false;

        $account = DataComponent::initializeAccount($request);

        $databaseImportById = DatabaseImportRepository::findOneById($request->id, $account->nucode);

        if(!empty($databaseImportById)) {

            $databaseImportAction = DatabaseImportActionRepository::findOneByDatabaseImportId($databaseImportById->_id, $account->nucode);

            if(!empty($databaseImportAction)) {

                foreach($databaseImportAction->phones as $key => $action) {

                    $databaseByContactPhone = DatabaseRepository::findOneByContactPhone($action, $databaseImportById->website["_id"]);

                    if(!empty($databaseByContactPhone)) {

                        if($databaseImportAction->inserts[$key]) {

                            DatabaseRepository::delete($databaseByContactPhone);
                            DatabaseAccountRepository::deleteByDatabaseId($databaseByContactPhone->_id, $databaseImportById->website["_id"]);
                            DatabaseLogRepository::deleteByDatabaseId($databaseByContactPhone->_id, $databaseImportById->website["_id"]);

                        } else {

                            if($databaseImportAction->crms[$key]) {

                                $databaseByContactPhone->crm = [
                                    "_id" => "0",
                                    "avatar" => "",
                                    "name" => "System",
                                    "username" => "system"
                                ];

                            }

                            if($databaseImportAction->groups[$key]) {

                                $databaseByContactPhone->group = [
                                    "_id" => "0",
                                    "name" => "System"
                                ];

                            }

                            if($databaseImportAction->telemarketers[$key]) {

                                $databaseByContactPhone->telemarketer = [
                                    "_id" => "0",
                                    "avatar" => "",
                                    "name" => "System",
                                    "username" => "system"
                                ];

                            }

                            DatabaseRepository::update($account, $databaseByContactPhone, $databaseImportById->website["_id"]);

                            if($databaseImportAction->accounts[$key]) {

                                $databaseAccountByDatabaseId = DatabaseAccountRepository::findOneByDatabaseId($databaseByContactPhone->_id, $databaseImportById->website["_id"]);

                                if(!empty($databaseAccountByDatabaseId)) {

                                    $databaseAccountByDatabaseId->deposit = [
                                        "average" => [
                                            "amount" => "0",
                                        ],
                                        "first" => [
                                            "amount" => "0",
                                            "timestamp" => ""
                                        ],
                                        "last" => [
                                            "amount" => "0",
                                            "timestamp" => new UTCDateTime()
                                        ],
                                        "total" => [
                                            "amount" => "0"
                                        ]
                                    ];
                                    $databaseAccountByDatabaseId->games = [];
                                    $databaseAccountByDatabaseId->login = [
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
                                            "timestamp" => new UTCDateTime()
                                        ],
                                        "total" => [
                                            "amount" => "0"
                                        ]
                                    ];
                                    $databaseAccountByDatabaseId->name = "";
                                    $databaseAccountByDatabaseId->reference = "";
                                    $databaseAccountByDatabaseId->register = [
                                        "timestamp" => new UTCDateTime()
                                    ];
                                    $databaseAccountByDatabaseId->username = "";
                                    $databaseAccountByDatabaseId->withdrawal = [
                                        "average" => [
                                            "amount" => "0",
                                        ],
                                        "first" => [
                                            "amount" => "0",
                                            "timestamp" => ""
                                        ],
                                        "last" => [
                                            "amount" => "0",
                                            "timestamp" => new UTCDateTime()
                                        ],
                                        "total" => [
                                            "amount" => "0"
                                        ]
                                    ];
                                    DatabaseAccountRepository::update($account, $databaseAccountByDatabaseId, $databaseImportById->website["_id"]);

                                }

                            }

                        }

                    }

                }

            }

            $databaseImportById->status = "Deleted";
            DatabaseImportRepository::update($account, $databaseImportById);

            $result->response = "All database import history data deleted";
            $result->result = true;

        }

        return $result;

    }


    public static function historyFindTable($request) {

        $result = new stdClass();
        $result->draw = $request->draw;

        $account = DataComponent::initializeAccount($request);

        $databaseImport = new DatabaseImport();
        $databaseImport->setTable("databaseImport_" . $account->nucode);
        $defaultOrder = ["created.timestamp"];
        $databaseImports = DataComponent::initializeTableQuery($databaseImport, DataComponent::initializeObject($request->columns), DataComponent::initializeObject($request->order), $defaultOrder);
        $databaseImports = $databaseImports->where([
            ["status", "!=", "Deleted"]
        ]);

        $result->recordsTotal = $databaseImports->count("_id");
        $result->recordsFiltered = $result->recordsTotal;

        $result->data = $databaseImports->forPage(DataComponent::initializePage($request->start, $request->length), $request->length)->get();

        return $result;

    }


    public static function initializeData($request) {

        $result = new stdClass();
        $result->response = "Failed to initialize database import data";
        $result->result = false;

        $account = DataComponent::initializeAccount($request);

        if($account->nucode == "system") {
            $result->userGroups = UserGroupRepository::findByStatus("Active");
            $result->websites = WebsiteRepository::findByStatus("Active");

        } else {
            $result->userGroups = UserGroupRepository::findByNucodeStatus($account->nucode, "Active");
            $result->websites = WebsiteRepository::findByNucodeStatus($account->nucode, "Active");
        }

        $result->response = "Database import data initialized";
        $result->result = true;

        return $result;

    }


}
