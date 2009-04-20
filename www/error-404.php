<?php

require dirname(__FILE__)."/../includes/common.php";

$phptal->setTemplate('404.html');
$phptal->pagename = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '???';

echo $phptal->execute();