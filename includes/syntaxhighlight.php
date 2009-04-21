<?php

define("XHTMLNS",'http://www.w3.org/1999/xhtml');

class SyntaxFilter extends DOM_Filter
{
    function filterDOM(DOMDocument $doc)
    {        
        $xp = new DOMXPath($doc);
        $xp->registerNamespace('xhtml',XHTMLNS);
      
        foreach($xp->query('//xhtml:code') as $node)
        {
            if ($node->childNodes->length > 1)
            {
                continue; // unusable
            }
            
            $code = $node->textContent;
            
            while($node->firstChild) $node->removeChild($node->firstChild);
            
            $parts = preg_split('/(<[a-z\/](?:[^<>"]|<\?.*?\?>|"[^"]*")*>)/', $code,NULL,PREG_SPLIT_DELIM_CAPTURE);
            for($i=0; $i < count($parts); $i+=2)
            {
                if (strlen($parts[$i])) 
                {
                    $node->appendChild($doc->createTextNode($parts[$i]));
                }
                if (isset($parts[$i+1]))
                {
                    $node->appendChild($span = $doc->createElementNS(XHTMLNS,'span'));
                    $span->setAttribute('class','tag');
                    $span->appendChild($doc->createTextNode($parts[$i+1]));
                }
            }
        }
        
        return $doc;
    }
    
    private function filterCode($m)
    {
        $lang = empty($m[1]) ? 'xml' : $m[1];
        $code = html_entity_decode(strip_tags($m[2]),ENT_QUOTES,'UTF-8');
        
        htmlspecialchars($code);
        
        return $code;
    }
}

/*
$geshi = new GeSHi('<tal:block tal:content="${hello &amp; & world}"><?php echo 123; ?></tal:block>', "xml");
echo $geshi->error();
echo $geshi->parse_code();
*/