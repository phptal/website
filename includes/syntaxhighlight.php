<?php

define("XHTMLNS",'http://www.w3.org/1999/xhtml');

class SyntaxFilter extends DOM_Filter
{

    private $doc;
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

            if (preg_match('/(?:^| )(php|xml)(?:$| )/',$node->getAttribute('class'),$m)) {
                $lang = $m[1];
            }
            else if (preg_match('/^\s*(\$[a-z_]|[a-z_][a-z0-9_:]*\()/si',$code)) {
                $lang = 'php';
            }
            else {
                $lang = 'xml';
            }

            if ($lang === 'php')
            {
                $this->filterPHPBlock($node,$code);
            }
            else
            {
                $this->filterXHTML($node,$code);
            }
        }
        return $doc;
    }

    private function filterXHTML(DOMElement $node, $code)
    {
        $parts = preg_split('/(<[a-z\/](?:[^<>"\']|"(?:[^<>"]|<\?.*?\?>)*"|\'(?:[^<>\']|<\?.*?\?>)\')*>)/si', $code,NULL,PREG_SPLIT_DELIM_CAPTURE);
        for($i=0; $i < count($parts); $i+=2)
        {
            if (strlen($parts[$i]))
            {
                $this->filterText($node,$parts[$i]);
            }
            if (isset($parts[$i+1]))
            {
                $tag = $this->span($node,'tag');
                $this->filterTag($tag,$parts[$i+1]);
            }
        }
    }

    private function filterTag(DOMElement $el, $tagcode)
    {
        $doc = $el->ownerDocument;

        if (preg_match('/(<[a-z0-9-]+)(.*)(\/?>)/si',$tagcode,$m))
        {
            $el->appendChild($el->ownerDocument->createTextNode($m[1]));

            $parts = preg_split('/(\s[a-z0-9:-]+)(\s*=\s*)("[^"]+"|\'[^\']+\')/si', $m[2],NULL,PREG_SPLIT_DELIM_CAPTURE);
            for($i=0; $i < count($parts); $i += 4)
            {
                if (strlen($parts[$i]))
                {
                    $el->appendChild($doc->createTextNode($parts[$i]));
                }
                if (isset($parts[$i+3]))
                {
                    $tales = false !== strpos($parts[$i+1],':');

                    $this->span($el,'atn',$parts[$i+1]);
                    $this->span($el,'pun',$parts[$i+2]);
                    $atv = $this->span($el,$tales ? 'atv tales' : 'atv');
                    $this->filterText($atv,$parts[$i+3]);
                }
            }

            $el->appendChild($doc->createTextNode($m[3]));
        }
        else
        {

            $el->appendChild($doc->createTextNode($tagcode));
        }
    }

    private function filterText(DOMElement $el, $text)
    {
        $doc = $el->ownerDocument;

        // prefilter needs $${, postfilter just ${
        $regex = '/(?:(&#?[a-z0-9]+;)|('.($this->prefilter ? '$' : '').'\${.*?})|(<\?(?!xml).*?\?>))()/si';
        $parts = preg_split($regex, $text, NULL, PREG_SPLIT_DELIM_CAPTURE);

        for($i=0; $i < count($parts); $i += 5)
        {
            if (strlen($parts[$i]))
            {
                $el->appendChild($doc->createTextNode($parts[$i]));
            }
            if (isset($parts[$i+3]) && strlen($parts[$i+3]))
            {
                $this->filterPHPBlock($el,$parts[$i+3]);
            }
            elseif (isset($parts[$i+2]) && strlen($parts[$i+2]))
            {
                $this->span($el,'tales',$parts[$i+2]);
            }
            elseif (isset($parts[$i+1]))
            {
                $this->span($el,'lit',$parts[$i+1]);
            }
        }
    }

    private function filterPHPBlock(DOMElement $el, $phpcode)
    {
        $phpblock = $this->span($el,'phpblock');
        if (preg_match('/^(<\?(?:php|=))(.*)(\?>)$/s',$phpcode,$m))
        {
            $this->span($phpblock,'phpblockpi',$m[1]);
            $this->filterPHPCode($phpblock,$m[2]);
            $this->span($phpblock,'phpblockpi',$m[3]);
        }
        else
        {
            $this->filterPHPCode($phpblock,$phpcode);
        }
    }

    private function filterPHPCode(DOMElement $el, $phpcode)
    {
        $doc = $el->ownerDocument;
        // the "(?:)()" construct forces last one to match, which ensures there's constant number of matched delimiters
        $parts = preg_split('/(?:(\/\/[^\n]*\n|\/\*[^\*]*\*\/)|("(?:[^"\\\\]+|\\\\.)*")|(\'(?:[^\'\\\\]+|\\\\.)*\')|(\\$[a-z_][a-z0-9_]*|(?<=->)(?>[a-z_][a-z0-9_]*)(?!\())|([^\sa-z0-9][^\sa-z0-9"\'$]*)|\b(as\b|return\b|function\b|class\b|implements\b|else(?:if)?\b|(?:include|require)(?:_once)?\b|endif\b|endforeach\b|[a-z_](?:[a-z0-9_]|::)(?=\()))()/si', $phpcode,NULL,PREG_SPLIT_DELIM_CAPTURE);

        for($i=0; $i < count($parts); $i += 8)
        {
            if (strlen($parts[$i]))
            {
                $el->appendChild($doc->createTextNode($parts[$i]));
            }
            if (isset($parts[$i+6]))
            {
                $this->span($el,'kwd',$parts[$i+6]);
            }
            if (isset($parts[$i+5]))
            {
                $this->span($el,'pun',$parts[$i+5]);
            }
            if (isset($parts[$i+4]))
            {
                $this->span($el,'var',$parts[$i+4]);
            }
            if (isset($parts[$i+3]))
            {
                $this->span($el,'str',$parts[$i+3]);
            }
            if (isset($parts[$i+2]))
            {
                $this->span($el,'str',$parts[$i+2]);
            }
            if (isset($parts[$i+1]))
            {
                $this->span($el,'com',$parts[$i+1]);
            }
        }
    }

    private function span(DOMElement $parent, $class = NULL, $text = NULL)
    {
        if ($text === '') return NULL;

        $newelement = $parent->ownerDocument->createElementNS(XHTMLNS, $class == 'var' ? 'var' : 'span');
        $parent->appendChild($newelement);
        if ($class && $class !== 'var') $newelement->setAttribute('class',$class);
        if ($text !== NULL) $newelement->appendChild($newelement->ownerDocument->createTextNode($text));
        return $newelement;
    }
}

/*
$geshi = new GeSHi('<tal:block tal:content="${hello &amp; & world}"><?php echo 123; ?></tal:block>', "xml");
echo $geshi->error();
echo $geshi->parse_code();
*/
