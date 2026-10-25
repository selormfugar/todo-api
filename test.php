<?php

require_once __DIR__ . '/vendor/autoload.php';

use TodoApi\Database;
use TodoApi\Todo;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ );
$dotenv->load();

//create database connection
$db = new Database();

//create todo object
$todo = new Todo($db);



echo "Database and todo object created successfully!";