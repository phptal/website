<?php

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

require_once "PHPTAL.php";

$phptal = new PHPTAL(dirname(__FILE__)."/main.zpt");
$phptal->setOutputMode(PHPTAL::HTML5);
$phptal->conf = $conf;
$phptal->ID = $ID;



try
{
    echo $phptal->execute();
}
catch(Exception $e)
{
    echo '<h1>'.htmlspecialchars($e->getMessage()).'</h1><pre>'.htmlspecialchars($e).'</pre>';
}

