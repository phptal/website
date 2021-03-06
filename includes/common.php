<?php

define('_PHPTAL_VERSION',      '1.3.0');
define('_PHPTAL_MAILING_LIST', 'http://lists.motion-twin.com/mailman/listinfo/phptal');
define('_PHPTAL_SUBVERSION',   'https://svn.motion-twin.com/phptal');
define('_PHPTAL_RSSHREF',      '/feed.xml');

define('TPL', dirname(__FILE__).'/../tpl/');

$path = dirname(__FILE__).'/../../phptal/classes/PHPTAL.php' ;
require_once $path;

require_once "abbrizer.php";
require_once "syntaxhighlight.php";

date_default_timezone_set('Europe/London');

function phptal_tales_iscurrent($src,$nothrow)
{
    return phptal_tales('current')." == ".phptal_tales("string:$src")."?'current':NULL";
}

class CodePreFilter extends PHPTAL_PreFilter
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

$abbrs = new Abbrizer(array(
'PHPTAL' => 'PHP Template Attribute Language',
'PHP' => 'PHP Hypertext Preprocessor',
'PHP5.3' => 'PHP Hypertext Preprocessor v5.3',
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
'PEAR2' => 'PHP Extension and Application Repository, v2',
'PEAR' => 'PHP Extension and Application Repository',
'WYSIWYG' => 'What You See Is What You Get',
'MIME' => 'Multipurpose Internet Mail Extensions',
'IE'=>'Internet Explorer',
'HTTP'=>'Hypertext Transfer Protocol',
'XSS'=>'Cross-Site Scripting',
'XSLT'=>'Extensible Stylesheet Language Transformations',
'CDATA'=>'Character data',
'SVN'=>'Subversion',
'SVG'=>'Scallable Vector Format',
'RDF'=>'Resource Description Framework',
'MathML'=>'Mathematical Markup Language',
'RSS'=>'(not) Really Simple Syndication',
));

$phptal = new PHPTAL();
$phptal->setTemplateRepository(TPL);
$phptal->addPreFilter(new CodePreFilter())->addPreFilter(new SyntaxFilter())->addPreFilter($abbrs)->addPreFilter(new PHPTAL_PreFilter_Compress());
$phptal->VERSION = _PHPTAL_VERSION;
$phptal->MAILING = _PHPTAL_MAILING_LIST;
$phptal->SUBVERS = _PHPTAL_SUBVERSION;
$phptal->RSSHREF = _PHPTAL_RSSHREF;

