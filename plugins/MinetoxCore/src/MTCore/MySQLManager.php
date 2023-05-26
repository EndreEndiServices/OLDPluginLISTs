<?php

namespace MTCore;

class MySQLManager{

    public static function getMysqlConnection() : \mysqli{
        $database = new \mysqli("remotemysql.com", "XfnAaPvpeb", "D64o9QnjTz", "XfnAaPvpeb");

        return $database;
    }

}
