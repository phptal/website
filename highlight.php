<?php

require_once './includes/common.php';

if (empty($argv[1]) || empty($argv[2]))
{
    echo "Usage: <XHTML input> <XHTML output>\n";
    exit(1);
}

$from = $argv[1];
$to = $argv[2];

if ($to == '-') $to = 'php://stdout';

echo 'Highlighting ', $from, " to $to... ";

class NeutralizeTALES extends PHPTAL_PreFilter
{
    function filter($src)
    {
        return preg_replace_callback('/\$+{/',array('self','escape'), $src);
    }

    private function escape($m)
    {
        $m = rtrim($m[0],'{');
        return $m.$m.'{';
    }
}

$phptal->addPreFilter(new NeutralizeTALES());

try {
    $phptal->setTemplate($from);
    $r = $phptal->execute();
}
catch (Exception $e){
    echo $e;
    exit(1);
}

if (!file_put_contents($to, $r))
{
    fwrite(STDERR,'Unable to open '.$to.' for writing'."\n");
    exit(1);
}

echo "saved\n";
