<?php

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
            $data = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "System" . DIRECTORY_SEPARATOR . "Users.adt");
            self::$users = json_decode($data,true);
            self::$sync_data = true;
        }
    }
    private static function save_data()
    {
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "System" . DIRECTORY_SEPARATOR . "Users.adt", json_encode(self::$users));
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
                if($data['locked'])
                    throw new \Error("Error! could not login as $user [user is locked]");

                $u = new self();
                $u->user = $user;
                $u->pass =  md5($pass);
                $u->isAdmin = $data['isAdmin'];
                $u->locked = $data['locked'];
                $u->privileges = $data['privileges'];
                $u->attributes = $data['attributes'];

                $GLOBALS["system_user"] = $u;

                return $u;
            }
            else
                throw new \Error("Error! could not login as $user [wrong password]");
        }
        else
            throw new \Error("Error! could not login as $user [user doesn't exist]");

    }

    public static function guest()
    {
        return $GLOBALS["system_user"] = new self();
    }

    private $user = "Guest";
    private $pass = "";
    private $isAdmin = false;
    private $locked = true;
    private $privileges = [];
    private $attributes = [];

    private function __construct($user = "Guest"){ $this->user = $user; }
    public function __set($name, $value)
    {
        if ($this->user == "Guest" || (in_array($name,["user","username"]) && strtolower($value) == "guest"))
            return;

        switch ($name)
        {
            case "pass":
            case "password":
                $this->pass = password_hash($value, PASSWORD_DEFAULT);
                break;

            case "user":
            case "username":
                if($this->user == "root")
                    return;
                unset(self::$users[$this->user]);
                $this->user = $name;
                $this->save();
                break;
        }
    }
    public function __get($name){}

    public function isAdmin()
    {
        return $this->isAdmin;
    }
    public function setAdminStatus(bool $status)
    {
        if(!Auth::admin())
            throw new \Error("Error! you don't have the authority to edit another user.");
        if($this->user == "root")
            return;
        $this->isAdmin = $status;
    }
    public function isLocked()
    {
        if(!Auth::admin() && ! Auth::user()->can("select", "System", "Users"))
            throw new \Error("Error! you don't have the authority to edit another user.");

        return $this->locked;
    }
    public function lock()
    {
        if(!Auth::admin() && ! Auth::user()->can("update", "System", "Users"))
            throw new \Error("Error! you don't have the authority to edit another user.");

        if($this->user == "root")
            return;

        $this->locked = true;
        self::$sync_data = false;
    }

    public function unlock()
    {
        if(!Auth::admin() && ! Auth::user()->can("update", "System", "Users"))
            throw new \Error("Error! you don't have the authority to edit another user.");

        $this->locked = true;
        self::$sync_data = false;
    }

    public function can($do, $database, $table = "@all")
    {
        if(Auth::admin())
            return true;

        elseif( ! isset($this->privileges[$database][$table]))
            return false;

        if(in_array($value,["create", "import", "drop"]))
            $table = "@db";
            
        $old = $this->privileges[$database][$table];

        if(\is_array($old))
        
            return \in_array($do, $old);
        
        else
            return $old == "allow";
    }

    public function set_privilege($value, $database, $table = "@all")
    {
        if(!Auth::admin() && ! Auth::user()->can("update", "System", "Users"))
            throw new \Error("Error! you don't have the authority to edit another user.");

        if(in_array($value, ["create", "import", "drop", "unset"]) && ($table != "@db" && $table != "@all"))
                throw new \Error("Error! unvalid request.");

        $old = (isset($this->privileges[$database][$table]) && is_array($old))? $old : [];

        if (! \in_array( \strtolower($value),["allow", "deny", "select", "insert", "update", "delete", "create", "import", "drop", "unset"]))
            return;
        
        if(in_array($value,["create", "import", "drop", "unset"]))
            $table = "@db";

        elseif(\strtolower($value) == "allow" || \strtolower($value) == "deny")
        {
            $old = $value;
        }
        elseif($old != "allow" && $old != "deny")
        {
            $old = array_merge($old,[$value]);
        }
        else
            $old = [$value];

    
        $this->privileges[$database][$table] = $old;

        self::$sync_data = false;

    }
    public function remove_privilege($value, $database, $table = "@all")
    {
        if(!Auth::admin() && ! Auth::user()->can("update", "System", "Users"))
            throw new \Error("Error! you don't have the authority to edit another user.");

        if (! \in_array( \strtolower($value),["select", "insert", "update", "delete"]))
            return;
        
        if(in_array($value,["create", "import", "drop", "unset"]))
            $table = "@db";
        $tmp = [];
        if( isset($this->privileges[$database][$table]))
        {
            $old = $this->privileges[$database][$table];
            $tmp = [];
            if( \is_array($old) && \in_array($value, $old))
            {
                foreach($old as $val)
                    if($val != $value)
                        $tmp[] = $val;
                
            }
            elseif($old = "allow")
            {
                foreach(["select", "insert", "update", "delete"] as $val)
                    if($val != $value)
                        $tmp[] = $val;
            }
        }
        $this->privileges[$database][$table] = $tmp;
        
        self::$sync_data = false;
    }

    public function set_attribute($attribute, $value)
    {
        if(!Auth::admin() && ! Auth::user()->can("update", "System", "Users"))
            throw new \Error("Error! you don't have the authority to edit another user.");
        self::$sync_data = false;
        $this->attributes[$attribute] = $value;
    }
    public function remove_attribute($attribute)
    {
        if(!Auth::admin() && ! Auth::user()->can("update", "System", "Users"))
            throw new \Error("Error! you don't have the authority to edit another user.");
        self::$sync_data = false;
        unset($this->attributes[$attribute]);
    }

    public function hasAttribute($attribute)
    {
        return isset($this->attributes[$attribute]);
    }
    public function getAttribute($attribute)
    {
        return ($this->attributes[$attribute]);
    }

    public function save()
    {
        if ($this->user == "Guest")
            return;

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

    public function create_user($user) :? self
    {
        if (Auth::admin() || Auth::user()->can("insert", "System", "Users"))
        {
           if ( ! self::exist($user))
           {
                self::$users[$user] = $u = new self();
                $u->user = $user;
                self::$sync_data = false;
                return $u;
           }
           else
               throw new \Error("Error! user $user already exists.");

        }
        else
            throw new \Error("Error! you don't have the authority to create a user.");
    }

    public function edit_user($user) :? self
    {
        if (Auth::admin() || Auth::user()->can("update", "System", "Users"))
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

    public function drop_user($user) :? boolean
    {
        if (Auth::admin() || Auth::user()->can("delete", "System", "Users"))
        {
           if (self::exist($user))
           {
               unset(self::$users[$user]);
               
               return true;
           }
           else
               throw new \Error("Error! user $user doesn't exist.");

        }
        else
            throw new \Error("Error! you don't have the authority to edit another user.");
    }
}