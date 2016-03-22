<?php

abstract class DOM_Filter implements PHPTAL_Filter
{
    public $prefilter = true;

    function filter($txt)
    {
        if ($this->prefilter)
        {
        $txt = preg_replace('/(<\?xml[^>]*>)?(<!DOCTYPE[^>]*>)?(.+)/s','\1\2 <tal:block xmlns="http://www.w3.org/1999/xhtml" xmlns:tal="http://xml.zope.org/namespaces/tal" xmlns:metal="http://xml.zope.org/namespaces/metal" xmlns:i18n="http://xml.zope.org/namespaces/i18n" xmlns:phptal="http://phptal.motion-twin.com/ns/phptal"> \3 </tal:block>',$txt);
        }

        $doc = new DOMDocument('1.0','UTF-8');
        if (!@$doc->loadXML($txt))
        {
            return $txt;
        }
        $doc = $this->filterDOM($doc);
        return $doc->saveXML();
    }

    abstract function filterDOM(DOMDocument $doc);
}

