<?php

/**
 * Author: alex
 * Date: 11/24/18
 * Time: 5:15 PM
 */

namespace Luna\Andromeda;

use Luna\Andromeda\Authentication\Auth;

class Andromeda
{
    private static $init = false;
    private  static $sources = ["Driver", "Database", "Table", "Query"];
    private static $drivers = ["Json", "Mysql", "Oracle"];


    public static function init()
    {
        require_once "Authentication" . DIRECTORY_SEPARATOR . "Auth.php";

        foreach (self::$sources as $source)
            require_once __DIR__ . "src" . DIRECTORY_SEPARATOR . $source .".php";

        foreach (self::$drivers as $driver)
            require_once __DIR__ . "Drivers" . DIRECTORY_SEPARATOR . $driver .".php";
    }

    public static function connect($user, $pass, $db = null, $driver = "Json")
    {
        if ($driver == "Json")
        {
            return Auth::login($user,$pass);
        }
    }
}