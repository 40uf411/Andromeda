<?php

require_once "Andromeda.php";

use Luna\Andromeda\Andromeda;

Andromeda::init();

echo password_hash("root", PASSWORD_DEFAULT);
echo "\n";
$user = Andromeda::connect("root","root");

var_dump($user);