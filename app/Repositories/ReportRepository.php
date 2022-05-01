<?php

namespace App\Repositories;

use App\Components\DataComponent;
use App\Models\Report;
use Illuminate\Support\Carbon;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;


class ReportRepository {


    public static function countByUserIdBetweenDate($endDate, $nucode, $startDate, $userId) {

        $report = new Report();
        $report->setTable("report_" . $nucode);

        return $report->where([
            ["date", ">=", $startDate],
            ["date", "<=", $endDate],
            ["user._id", "=", $userId]
        ])->count("_id");

    }


    public static function delete($id) {

        Report::find($id)->delete();

    }


    public static function findOneByDateUserId($date, $nucode, $userId) {

        $report = new Report();
        $report->setTable("report_" . $nucode);

        return $report->where([
            ["date", "=", $date],
            ["user._id", "=", $userId]
        ])->first();

    }


    public static function findOneByUserId($nucode, $userId) {

        $report = new Report();
        $report->setTable("report_" . $nucode);

        return $report->where([
            ["user._id", "=", $userId]
        ])->first();

    }


    public static function findByUserIdBetweenDate($endDate, $length, $nucode, $page, $startDate, $userId) {

        $report = new Report();
        $report->setTable("report_" . $nucode);

        return $report->where([
            ["date", ">=", $startDate],
            ["date", "<=", $endDate],
            ["user._id", "=", $userId]
        ])->forPage($page, $length)->get();

    }


    public static function findRawTable($date, $name, $nucode, $username) {

        $report = new Report();
        $report->setTable("report_" . $nucode);

        return $report->raw(function($collection) use ($date, $name, $nucode, $username) {

            $query = [];

            if(!is_null($date)) {

                $date = explode(" to ", $date);

                if(count($date) == 1) {

                    array_push($query, [
                        '$match' => [
                            "date" => new UTCDateTime(Carbon::parse($date[0])->format("U") * 1000)
                        ]
                    ]);

                } else if(count($date) == 2) {

                    array_push($query, [
                        '$match' => [
                            "date" => [
                                '$gte' => new UTCDateTime(Carbon::parse($date[0])->format("U") * 1000),
                                '$lte' => new UTCDateTime(Carbon::parse($date[1])->format("U") * 1000)
                            ]
                        ]
                    ]);

                }

            }

            if(!is_null($name)) {

                array_push($query, [
                    '$match' => [
                        "user.name" => new Regex($name)
                    ]
                ]);

            }

            if(!is_null($username)) {

                array_push($query, [
                    '$match' => [
                        "user.username" => new Regex($username)
                    ]
                ]);

            }

            array_push($query, [
                '$group' => [
                    "_id" => '$user._id',
                    "date" => [
                        '$push' => '$date'
                    ],
                    "status" => [
                        '$push' => '$status'
                    ],
                    "total" => [
                        '$sum' => '$total'
                    ],
                    "user" => [
                        '$push' => '$user'
                    ],
                    "website" => [
                        '$push' => '$website'
                    ]
                ]
            ]);

            return $collection->aggregate($query, ["allowDiskUse" => true]);

        });

    }


    public static function insert($account, $data) {

        $data->created = DataComponent::initializeTimestamp($account);
        $data->modified = $data->created;

        $data->setTable("report_" . $account->nucode);

        $data->save();

        return $data;

    }


    public static function update($account, $data) {

        if($account != null) {

            $data->modified = DataComponent::initializeTimestamp($account);

        }

        $data->setTable("report_" . $account->nucode);

        return $data->save();

    }


}
