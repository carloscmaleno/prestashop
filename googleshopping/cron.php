<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/googleshopping.php');

$module=new GoogleShopping();
$module->generateFile();
die ('OK');

?>
