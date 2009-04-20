<?php

require_once './includes/common.php';

if (empty($argv[1]) || empty($argv[2]))
{
    echo "Usage: <PHPTAL template> <HTML output>\n";
    exit(1);
}

$from = $argv[1];
$to = $argv[2];

echo 'Parsing ', $from, " to $to... ";

$phptal->setTemplate($from);

try {
    $r = $phptal->execute();
}
catch (Exception $e){
    echo $e;
    exit(1);
}

if (!file_put_contents($to, $r))
{
    echo('Unable to open '.$out.' for writing'."\n");
    exit(1);
}

echo "saved\n";
