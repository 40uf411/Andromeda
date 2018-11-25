<?php
/**
 * User: alex
 * Date: 11/24/18
 * Time: 7:53 PM
 */

namespace Luna\Andromeda\Authentication;

require_once "User.php";

class User
{
    private static $users;
    private static $sync_data = false;

    private static function load_data()
    {
        if (! self::$sync_data)
        {
            $data = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "users.adt");
            self::$users = json_decode($data,true);
            self::$sync_data = true;
        }
    }
    private static function save_data()
    {
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . "users.adt", json_encode(self::$users));
        self::$sync_data = true;
    }

    public static function exist($user) : bool
    {
        self::load_data();

        return (bool) array_key_exists($user, self::$users);
    }

    public static function login($user, $pass) :? self
    {
        if (self::exist($user))
        {
            $data = self::$users[$user];
            $old_pass = $data["pass"];

            if (password_verify($pass, $old_pass))
            {
                $u = new self();
                $u->user = $user;
                $u->pass =  md5($pass);
                $u->isAdmin = $data['isAdmin'];
                $u->privileges = $data['privileges'];
                $u->attributes = $data['attributes'];
                return $u;
            }
            else
                throw new \Error("Error! could not login as $user [wrong password]");
        }
        else
            throw new \Error("Error! could not login as $user [user doesn't exist]");

    }

    private $user;
    private $pass;
    private $isAdmin = false;
    private $privileges;
    private $attributes;

    private function __construct(){}
    public function __set($name, $value)
    {
        switch ($name)
        {
            case "pass":
            case "password":
                $this->pass = password_hash($value, PASSWORD_DEFAULT);
                break;

            case "user":
            case "username":
                unset(self::$users[$this->user]);
                $this->save();
                break;

            case "isAdmin":
                $this->isAdmin = $value;
                break;

            case "privileges":
                $this->privileges = $value;
                break;

            case "attributes":
                $this->attributes = $value;
                break;
        }
    }
    #public function __get($name){}

    public function add_privilege($table, $details)
    {

    }
    public function edit_privilege($table, $details)
    {

    }
    public function remove_privilege($table, $details)
    {

    }

    public function add_attribute($attribute, $value)
    {

    }
    public function edit_attribute($attribute, $value)
    {

    }
    public function remove_attribute($attribute)
    {

    }

    public function save()
    {
        self::$users[$this->user] = [
            "pass" => $this->pass,
            "isAdmin" => $this->isAdmin,
            "privileges" => $this->privileges,
            "attributes" => $this->attributes
        ];
        self::$sync_data = false;
        self::save_data();
        self::$sync_data = true;
    }

    public function edit_user($user) :? self
    {
        if ($this->isAdmin)
        {
           if (self::exist($user))
           {
               $data = self::$users[$user];
               $u = new self();
               $u->user = $user;
               $u->pass =  "";
               $u->isAdmin = $data['isAdmin'];
               $u->privileges = $data['privileges'];
               $u->attributes = $data['attributes'];
               return $u;
           }
           else
               throw new \Error("Error! user $user doesn't exist.");

        }
        else
            throw new \Error("Error! you don't have the authority to edit another user.");
    }
}