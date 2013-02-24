<?php


include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/syncg2k.php');

$module=new SyncG2k();
$module->cron();

?>