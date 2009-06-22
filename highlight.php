<?php

require_once './includes/common.php';

if (empty($argv[1]) || empty($argv[2]))
{
    echo "Usage: <XHTML input> <XHTML output>\n";
    exit(1);
}

$from = $argv[1];
$to = $argv[2];

echo 'Highlighting ', $from, " to $to... ";

$filter = new SyntaxFilter();
$filter->prefilter = false;

try {
    $r = file_get_contents($from);
    
    $r = $filter->filter($r);
}
catch (Exception $e){
    echo $e;
    exit(1);
}

if (!file_put_contents($to, $r))
{
    echo('Unable to open '.$to.' for writing'."\n");
    exit(1);
}

echo "saved\n";
