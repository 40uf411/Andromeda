<?php

/* TODO
*
*
*/

namespace Luna\Andromeda\Sources;

use Luna\Andromeda\Authentication\Auth;

class Database
{
    private $name;
    private $owns = [];
    private $imports = [];

    private $meta_file = "";
    
    private $sync = false;

    private function load_data()
    {
        if (! $this->sync )
        {
            $meta = file_get_contents($this->meta_file);
            $meta = json_decode($meta,true)[$this->name];

            $this->tables = $meta['owns'];
            $this->imports = $meta['imports'];

            $this->sync = true;
        }
    }

    private function save_data()
    {
        if (! $this->sync )
        {
            $meta = file_get_contents($this->meta_file);
            $meta = json_decode($meta,true);
    
            $meta[$this->name]["owns"] = $this->owns;
            $meta[$this->name]["imports"] = $this->imports;
    
            file_put_contents($this->meta_file, \json_encode($meta));
    
            $this->sync = true;
        }
    }

    public function host($table, array $details = [], $override = false)
    {
        if(!Auth::admin() && ! Auth::user()->can("create", $this->name))

            throw new \Error("Error! you don't have the authority to edit another user.");

        if(Table::exist($table, $this->name) && !$override)

            throw new \Error("Error! could not create database $name [database already exists].");

        $this->load_data();

        $folder = $this->name;

        if(\is_a($table,Table::class))
        {
            if($table->folder != $this->name)

                throw new \Error("Error! unmatched table and database.");

            $this->owns[] = $table->name;
        }
        else
        {
            Table::create($table, $this->name, $details, $override);

            $this->owns[] = $table;
        }
        $this->sync = false;

        $this->save_data();
    }
    
    public static function exist($name)
    {
        $meta_file =(string) __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "System" . DIRECTORY_SEPARATOR  . "Databases.adt";

        $meta_file = \file_get_contents($meta_file);

        $meta_file= \json_decode($meta_file, true);
        
        return array_key_exists("$name",$meta_file);
    }

    public static function connect($name) :? self
    {
        if(!Auth::admin() && ! Auth::user()->can("select", $name))

            throw new \Error("Error! you don't have the authority to edit another user.");

        if(self::exist($name))

            throw new \Error("Error! could not create database $name [database already exists]");

        $db = new self();
        $db->name = $name;
        $db->meta_file = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "System" . DIRECTORY_SEPARATOR  . "Databases.adt";
        $db->load_data();
        return $db;
    }

    public static function create($name) :? self
    {
        if(!Auth::admin() && ! Auth::user()->can("insert", "System", "Databases"))

            throw new \Error("Error! you don't have the authority to edit another user.");

        if(self::exist($name))

            throw new \Error("Error! could not create database $name [database already exists]");

        mkdir(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $name);

        return self::connect($name);
    }

    public function drop($name, $imported = false) :? self
    {
        if(!Auth::admin() && ! Auth::user()->can("drop", $this->name))

            throw new \Error("Error! you don't have the authority to edit another user.");

        if($imported)
        {
            if( ! in_array($name, $this->imports))
        
                throw new \Error("Error! table $table is not imported.");
            
            $tmp = [];
            foreach ($this->imports as $table) 
            {
                if($table == $name)
                    continue;
                $tmp[] = $table;    
            }
            $this->imports = $tmp;
            $this->sync = false;
            $this->save_data();
            return true;
        }
        else
        {
            if( ! in_array($name, $this->imports))
        
                throw new \Error("Error! table $table is not in database " . $this->name . ".");

            $tmp = [];
            foreach ($this->owns as $table) 
            {
                if($table == $name)
                    continue;
                $tmp[] = $table;    
            }
            $this->owns = $tmp;
            unlink(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $name . ".adt");
            $this->sync = false;
            $this->save_data();
            return true;
        }
    }

    public function Unset()
    {
        if(!Auth::admin() && ! Auth::user()->can("unset", $this->name))

            throw new \Error("Error! you don't have the authority to edit another user.");

        foreach ($this->owns as $table) 
        {
            $this->drop($table);   
        }
        unlink(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $name);
    }
}