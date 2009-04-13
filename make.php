<?php

define('_PHPTAL_VERSION',      '1.1.15');
define('_PHPTAL_MAILING_LIST', 'http://lists.motion-twin.com/mailman/listinfo/phptal');
define('_PHPTAL_SUBVERSION',   'https://svn.motion-twin.com/phptal');
define('_PHPTAL_RSSHREF',      '/feed.xml');


define('IN', 'src/');
define('OUT', 'www/');
define('TPL', 'tpl/');

define('PHPTAL_FORCE_REPARSE',1);

require_once 'PHPTAL.php';
require_once 'PHPTAL/Filter.php';

date_default_timezone_set('Europe/London');


function phptal_tales_iscurrent($src,$nothrow)
{
    return phptal_tales('current')." == ".phptal_tales("string:$src")."?'current':NULL";
}

class CodePreFilter implements PHPTAL_Filter
{
    function filter($txt)
    {
        while (preg_match('/\s*?<!--code\s+(.*?)\s+-->\s+/is', $txt, $m)){
            list($src,$data) = $m;
            $data = htmlentities($data, ENT_COMPAT, 'UTF-8');
            $txt = str_replace($src,$data,$txt);
        }
        return $txt;
    }
}

class MultiFilter implements PHPTAL_Filter
{
    function __construct(array $filters)
    {
        $this->filters = $filters; 
    }
    private $filters;
    function filter($txt)
    {
        foreach($this->filters as $f) $txt = $f->filter($txt);
        return $txt;
    }
}

require_once "abbrizer.php";

$abbrs = new Abbrizer(array(
'PHPTAL' => 'PHP Template Attribute Language',
'PHP' => 'PHP Hypertext Preprocessor',
'PHP5' => 'PHP Hypertext Preprocessor v5',
'SPL' => 'Standard PHP Library',
'XML' => 'eXtensible Markup Language',
'HTML' => 'Hypertext Markup Language',
'HTML5' => 'Hypertext Markup Language v5',
'XHTML' => 'eXtensible Hypertext Markup Language',
'XHTML5' => 'HTML5 sent as application/xhtml+xml',
'JSP' => 'JavaServer Pages',
'ASP' => 'Active Server Pages',
'ZPT' => 'Zope Page Templates',
'METAL' => 'Macro Expansion for TAL',
'TAL' => 'Template Attribute Language',
'I18N' => 'Internationalisation',
'TALES' => 'Template Attribute Language Expression Syntax',
'PHPTALES' => 'PHP Template Attribute Language Expression Syntax',
'URI' => 'Uniform Resource Identifier',
'URL' => 'Uniform Resource Locator',
'GNU' => 'GNU\'s Not Unix',
'GPL' => 'GNU General Public License',
'LGPL' => 'GNU Lesser General Public License',
'FAQ' => 'Frequently Asked Questions',
'DOM' => 'Document Object Model',
'W3C' => 'WWW Consortium',
'ASCII' => 'American Standard Code for Information Interchange',
'PDF' => 'Portable Document Format',
'PEAR' => 'PHP Extension and Application Repository',
'WYSIWYG' => 'What You See Is What You Get',
'MIME' => 'Multipurpose Internet Mail Extensions',
'IE'=>'Internet Explorer',
'HTTP'=>'Hypertext Transfer Protocol',
'XSS'=>'Cross-Site Scripting',
));


$d = dir(IN);
while ($entry = $d->read()){
    $realPath = IN . $entry;
    if (is_file($realPath) && $entry[0] != '.'){
        echo '* parsing ', $entry, "\n";
        $t = new PHPTAL($realPath);
        $t->setTemplateRepository(TPL);
        $t->setPreFilter(new MultiFilter(array(new CodePreFilter(),$abbrs)));
        $t->VERSION = _PHPTAL_VERSION;
        $t->MAILING = _PHPTAL_MAILING_LIST;
        $t->SUBVERS = _PHPTAL_SUBVERSION;
        $t->RSSHREF = _PHPTAL_RSSHREF;
        
        try {
            $r = $t->execute();
        }
        catch (Exception $e){
            die($e);
        }

        $out = OUT . $entry;
        $fp = fopen($out, 'w');
        if (!$fp){
            die('Unable to open '.$out.' for writing'."\n");
        }
        fwrite($fp, $r);
        fclose($fp);
    }
}

