<?php

class Abbrizer implements PHPTAL_Filter
{
    function __construct(array $abbrs, $max = 2)
    {
        $this->max = $max;
        $this->abbrs = $abbrs;
        $this->regex = '/\b('.implode('|',array_keys($abbrs)).')(?:\b|(?=s\b))/';
        
    }
    
    function filter($txt)
    {
        $txt = preg_replace('/(<?xml[^>]*>)?(<!DOCTYPE[^>]*>)?(.+)/s','\1\2<tal:block 
            xmlns="http://www.w3.org/1999/xhtml" 
            xmlns:tal="http://xml.zope.org/namespaces/tal"
            xmlns:metal="http://xml.zope.org/namespaces/metal"
            xmlns:i18n="http://xml.zope.org/namespaces/i18n"
            xmlns:phptal="http://phptal.motion-twin.com/ns/phptal"
            >\3</tal:block>',$txt);
            
        $doc = new DOMDocument();
        $doc->loadXML($txt);
        
        $done = array();
        $lastabbrname = NULL;
        
        $xp = new DOMXPath($doc);
        $xp->registerNamespace('xhtml','http://www.w3.org/1999/xhtml');
        
        foreach($xp->query('//text()[
            not(ancestor::xhtml:code or 
                ancestor::xhtml:pre or 
                ancestor::xhtml:head or 
                ancestor::xhtml:script or 
                ancestor::xhtml:style or 
                ancestor::xhtml:acronym or 
                ancestor::xhtml:abbr)
            ]') as $node)
        {
            $parts = preg_split($this->regex, $node->nodeValue, NULL, PREG_SPLIT_DELIM_CAPTURE);
            
            if (count($parts) < 2) continue;
            
            $frag = $doc->createDocumentFragment();
            for($i=0; $i < count($parts); $i += 2)
            {
                $frag->appendChild($doc->createTextNode($parts[$i]));
                if (isset($parts[$i+1]))
                {
                    $abbrname = $parts[$i+1];
                    
                    $abbr = $doc->createElementNS('http://www.w3.org/1999/xhtml','abbr');
                    if ($lastabbrname !== $abbrname && isset($this->abbrs[$abbrname]) && (!isset($done[$abbrname]) || $done[$abbrname] < $this->max))
                    {
                        $abbr->setAttribute('title',$this->abbrs[$abbrname]);
                        if (!isset($done[$abbrname])) $done[$abbrname] = 1;
                        else $done[$abbrname]++;
                        $lastabbrname = $abbrname;
                    }
                    $abbr->appendChild($doc->createTextNode($abbrname));
                    $frag->appendChild($abbr);
                    
                }
            }
            $node->parentNode->replaceChild($frag,$node);
        }
        
        return $doc->saveXML();
    }
}

