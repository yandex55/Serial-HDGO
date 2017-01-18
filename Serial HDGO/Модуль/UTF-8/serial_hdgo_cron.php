<?php
/*
=====================================================
Serial HDGO
-----------------------------------------------------
Автор : Gameer
-----------------------------------------------------
Site : http://gameer.name/
-----------------------------------------------------
Copyright (c) 2017 Gameer
=====================================================
Данный код защищен авторскими правами
*/

@error_reporting ( E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );

$allow_cron_serial_block = 1;

if ($allow_cron_serial_block) {

	define('DATALIFEENGINE', true);
	define('LOGGED_IN', true);
	define('ROOT_DIR', dirname (__FILE__));
	define('ENGINE_DIR', ROOT_DIR.'/engine');
	
	include ENGINE_DIR.'/data/config.php';
	include ENGINE_DIR . "/data/serial_hdgo.php";
	
	require_once ENGINE_DIR.'/classes/mysql.php';
	require_once ENGINE_DIR.'/data/dbconfig.php';
	require_once ENGINE_DIR.'/modules/functions.php';
	
	
	date_default_timezone_set ( $config['date_adjust'] );
	$cron_serial_block = true;
	$sql = $db->query("SELECT id, title, date, category, xfields, first_title, end_serial FROM " . PREFIX . "_post WHERE end_serial!=1 AND approve=1");
	while($row_moonwalk = $db->get_row($sql))
	{
		$news_id = $row_moonwalk["id"];
		include ENGINE_DIR . "/mod_gameer/serial_hdgo/serial_hdgo.php";
	}
	die ("done");
}
?>