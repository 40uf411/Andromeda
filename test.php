<?php

require_once "Andromeda.php";

use Luna\Andromeda\Andromeda;
use Luna\Andromeda\Authentication\Auth;
use Luna\Andromeda\Sources\Table;
use Luna\Andromeda\Sources\Database;

Andromeda::init();

# echo password_hash("root", PASSWORD_DEFAULT);
echo "\n";
# root:root
$user = Andromeda::connect("root","root");
#var_dump(Auth::admin());
#var_dump(Auth::user()->can("update", "System", "Users"));
#$u = $user->create_user("Guest");
#$u->set_privilege("select", "value", "users");
#$u->remove_privilege("insert", "value");
#var_dump($u->can("select", "value"));
Database::create("test");