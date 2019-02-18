<?php
/**
 * User: alex
 * Date: 11/24/18
 * Time: 7:44 PM
 */

namespace Luna\Andromeda\Driver;

class Json
{
    protected $connection = false;

    protected $lock = false;

    protected $db;

    protected $pass = "";

    protected $config;

    protected $query = [
        "action" => [],
        "select" => "*",
        "tables" => [] ,
        "where" => [],
    ];


    /**
     * AndromedaDriver constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }
}