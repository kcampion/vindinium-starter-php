<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

require('./Client.php');
$client = new Client();
$client->load();