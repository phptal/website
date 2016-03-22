<?php

require_once dirname(__FILE__).'/common.php';

echo new_phptal_instance()->setOutputMode(PHPTAL::XHTML)->setTemplate('feed.xml')->set('news',get_news_items())->execute();
