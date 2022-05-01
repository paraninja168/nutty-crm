<?php

namespace App\Components;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use stdClass;


class DataComponent {


    public static function checkNucode($request, $nucode, $validation) {

        if($request->session()->has("account")) {

            if($request->session()->get("account")->nucode != "system") {

                if($request->session()->get("account")->nucode != $nucode) {

                    array_push($validation, false);

                }

            }

        }

        return $validation;

    }


    public static function checkPrivilege($request, $privilege, $action) {

        $result = false;

        if($request->session()->has("account")) {

            $start = 0;

            switch($action) {
                case "view":
                    $start = 0;

                    break;
                case "add":
                    $start = 1;

                    break;
                case "edit":
                    $start = 2;

                    break;
                case "delete":
                    $start = 3;

                    break;
            }

            if(substr($request->session()->get("account")->privilege[$privilege], $start, 1) == "7") {

                $result = true;

            }

        }

        return $result;

    }


    public static function checkSystemPrivilege($request) {

        $result = false;

        if($request->session()->has("account")) {

            if($request->session()->get("account")->nucode == "system" && $request->session()->get("account")->username == "system") {

                $result = true;

            }

        }

        return $result;

    }


    public static function createDatabaseAccountIndex($table) {

        $table->string("database._id")->unique();
        $table->string("database.name")->index();
        $table->string("deposit.average.amount")->index();
        $table->string("deposit.first.amount")->index();
        $table->date("deposit.first.timestamp")->index();
        $table->string("deposit.last.amount")->index();
        $table->date("deposit.last.timestamp")->index();
        $table->string("deposit.total.amount")->index();
        $table->integer("login.average.daily")->index();
        $table->integer("login.average.monthly")->index();
        $table->integer("login.average.weekly")->index();
        $table->integer("login.average.yearly")->index();
        $table->date("login.first.timestamp")->index();
        $table->date("login.last.timestamp")->index();
        $table->string("login.total.amount")->index();
        $table->string("reference")->index();
        $table->date("register.timestamp")->index();
        $table->string("username")->unique();
        $table->string("withdrawal.average.amount")->index();
        $table->string("withdrawal.first.amount")->index();
        $table->date("withdrawal.first.timestamp")->index();
        $table->string("withdrawal.last.amount")->index();
        $table->date("withdrawal.last.timestamp")->index();
        $table->string("withdrawal.total.amount")->index();
        $table->date("created.timestamp")->index();
        $table->date("modified.timestamp")->index();

    }


    public static function createDatabaseAttemptIndex($table) {

        $table->string("contact.email")->index();
        $table->string("contact.line")->index();
        $table->string("contact.michat")->index();
        $table->string("contact.phone")->unique();
        $table->string("contact.telegram")->index();
        $table->string("contact.wechat")->index();
        $table->string("contact.whatsapp")->index();
        $table->integer("total")->index();
        $table->date("created.timestamp")->index();
        $table->date("modified.timestamp")->index();

    }


    public static function createDatabaseImportIndex($table) {

        $table->string("file")->index();
        $table->string("group._id")->index();
        $table->string("group.name")->index();
        $table->integer("row")->index();
        $table->string("status")->index();
        $table->string("website._id")->index();
        $table->string("website.name")->index();
        $table->date("created.timestamp")->index();
        $table->date("modified.timestamp")->index();

    }


    public static function createDatabaseImportActionIndex($table) {

        $table->string("databaseImport._id")->unique();
        $table->string("databaseImport.file")->index();
        $table->date("created.timestamp")->index();
        $table->date("modified.timestamp")->index();

    }


    public static function createDatabaseIndex($table) {

        $table->string("city")->index();
        $table->string("contact.email")->index();
        $table->string("contact.line")->index();
        $table->string("contact.michat")->index();
        $table->string("contact.phone")->unique();
        $table->string("contact.telegram")->index();
        $table->string("contact.wechat")->index();
        $table->string("contact.whatsapp")->index();
        $table->string("country")->index();
        $table->string("crm._id")->index();
        $table->string("crm.avatar")->index();
        $table->string("crm.name")->index();
        $table->string("crm.username")->index();
        $table->string("gender")->index();
        $table->string("group._id")->index();
        $table->string("group.name")->index();
        $table->string("import._id")->index();
        $table->string("import.file")->index();
        $table->string("language")->index();
        $table->string("name")->index();
        $table->string("reference")->index();
        $table->string("state")->index();
        $table->string("status")->index();
        $table->string("street")->index();
        $table->string("telemarketer._id")->index();
        $table->string("telemarketer.avatar")->index();
        $table->string("telemarketer.name")->index();
        $table->string("telemarketer.username")->index();
        $table->string("zip")->index();
        $table->date("created.timestamp")->index();
        $table->date("modified.timestamp")->index();

    }


    public static function createDatabaseLogIndex($table) {

        $table->string("database._id")->index();
        $table->string("database.name")->index();
        $table->string("reference")->index();
        $table->string("status")->index();
        $table->string("user._id")->index();
        $table->string("user.avatar")->index();
        $table->string("user.name")->index();
        $table->string("user.username")->index();
        $table->date("created.timestamp")->index();
        $table->date("modified.timestamp")->index();

    }


    public static function createNexusPlayerTransactionIndex($table) {

        $table->string("adjustment.reference")->index();
        $table->decimal("amount.final")->index();
        $table->decimal("amount.request")->index();
        $table->date("approved.timestamp")->index();
        $table->string("approved.user._id")->index();
        $table->string("approved.user.username")->index();
        $table->string("bank.account.from.name")->index();
        $table->string("bank.account.from.number")->index();
        $table->string("bank.account.to.name")->index();
        $table->string("bank.account.to.number")->index();
        $table->string("bank.from")->index();
        $table->string("bank.to")->index();
        $table->decimal("fee.admin")->index();
        $table->string("reference")->unique();
        $table->date("requested.timestamp")->index();
        $table->string("requested.user._id")->index();
        $table->string("requested.user.username")->index();
        $table->string("transaction.code")->index();
        $table->string("transaction.type")->index();
        $table->string("username")->index();

    }


    public static function createPlayerAttemptIndex($table) {

        $table->integer("total")->index();
        $table->string("username")->index();
        $table->date("created.timestamp")->index();
        $table->date("modified.timestamp")->index();

    }


    public static function createReportIndex($table) {

        $table->date("date")->index();
        $table->integer("total")->index();
        $table->string("user._id")->index();
        $table->string("user.avatar")->index();
        $table->string("user.name")->index();
        $table->string("user.username")->index();
        $table->date("created.timestamp")->index();
        $table->date("modified.timestamp")->index();

    }


    public static function initializeAccessDenied() {

        $result = new stdClass();
        $result->response = "Sorry, you're not authorized to access this feature";
        $result->result = false;

        return $result;

    }


    public static function initializeAccount($request) {

        $result = new User();

        if($request->session()->has("account")) {

            $result = $request->session()->get("account");

        }

        return $result;

    }


    public static function initializeCollectionByNucode($nucode) {

        if(!Schema::hasTable("databaseAttempt_" . $nucode)) {

            Schema::create("databaseAttempt_" . $nucode, function(Blueprint $table) {

                self::createDatabaseAttemptIndex($table);

            });

        } else {

            Schema::table("databaseAttempt_" . $nucode, function(Blueprint $table) {

                self::createDatabaseAttemptIndex($table);

            });

        }

        if(!Schema::hasTable("databaseImport_" . $nucode)) {

            Schema::create("databaseImport_" . $nucode, function(Blueprint $table) {

                self::createDatabaseImportIndex($table);

            });

        } else {

            Schema::table("databaseImport_" . $nucode, function(Blueprint $table) {

                self::createDatabaseImportIndex($table);

            });

        }

        if(!Schema::hasTable("databaseImportAction_" . $nucode)) {

            Schema::create("databaseImportAction_" . $nucode, function(Blueprint $table) {

                self::createDatabaseImportActionIndex($table);

            });

        } else {

            Schema::table("databaseImportAction_" . $nucode, function(Blueprint $table) {

                self::createDatabaseImportActionIndex($table);

            });

        }

        if(!Schema::hasTable("playerAttempt_" . $nucode)) {

            Schema::create("playerAttempt_" . $nucode, function(Blueprint $table) {

                self::createPlayerAttemptIndex($table);

            });

        } else {

            Schema::table("playerAttempt_" . $nucode, function(Blueprint $table) {

                self::createPlayerAttemptIndex($table);

            });

        }

        if(!Schema::hasTable("report_" . $nucode)) {

            Schema::create("report_" . $nucode, function(Blueprint $table) {

                self::createReportIndex($table);

            });

        } else {

            Schema::table("report_" . $nucode, function(Blueprint $table) {

                self::createReportIndex($table);

            });

        }

    }


    public static function initializeCollectionByWebsite($websiteId) {

        if(!Schema::hasTable("database_" . $websiteId)) {

            Schema::create("database_" . $websiteId, function(Blueprint $table) {

                self::createDatabaseIndex($table);

            });

        } else {

            Schema::table("database_" . $websiteId, function(Blueprint $table) {

                self::createDatabaseIndex($table);

            });

        }

        if(!Schema::hasTable("databaseAccount_" . $websiteId)) {

            Schema::create("databaseAccount_" . $websiteId, function(Blueprint $table) {

                self::createDatabaseAccountIndex($table);

            });

        } else {

            Schema::table("databaseAccount_" . $websiteId, function(Blueprint $table) {

                self::createDatabaseAccountIndex($table);

            });

        }

        if(!Schema::hasTable("databaseLog_" . $websiteId)) {

            Schema::create("databaseLog_" . $websiteId, function(Blueprint $table) {

                self::createDatabaseLogIndex($table);

            });

        } else {

            Schema::table("databaseLog_" . $websiteId, function(Blueprint $table) {

                self::createDatabaseLogIndex($table);

            });

        }

        if(!Schema::hasTable("nexusPlayerTransaction_" . $websiteId)) {

            Schema::create("nexusPlayerTransaction_" . $websiteId, function(Blueprint $table) {

                self::createNexusPlayerTransactionIndex($table);

            });

        } else {

            Schema::table("nexusPlayerTransaction_" . $websiteId, function(Blueprint $table) {

                self::createNexusPlayerTransactionIndex($table);

            });

        }

    }


    public static function initializeData($data) {

        if(!array_key_exists("created", $data)) {

            $data["created"] = [
                "timestamp" => null,
                "user" => [
                    "_id" => null,
                    "avatar" => null,
                    "name" => null,
                    "username" => null
                ]
            ];

        }

        if(!array_key_exists("timestamp", $data["created"])) {

            $data["created"]["timestamp"] = new UTCDateTime();

        }

        if(!array_key_exists("user", $data["created"])) {

            $data["created"]["user"] = [
                "_id" => "0",
                "avatar" => "",
                "name" => "System",
                "username" => "System"
            ];

        }

        if(!array_key_exists("modified", $data)) {

            $data["modified"] = [
                "timestamp" => null,
                "user" => [
                    "_id" => null,
                    "avatar" => null,
                    "name" => null,
                    "username" => null
                ]
            ];

        }

        if(!array_key_exists("timestamp", $data["modified"])) {

            $data["modified"]["timestamp"] = new UTCDateTime();

        }

        if(!array_key_exists("user", $data["modified"])) {

            $data["modified"]["user"] = [
                "_id" => "0",
                "avatar" => "",
                "name" => "System",
                "username" => "System"
            ];

        }

        return $data;

    }


    public static function initializeFilterDateRange($dateRange, $defaultEnd, $defaultStart) {

        $result = new stdClass();
        $result->end = $defaultEnd;
        $result->response = "Failed to initialize filter date range";
        $result->result = false;
        $result->start = $defaultStart;

        if(!empty($dateRange)) {

            $date = explode(" to ", $dateRange);

            if(count($date) == 1) {

                $result->start = new UTCDateTime(Carbon::parse($date[0])->format("U") * 1000);

            } else if(count($date) == 2) {

                $result->start = new UTCDateTime(Carbon::parse($date[0])->format("U") * 1000);
                $result->end = new UTCDateTime(Carbon::parse($date[1])->format("U") * 1000);

            }

        }

        $result->response = "Filter date range initialized";
        $result->result = true;

        return $result;

    }


    public static function initializeObject($data) {

        return json_decode(json_encode($data));

    }


    public static function initializeObjectId($id) {

        $result = "0";

        if($id != "0") {

            $result = new ObjectId($id);

        }

        return $result;

    }


    public static function initializePage($start, $length) {

        $result = $start / $length;

        if($result < 1) {

            $result = 1;

        }

        return $result;

    }


    public static function initializeSystemAccount() {

        $result = new stdClass();
        $result->_id = "0";
        $result->avatar = "";
        $result->group["_id"] = "0";
        $result->group["name"] = "System";
        $result->name = "System";
        $result->username = "System";

        return $result;

    }


    public static function initializeTableData($account, $model) {

        if($account->nucode != "system") {

            $model = $model->where([
                ["nucode", "=", $account->nucode]
            ]);

        }

        return $model;

    }


    public static function initializeTableQuery($model, $tableFilterColumns, $tableFilterOrders, $defaultOrders) {

        $query = [];

        foreach($tableFilterColumns as $column) {

            if(!empty($column->search->value)) {

                if($column->search->regex) {

                    array_push($query, [
                        $column->name,
                        "LIKE",
                        "%" . $column->search->value . "%"
                    ]);

                } else {

                    if($column->search->value == "true" || $column->search->value == "false") {

                        array_push($query, [
                            $column->name,
                            "=",
                            filter_var($column->search->value, FILTER_VALIDATE_BOOLEAN)
                        ]);

                    } else {

                        array_push($query, [
                            $column->name,
                            "=",
                            $column->search->value
                        ]);

                    }

                }

            }

        }

        $model = $model->where($query);

        if($tableFilterOrders[0]->column == 0) {

            if(strtoupper($tableFilterOrders[0]->dir) == "ASC" || strtoupper($tableFilterOrders[0]->dir) == "DESC") {

                foreach($defaultOrders as $order) {

                    $model = $model->orderBy($order, strtoupper($tableFilterOrders[0]->dir));

                }

            }

        } else {

            if(strtoupper($tableFilterOrders[0]->dir) == "ASC" || strtoupper($tableFilterOrders[0]->dir) == "DESC") {

                $model = $model->orderBy($tableFilterOrders[0]->column->data, strtoupper($tableFilterOrders[0]->dir));

            }

        }

        return $model;

    }


    public static function initializeTimestamp($account) {

        return [
            "timestamp" => new UTCDateTime(),
            "user" => [
                "_id" => self::initializeObjectId($account->_id),
                "avatar" => $account->avatar,
                "name" => $account->name,
                "username" => $account->username
            ]
        ];

    }


    public static function initializeUserAgent($request) {

        $result = [
            "browser" => [
                "manufacturer" => "",
                "name" => "",
                "renderingEngine" => "",
                "version" => ""
            ],
            "device" => [
                "os" => "",
                "manufacturer" => "",
                "type" => ""
            ],
            "ip" => $request->ip()
        ];

        $userAgent = strtolower($request->header("user-agent"));

        if(str_contains($userAgent, "firefox")) {

            $result["browser"]["manufacturer"] = "Mozilla Foundation";
            $result["browser"]["name"] = "Firefox";
            $result["browser"]["renderingEngine"] = "Gecko";

        } else if(str_contains($userAgent, "chrome") && !str_contains($userAgent, "edg") && !str_contains($userAgent, "opr")) {

            $result["browser"]["manufacturer"] = "Google Inc.";
            $result["browser"]["name"] = "Chrome";
            $result["browser"]["renderingEngine"] = "AppleWebKit";

        } else if(str_contains($userAgent, "edg")) {

            $result["browser"]["manufacturer"] = "Microsoft Corporation";
            $result["browser"]["name"] = "Edge";
            $result["browser"]["renderingEngine"] = "AppleWebKit";

        } else if(str_contains($userAgent, "opr")) {

            $result["browser"]["manufacturer"] = "Opera Software";
            $result["browser"]["name"] = "Opera";
            $result["browser"]["renderingEngine"] = "AppleWebKit";

        } else if(str_contains($userAgent, "safari") && !str_contains($userAgent, "chrome") && !str_contains($userAgent, "edg") && !str_contains($userAgent, "opr")) {

            $result["browser"]["manufacturer"] = "Apple Inc.";
            $result["browser"]["name"] = "Safari";
            $result["browser"]["renderingEngine"] = "AppleWebKit";

        }

        $browser = substr($userAgent, strpos($userAgent, strtolower($result["browser"]["name"])));
        $browserVersion = substr($browser, strpos($browser, "/") + 1);

        $lastIndex = strpos($browserVersion, " ");

        if($lastIndex < 1) {

            $lastIndex = strlen($browserVersion);

        }

        $result["browser"]["version"] = substr($browserVersion, 0, $lastIndex);

        if(str_contains($userAgent, "android")) {

            $result["device"]["os"] = "Android";
            $result["device"]["manufacturer"] = "Google Inc.";
            $result["device"]["type"] = "Mobile";

        } else if(str_contains($userAgent, "iphone")) {

            $result["device"]["os"] = "iPhone";
            $result["device"]["manufacturer"] = "Apple Inc.";
            $result["device"]["type"] = "Mobile";

        } else if(str_contains($userAgent, "windows phone")) {

            $result["device"]["os"] = "Windows Phone";
            $result["device"]["manufacturer"] = "Microsoft Corporation";
            $result["device"]["type"] = "Mobile";

        } else if(str_contains($userAgent, "windows nt")) {

            $result["device"]["os"] = "Windows";
            $result["device"]["manufacturer"] = "Microsoft Corporation";
            $result["device"]["type"] = "Computer";

        } else if(str_contains($userAgent, "macintosh")) {

            $result["device"]["os"] = "Macintosh";
            $result["device"]["manufacturer"] = "Apple Inc.";
            $result["device"]["type"] = "Computer";

        } else if(str_contains($userAgent, "ubuntu")) {

            $result["device"]["os"] = "Ubuntu";
            $result["device"]["manufacturer"] = "Canonical Ltd.";
            $result["device"]["type"] = "Computer";

        }

        return $result;

    }


}
