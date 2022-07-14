<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;


class UnclaimedDeposit extends Model {


    use HasFactory;


    protected $attributes = [
        "date" => "",
        "reference" => "",
        "status" => true,
        "type" => "",
        "username" => "",
        "created" => [
            "timestamp" => "",
            "user" => [
                "_id" => "0",
                "username" => "System"
            ]
        ],
        "modified" => [
            "timestamp" => "",
            "user" => [
                "_id" => "0",
                "username" => "System"
            ]
        ]
    ];

    protected $fillable = [
        "date",
        "reference",
        "status",
        "type",
        "username",
        "created->timestamp",
        "created->user->_id",
        "created->user->username",
        "modified->timestamp",
        "modified->user->_id",
        "modified->user->username"
    ];

    protected $table = "unclaimedDeposit";

    public $timestamps = false;


}
