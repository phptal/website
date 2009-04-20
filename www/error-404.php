<?php

$URI = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '???';

$fixes = array(
'ar01\.html$' => 'introduction.html',
'ar02\.html$' => 'whyusephptal.html',
'ar03\.html$' => 'installation.html',
'ar04\.html$' => 'firstexample.html',
'ar05\.html$' => 'attributelanguage.html',
'ar05s02\.html$' => 'tal-namespace.html',
'ar05s03\.html$' => 'metal.html',
'ar05s04\.html$' => 'i18n.html',
'ar05s05\.html$' => 'phptal-namespace.html',
'ar05s06\.html$' => 'phptal-blocks.html',
'ar05s07\.html$' => 'phptales.html',
'ar06\.html$' => 'phpintegration.html',
'ar06s02\.html$' => 'configuration.html',
'ar06s03\.html$' => 'phptal-class.html',
'ar06s04\.html$' => 'filter-interface.html',
'ar06s05\.html$' => 'trigger-interface.html',
'ar06s06\.html$' => 'translation-interface.html',
'ar06s07\.html$' => 'gettext.html',
'ar07\.html$' => 'sysadmin.html',
'ar08\.html$' => 'usefullinks.html',
'ar09\.html$' => 'greetings.html',
);

$origURI = $URI;

foreach($fixes as $pattern => $replace)
{
    if (preg_match('/'.$pattern.'/', $URI))
    {
        $URI = preg_replace('/'.$pattern.'/', $replace, $URI);
    }
}

if ($origURI !== $URI)
{
    $URI = "http://".$_SERVER["HTTP_HOST"].$URI;
    header("Location: ".$URI);
    header("Content-Type:text/plain;charset=UTF-8");
    die($URI);
}

header("HTTP/1.1 404 bzzt");

require dirname(__FILE__)."/../includes/common.php";

$phptal->setTemplate('404.html');
$phptal->pagename = ltrim($URI,'/');
echo $phptal->execute();
