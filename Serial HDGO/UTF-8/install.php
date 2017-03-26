<?php
@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', dirname ( __FILE__ ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR . '/data/config.php';
require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';

$isUTF = ( strtolower( $config['charset'] ) == 'utf-8' ) ? true : false;


$charset = $isUTF ? 'utf8' : 'cp1251';
$thisFile = explode( DIRECTORY_SEPARATOR, __FILE__ );
$thisFile = end($thisFile);
$dataBase = $configuration = array( );
$isUpdate = $isUpdateTo = $metaRedirect = false;
$status = 'installed';

$db_charset = "{$config['charset']}";
$db_collate = "{$config['charset']}_general_ci";

$langData = array(
  'installed' => array(
    'title' => 'Установка успешно завершена.',
    'descr' => 'Не забудьте удалить файл установки <b>' . $thisFile . '</b>.',
  ),
  'title' => 'Serial HDGO',
);

if ( !$isUTF ) {
	$langData['title'] = iconv("WINDOWS-1251", "UTF-8", $langData['title']);
	$langData['desc'] = iconv("WINDOWS-1251", "UTF-8", $langData['desc']);
}

  $dataBase = $configuration = array( );
  $status = 'installed';
  
  $dataBase[] = "INSERT INTO `{PREFIX}_admin_sections` (`name`, `title`, `descr`, `icon`, `allow_groups`) VALUES ('serial_hdgo', 'Serial HDGO', 'Модуль обновления сериалов с HDGO', 'serial_hdgo.png', '1');";
  $dataBase[] = "CREATE TABLE IF NOT EXISTS `{PREFIX}_serial_hdgo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `news_id` int(10) NOT NULL,
  `voice` varchar(512) NOT NULL,
  `season` int(10) NOT NULL,
  `seria` int(10) NOT NULL,
  `date_update` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;";
  $dataBase[] = "ALTER TABLE `{PREFIX}_post` ADD `end_serial_hdgo` TINYINT( 1 ) NOT NULL DEFAULT  '0';";
if ( is_array( $dataBase ) and count( $dataBase ) > 0 ) {
  foreach ( $dataBase as $dataQuery ) {
    $db->query( str_replace( array( '{PREFIX}', '{CHAR}' ), array( PREFIX, $charset ), $dataQuery ) );
  }
}
echo '<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=' . str_replace( array( 'utf8', 'cp1251' ), array( 'UTF-8', 'windows-1251' ), $charset ) . '" />
  <title>' . $langData['title'] . '</title>
  ' . ( $metaRedirect ? '<meta http-equiv="refresh" content="1" />' : ''  ) . '
  <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400,300,600&amp;subset=latin,cyrillic" />
  <style>
  html, body { font: 8pt/18px "Open Sans", Arial, Helvetica, sans-serif; color: #333; background: #F1F1F1; text-shadow: #EEE 0px 1px; margin: 0; padding: 0 }
  .wrapper { width: 800px; margin: 20% auto 0; text-align: center; -webkit-box-shadow: rgba( 0, 0, 0, 0.3 ) 0 1px 3px; -moz-box-shadow: rgba( 0, 0, 0, 0.3 ) 0 1px 3px; box-shadow: rgba( 0, 0, 0, 0.3 ) 0 1px 3px; border-color: #E5E5E5 #DBDBDB #D2D2D2; border-radius: 4px; background: #FFF; padding: 30px }
  .wrapper .title { font-size: 28pt; font-weight: 700; line-height: 40pt }
  .wrapper .result { margin-top: 10px }
  .wrapper .explay { margin-top: 15px }
  .wrapper .explay a { text-decoration: none; color: #787878 }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="title">' . $langData['title'] . '</div>
    <div class="result">' . $langData[$status]['title'] . '</div>
    ' . ( empty( $langData[$status]['descr'] ) ? '' : '<div class="descr">' . $langData[$status]['descr'] . '</div>
    <div class="explay"><a href="https://gameer.name/" target="_blank">Поддержка модуля</a></div>' ) . '
  </div>
</body>
</html>';
?>