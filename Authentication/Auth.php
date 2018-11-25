<?php
/**
 * Author: alex
 * Date: 11/24/18
 * Time: 5:17 PM
 */

namespace Luna\Andromeda\Authentication;

require_once "User.php";
class Auth
{
    public static function login($user,$pass) :? User
    {
        return User::login($user,$pass);
    }

    public static function create_user($user, $pass, ...$tables) :? User
    {
        
    }
}