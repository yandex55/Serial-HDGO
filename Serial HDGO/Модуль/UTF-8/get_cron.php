<?PHP
define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname (__FILE__));
define('ENGINE_DIR', ROOT_DIR.'/engine');
require_once ENGINE_DIR.'/data/config.php';
?>

Поставить на крон сервера вызов, каждые 60 минут:
<pre>
*/59 * * * *	wget --delete-after "<?php echo $config['http_home_url']; ?>serial_hdgo_cron.php" &gt;/dev/null 2&gt;&1
</pre>
или
<pre>
*/59 * * * *	php -f <?php echo $_SERVER['DOCUMENT_ROOT']; ?>/serial_hdgo_cron.php &gt;/dev/null 2&gt;&1
</pre>