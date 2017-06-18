<?php

/**
 * Created by PhpStorm.
 * User: leon
 * Date: 18-06-17
 * Time: 23:23
 */
class db
{
    public function __construct() {

    }

    public function connectDB(){
        $db_settings = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/config/config.ini");
        return new medoo([
            'database_type' => 'mysql',
            'database_name' => $db_settings['database_name'],
            'server' => $db_settings['server'],
            'username' => $db_settings['username'],
            'password' => $db_settings['password'],
            'charset' => 'utf8',
            'command' => [
                'SET SQL_MODE=ANSI_QUOTES'
            ]
        ]);
    }

}