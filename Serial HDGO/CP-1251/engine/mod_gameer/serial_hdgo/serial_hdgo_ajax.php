<?PHP
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
@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -30 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR . '/data/config.php';
require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/templates.class.php';

if ($config['version_id'] > 9.6)
	dle_session();
else
	@session_start();
@header( "Content-type: text/html; charset=" . $config['charset'] );

define( 'TEMPLATE_DIR', ROOT_DIR . '/templates/' . $config['skin'] );

if ($config['version_id'] > 10.2)
{
	date_default_timezone_set($config['date_adjust']);
	$_TIME = time();
}
else
	$_TIME = time() + ($config['date_adjust'] * 60);

$news_id = isset($_POST["news_id"]) ? intval($_POST["news_id"]) : false;
if(!$news_id) return;

$player = isset($_POST["player"]) ? strip_tags($_POST["player"]) : false;

$seria_id = isset($_POST["seria_id"]) ? intval($_POST["seria_id"]) : false;
if($seria_id !== false)
{
	$video_id = isset($_POST["video_id"]) ? strip_tags($_POST["video_id"]) : false;
	$video_id = explode("episode", $video_id);
	set_cookie("seria_" . $news_id, $seria_id, 7);
	echo $video_id[0] . "episode=" . $seria_id;
}
elseif($player == "yes")
{
	$tpl = new dle_template();
	$tpl->dir = TEMPLATE_DIR;
	include ENGINE_DIR . "/mod_gameer/serial_hdgo/serial_hdgo.php";
}
else
{
	$translator_id = isset($_POST["translator_id"]) ? intval($_POST["translator_id"]) : false;
	if(!$translator_id) return;

	if(isset($_COOKIE["trans_" . $news_id]) && $translator_id != $_COOKIE["trans_" . $news_id])
	{
		unset($_COOKIE["trans_" . $news_id]);
		unset($_COOKIE["season_" . $news_id]);
		unset($_COOKIE["seria_" . $news_id]);
		set_cookie("trans_" . $news_id, "", 1);
		set_cookie("season_" . $news_id, "", 1);
		set_cookie("seria_" . $news_id, "", 1);
	}

	$season_id = isset($_POST["season_id"]) ? intval($_POST["season_id"]) : false;
	if($season_id && $season_id > 0)
	{
		if(isset($_COOKIE["season_" . $news_id]) && $_COOKIE["season_" . $news_id] != $season_id)
		{
			unset($_COOKIE["seria_" . $news_id]);
			set_cookie("seria_" . $news_id, "", 1);
		}
		set_cookie("season_" . $news_id, $season_id, 7);
		$_COOKIE["season_" . $news_id] = $season_id;
	}

	set_cookie("trans_" . $news_id, $translator_id, 7);
	$_COOKIE["trans_" . $news_id] = $translator_id;

	$ajax_season = true;

	$tpl = new dle_template();
	$tpl->dir = TEMPLATE_DIR;
	include ENGINE_DIR . "/mod_gameer/serial_hdgo/serial_hdgo.php";
}
?>