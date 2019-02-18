<?php

namespace Luna\Andromeda;

use Luna\Andromeda\Authentication\Auth;

class Andromeda
{
    private static $init = false;
    private  static $sources = ["Json", "Database", "Table", "Query"];

    public static function init()
    {
        require_once "Authentication" . DIRECTORY_SEPARATOR . "Auth.php";

        foreach (self::$sources as $source)
            require_once __DIR__ . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . $source .".php";

        $GLOBALS["system_user"] = self::guest();
    }

    public static function connect($user, $pass)
    {
        return Auth::login($user,$pass);
    }

    public static function guest()
    {
        return Auth::guest();
    }
}