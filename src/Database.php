<?php
/**
 * User: alex
 * Date: 11/25/18
 * Time: 9:13 PM
 */

namespace Luna\Andromeda\Sources;


class Database
{
    private $name;
    private $tables = [];
    private $imported = [];

    private $sync = false;

    private function load_data()
    {
        if (! $this->sync )
        {
            $file = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".."  . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "_INNER_" . DIRECTORY_SEPARATOR . ".adt");

            $file = json_decode($file, true);

            $this->tables = $file['tables'];

            $this->imported = $file['imported'];

            $this->sync = true;
        }
    }

    private function save_data()
    {

        $f = [
          "tables" => $this->tables,
          "imported" => $this->imported
        ];

        $f = json_encode($f);

        file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".."  . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "_INNER_" . DIRECTORY_SEPARATOR . ".adt",$f);

        $this->sync = true;
    }

    public function host($name, array $details = [])
    {
        $this->load_data();

        $folder = $this->name;

        if ( ! Table::exist($name, $folder) and ! array_key_exists($name, $this->tables))
        {
            $this->tables[] = $name;

            $this->sync = false;

            Table::create($name,$folder);

            $this->save_data();
        }
        else
            throw new \Error("Error! table already exist in database");
    }

    public static function connect($name) :? self
    {

    }

    public static function create($name) :? self
    {

    }

    public static function exist($name)
    {

    }
}