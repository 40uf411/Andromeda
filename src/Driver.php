<?php
/**
 * User: alex
 * Date: 11/24/18
 * Time: 7:05 PM
 */

namespace Luna\Andromeda\Sources;


class Driver
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