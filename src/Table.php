<?php
/**
 * User: alex
 * Date: 11/24/18
 * Time: 11:17 PM
 */

namespace Luna\Andromeda\Sources;

class Table
{
    private $sync = false;

    private $name;
    private $folder;

    private $max_records = null;
    private $num_records = 0;
    private $format = [];
    private $size;

    private $data = [];

    private function __construct(){}

    private function load_data($full = false)
    {
        if (! $this->sync )
        {
            $file = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . $this->folder . DIRECTORY_SEPARATOR . $this->name . ".adt");
            $file= json_decode($file,true);

            if ($full)
            {
                $this->max_records = $file['meta']['max_records'];
                $this->num_records = $file['meta']['num_records'];
                $this->format = $file['meta']['format'];
                $this->size = filesize(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . $this->folder . DIRECTORY_SEPARATOR . $this->name . ".adt");
            }
            $this->data = $file['data'];
        }
    }

    private function save_data()
    {
        $file = [
            "meta" => [
                "folder" => $this->folder,
                "max_records" => $this->max_records,
                "num_records" => $this->num_records,
                "format" => $this->format,
            ],
            "data" => $this->data
        ];
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Data" . $this->folder . DIRECTORY_SEPARATOR . $this->name . ".adt", $file);
    }

    public function json()
    {
        $this->load_data();

        return json_encode($this->data);
    }

    public static function open($name, $folder) :? self
    {
        if (self::exist($name, $folder))
        {
            $t = new self();
            $t->name = $name;
            $t->load_data(true);

            return $t;
        }
        else
            throw new \Error("Error! table $name doesn't exist exist in folder $folder.");
    }

    public static function create($name, $folder, array $details = [], $override = false) :? self
    {
        if ( ! self::exist($name, $folder) and $override )

            throw new \Error("Error! Table $name already exists in folder $folder.");

        else
        {
            $t = new self();
            $t->name = $name;
            $t->folder = $folder;
            $t->format = (array_key_exists("format",$details))? $details["format"]: [];
            $t->max_records = (array_key_exists("max_records",$details))? $details["max_records"]: [];

            $t->save_data();
            return $t;
        }

    }

    public static function exist($name, $folder)
    {
        return file_exists(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $name . ".adt");
    }
}