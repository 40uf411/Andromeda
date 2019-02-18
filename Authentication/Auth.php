<?php

namespace Luna\Andromeda\Authentication;

require_once "User.php";
class Auth
{
    public static function login($user,$pass) :? User
    {
        return User::login($user,$pass);
    }

    public static function guest() :? User
    {
        return User::guest();
    }

    public static function admin() :? bool
    {
        return (is_a($GLOBALS["system_user"],User::class) && $GLOBALS["system_user"]->isAdmin());
    }
    public static function can($do, $database, $table = "@all")
    {
        return (is_a($GLOBALS["system_user"],User::class) && $GLOBALS["system_user"]->can($do, $database, $table));
    }
    public static function user()
    {
        return $GLOBALS["system_user"];
    }
}