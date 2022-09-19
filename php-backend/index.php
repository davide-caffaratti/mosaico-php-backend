<?php
require (dirname(__FILE__).'/lib/config.inc.php');
include(dirname(__FILE__).'/interface-lang/'.$config['USED_LANG'].'.inc.php');
include(dirname(__FILE__) . '/lib/autoload.inc.php');
Autoloader::init();
Mosaico_Server::get();
