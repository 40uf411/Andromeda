<?php

/* TODO
*
*
*/

namespace Luna\Andromeda\Sources;

use Luna\Andromeda\Authentication\Auth;

class Table
{
    private $sync = false;

    private $name;
    private $folder;

    private $max_records = null;
    private $num_records = 0;
    private $indexes = 0;
    private $schema = [];
    private $size;

    private $importdBy = [];

    private $meta_file = "";
    private $data_file = "";

    private $data = [];

    private function __construct(){}

    public function __get($name)
    {
        switch($name)
        {
            case "name":
                return $this->name;
                break;
            case "folder":
                return $this->folder;
                break;
            case "max_records":
                return $this->max_records;
                break;
            case "num_records":
                return $this->num_records;
                break;
            case "indexes":
                return $this->indexes;
                break;
            case "schema":
                return $this->schema;
                break;
            case "importdBy":
                return $this->importdBy;
                break;
            case "data":
                if( ! Auth::admin() || ! Auth::user()->can("update", $this->folder, $this->name) )
            
                    throw new \Error("Error! you don't have the authority to edit another user.");

                return $this->data;
                break;
        }
    }

    private function load_data($full = false)
    {
        if (! $this->sync )
        {
            $file = file_get_contents($this->data_file);
            $file = json_decode($file,true);

            if ($full)
            {
                $meta = file_get_contents($this->meta_file);
                $meta = json_decode($meta,true)[$name];

                $this->max_records = $meta['max_records'];
                $this->num_records = $meta['num_records'];
                $this->format = $meta['format'];
                $this->indexes = $meta['indexes'];
                $this->size = filesize($this->data_file);
            }
            $this->data = $file['data'];

            $this->sync = true;
        }
    }

    private function save_data()
    {
        $meta = file_get_contents($this->meta_file);
        $meta = json_decode($meta,true);

        $meta[$this->name . "@" . $this->folder]["max_records"] = $this->max_records;
        $meta[$this->name . "@" . $this->folder]["num_records"] = $this->num_records;
        $meta[$this->name . "@" . $this->folder]["indexes"] = $this->indexes;
        $meta[$this->name . "@" . $this->folder]["schema"] = $this->schema;
        $meta[$this->name . "@" . $this->folder]["importdBy"] = $this->importdBy;

        file_put_contents($this->meta_file, \json_encode($meta));

        file_put_contents($this->data_file, $this->data);

        $this->sync = true;
    }

    public function insert(array $data)
    {
        if( ! Auth::admin() && ! Auth::user()->can("update", $this->folder, $this->name) )
        
            throw new \Error("Error! you don't have the authority to edit another user.");

        $this->data = $data;
        $this->sync = false;

        return $this;
    }
    public function commit()
    {
        if( ! Auth::admin() && ! Auth::user()->can("update", $this->folder, $this->name) )
        
            throw new \Error("Error! you don't have the authority to edit another user.");

        $this->save_data();
    }

    public static function open($name, $folder) :? self
    {
        if( ! Auth::admin() && ! Auth::user()->can("select", $folder, $name) )
        
            throw new \Error("Error! you don't have the authority to edit another user.");

        if ( ! self::exist($name, $folder))
            
            throw new \Error("Error! table $name doesn't exist exist in folder $folder.");
        
        $t = new self();

        $t->name = $name;
        
        $t->load_data(true);

        return $t;
    }

    public static function create($name, $folder, array $details = [], $override = false) :? self
    {
        if( ! Auth::admin() && ! Auth::user()->can("create", $folder) )

            throw new \Error("Error! you don't have the authority to edit another user.");

        elseif ( self::exist($name, $folder) and  ! $override )

            throw new \Error("Error! Table $name already exists in folder $folder.");

        elseif ( $name == "@db" )

            throw new \Error("Error! Table can't be named @db.");

        else
        {
            $t = new self();
            $t->name = $name;
            $t->folder = $folder;
            $t->schema = (array_key_exists("schema",$details))? $details["schema"]: [];
            $t->max_records = (array_key_exists("max_records",$details))? $details["max_records"]: 0;
            $t->indexes = (array_key_exists("indexes",$details))? $details["indexes"]: [];

            $t->meta_file = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "System" . DIRECTORY_SEPARATOR  . "Tables.adt";
            $t->data_file = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $name . ".adt";

            file_put_contents($t->data_file,"[]");

            $t->save_data();
            return $t;
        }

    }
    public function drop()
    {
        foreach ($this->importdBy as $db)
        {
            Database::connect($db)->drop($this->name . "@" . $this->folder, true);
        }

        Database::connect($this->folder)->drop($this->name);

        $meta = file_get_contents($this->meta_file);
        $meta = json_decode($meta,true);
        unset($meta[$this->name . "@" . $this->folder]);
        file_put_contents($this->meta_file, \json_encode($meta));
        \unlink($this->data_file);        
    }

    public static function exist($name, $folder)
    {
        $meta_file =(string) __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "System" . DIRECTORY_SEPARATOR  . "Tables.adt";

        $meta_file = \file_get_contents($meta_file);
        $meta_file= \json_decode($meta_file, true);
        return array_key_exists("$name@$folder",$meta_file);
    }

    public function clone($name, $folder, $override = false)
    {
        if( ! Auth::admin() && ! Auth::user()->can("select", $this->folder) && ! Auth::user()->can("create", $folder) )

            throw new \Error("Error! you don't have the authority to edit another user.");

        
        elseif ( self::exist($name, $folder) and ! $override )

            throw new \Error("Error! Table $name already exists in folder $folder.");

            $t = new self();
            $t->name = $name;
            $t->folder = $folder;
            $t->schema = $this->schema;
            $t->max_records = $this->max_records;
            $t->num_records = $this->num_records;
            $t->indexes = $this->indexes;
            $t->data = $this->data;

            $t->meta_file = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "System" . DIRECTORY_SEPARATOR  . "Tables.adt";
            $t->data_file = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $name . ".adt";

            file_put_contents($t->data_file,"[]");

            $t->save_data();
            return $t;
    }

}