<?php

require_once __DIR__ . "/Core/Router.php";

$router = new Router();

$router->add("table", "databases", NULL, NULL);
$router->add("table", "tables", "*", NULL);
$router->add("table", "records", "*", "[\w-]+(?:\?(?<parameters>(?:[\w-]+=[\w-]+&?)+))?");
//
$router->add("user", "signup", NULL, "signup");
$router->add("user", "login", NULL, "login");
$router->add("user", "logout", NULL, "logout");
$router->add("user", "reset-password", NULL, "reset-password");

$router->dispatch();
