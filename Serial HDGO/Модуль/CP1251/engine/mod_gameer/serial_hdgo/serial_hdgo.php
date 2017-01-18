<?PHP
defined("DATALIFEENGINE") || die("Hack");

if($cron_serial_block !== true)
	$cron_serial_block = false;

if($cron_serial_block === false)
{
	$news_id = intval($news_id) > 0 ? intval($news_id) : intval($_GET["newsid"]);
	if(intval($news_id) <= 0) return;
}

if($cron_serial_block === false)
	include ENGINE_DIR . "/data/serial_hdgo.php";

if($cron_serial_block === false)
	$row_hdgo = $db->super_query("SELECT title, date, category, xfields, first_title_hdgo, end_serial_hdgo FROM " . PREFIX . "_post WHERE id=$news_id");

if($row_hdgo["end_serial_hdgo"] == 1) return;
if(!$row_hdgo["xfields"]) return;

$cat_serial = explode(",", $serial_hdgo["cat_serial"]);
$cat_anime = explode(",", $serial_hdgo["cat_anime"]);
$cat_arr_ex = explode(",", $row_hdgo["category"]);

$serial_cat = false; $anime_cat = false;

foreach($cat_arr_ex as $val_cat)
{
	if(in_array($val_cat, $cat_serial))
	{
		$serial_cat = true;
		break;
	}
	elseif(in_array($val_cat, $cat_anime))
	{
		$anime_cat = true;
		break;
	}
}

if(!$serial_cat && !$anime_cat) return;

$xf_hdgo = xfieldsdataload( $row_hdgo['xfields'] );
$id_kp = false;

if($serial_cat)
{
	$api_send = "kinopoisk_id";
	$id_kp = $xf_hdgo[$serial_hdgo["id_kp"]];
}
elseif($anime_cat)
{
	if($xf_hdgo[$serial_hdgo["world_art"]])
	{
		$api_send = "world_art_id";
		$id_kp = $xf_hdgo[$serial_hdgo["world_art"]];
	}
	elseif($xf_hdgo[$serial_hdgo["id_kp"]])
	{
		$api_send = "kinopoisk_id";
		$id_kp = $xf_hdgo[$serial_hdgo["world_art"]];
	}
	else return;
}

$season = isset($xf_hdgo[$serial_hdgo["season"]]) ? intval(trim(str_ireplace("сезон", "", $xf_hdgo[$serial_hdgo["season"]]))) : 1;
$seria = isset($xf_hdgo[$serial_hdgo["seria"]]) ? intval(trim(str_ireplace("серия", "", $xf_hdgo[$serial_hdgo["seria"]]))) : 0;

if(!$id_kp) return;
$check_voice_update = false;

if(!function_exists('dle_cached_hdgo'))
{
	function dle_cached_hdgo($prefix)
	{
		global $config;
		
		$config['clear_cache'] = (intval($config['clear_cache']) > 1) ? intval($config['clear_cache']) : 0;
		$buffer = @file_get_contents( ENGINE_DIR . "/cache/hdgo/" . $prefix . ".json" );

		if ( $buffer !== false AND $config['clear_cache'] )
		{
			$file_date = @filemtime( ENGINE_DIR . "/cache/hdgo/" . $prefix . ".json" );
			$file_date = time()-$file_date;

			if ( $file_date > ( $config['clear_cache'] * 60 ) )
			{
				$buffer = false;
				@unlink( ENGINE_DIR . "/cache/hdgo/" . $prefix . ".json" );
			}
		}
		return $buffer;
	}
}
if(!function_exists('create_cached_hdgo_hdgo'))
{
	function create_cached_hdgo($prefix, $cache_text)
	{
		file_put_contents (ENGINE_DIR . "/cache/hdgo/" . $prefix . ".json", $cache_text, LOCK_EX);
		@chmod( ENGINE_DIR . "/cache/hdgo/" . $prefix . ".json", 0666 );
	}
}

if(!function_exists('requestToHDGO'))
{
	function requestToHDGO($url)
	{
		if($ch = curl_init())
		{
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
			$data = curl_exec($ch);
			curl_close($ch);
		}
		else
			$data = file_get_contents($url);
		return $data;
	}
}

$cache_hdgo = false;
$cache_hdgo = dle_cached_hdgo("hg_" . $id_kp);

if($cache_hdgo)
{
	$data_new = json_decode($cache_hdgo, true);
}
else
{
	$data = requestToHDGO("http://hdgo.cc/api/video.json?{$api_send}={$id_kp}&token={$serial_hdgo['token']}");
	create_cached_hdgo("hg_" . $id_kp, $data);
	$data_new = json_decode( $data, true );
}

$thistime = date("Y-m-d H:i:s", time());

$max_seria_array = array();
$season_array = array();
$translator_array = array();
$max_season_by_voice = array();

foreach($data_new as $key => $val)
{
	if($val['id_hdgo'] == NULL || !$val['id_hdgo']) continue;
	$title_ru_hdgo = iconv("utf-8", "cp1251", $val['title']);
	$translator_array[] = iconv("utf-8", "cp1251", $val['translator']);
	$voice_data = false;
	$voice_data = requestToHDGO("http://hdgo.cc/api/serial_episodes.json?id={$val['id_hdgo']}&token={$serial_hdgo['token']}");
	$voice_data = json_decode($voice_data, true);
	foreach($voice_data["season_episodes_count"] as $pepsi => $liter)
		$max_season_by_voice[$val['id_hdgo']][] = $liter["season_number"];
		
	$max_season_by_voice_max = max($max_season_by_voice[$val['id_hdgo']]);
	$season_array[] = $max_season_by_voice_max;
	
	if($serial_hdgo["season_serial_site"] != 1) $season = $max_season_by_voice_max;
	
	foreach($voice_data["season_episodes_count"] as $index => $value)
	{
		if($value["season_number"] == $season)
		{
			$voice_data['serial']['translator'] = $db->safesql($voice_data["serial"]["translator"]);
			$voice_data['serial']['translator'] = iconv("utf-8", "cp1251", $voice_data['serial']['translator']);
			if($serial_hdgo["season_serial_site"] == 1)
				$seria_data = $db->super_query("SELECT seria, season FROM " . PREFIX . "_serial_hdgo WHERE news_id=$news_id AND voice='{$voice_data['serial']['translator']}' AND season=$season");
			else
				$seria_data = $db->super_query("SELECT seria, season FROM " . PREFIX . "_serial_hdgo WHERE news_id=$news_id AND voice='{$voice_data['serial']['translator']}'");
			
			$max_seria = $value["episodes_count"];
			
			if($serial_hdgo["season_serial_site"] != 1)
				$max_seria_array[$season][] = $max_seria;
			else
				$max_seria_array[] = $max_seria;
			
			if($seria_data != NULL && intval($seria_data["seria"]) >= 0 && $max_seria > $seria_data["seria"] && ($serial_hdgo["season_serial_site"] == 1 || $serial_hdgo["season_serial_site"] != 1 && $serial_hdgo["season"] && $season > $seria_data["season"]))
			{
				
				$thistime = date("Y-m-d H:i:s", time());
				
				if($serial_hdgo["season_serial_site"] == 1 && $max_seria > $seria_data["seria"])
				{
					$check_voice_update = true;
					$db->query("UPDATE " . PREFIX . "_serial_hdgo SET seria=$max_seria, date_update='{$thistime}' WHERE news_id=$news_id AND voice='{$voice_data['serial']['translator']}' AND season=$season");
				}
				else
				{
					if($seria_data["season"] < $season || $max_seria > $seria_data["seria"])
					{
						$check_voice_update = true;
						$db->query("UPDATE " . PREFIX . "_serial_hdgo SET seria=$max_seria, season=$season, date_update='{$thistime}' WHERE news_id=$news_id AND voice='{$voice_data['serial']['translator']}'");
					}
				}
				
			}
			else if(!$seria_data["season"] || $seria_data["season"] == NULL)
			{
				if(intval($max_seria) > intval($seria))
					$date_insert = $thistime;
				else
					$date_insert = $row_hdgo['date'];
				$db->query("INSERT INTO " . PREFIX . "_serial_hdgo (news_id, voice, season, seria, date_update) VALUES ($news_id, '{$voice_data['serial']['translator']}', $season, $max_seria, '{$date_insert}');");
			}
			
		}
	}
}

$max_all_season = max($season_array);

if($serial_hdgo["season_serial_site"] != 1)
	$max_all_seria = max($max_seria_array[$max_all_season]);
else
	$max_all_seria = max($max_seria_array);

if($check_voice_update || $seria < $max_all_seria && $serial_hdgo["season_serial_site"] == 1 || ($seria < $max_all_seria || $season < $max_all_season) && $serial_hdgo["season_serial_site"] != 1)
{
	if(!$row_hdgo["first_title_hdgo"])
	{
		if($serial_hdgo["where_get_title"] == "post_or_title")
		{
			$row_hdgo["title"] = $db->safesql($row_hdgo["title"]);
			$upd_first_title = ", first_title_hdgo='{$row_hdgo["title"]}'";
		}
		elseif($serial_hdgo["where_get_title"] == "hdgo_get_title")
		{
			$row_hdgo["title"] = $db->safesql($title_ru_hdgo);
			$upd_first_title = ", first_title_hdgo='{$row_hdgo["title"]}'";
		}
		else
		{
			if($xf_hdgo[$serial_hdgo["where_get_title"]])
			{
				$row_hdgo["title"] = $db->safesql($xf_hdgo[$serial_hdgo["where_get_title"]]);
				$upd_first_title = ", first_title_hdgo='{$row_hdgo["title"]}'";
			}
		}
	}
	
	if($row_hdgo["first_title_hdgo"] && $row_hdgo["first_title_hdgo"] != $row_hdgo["title"])
		$row_hdgo["title"] = $row_hdgo["first_title_hdgo"];
	
	$xf_hdgo[$serial_hdgo["seria"]] = $max_all_seria;
	
	if($serial_hdgo["season_serial_site"] != 1 && $serial_hdgo["season"] != "-")
		$xf_hdgo[$serial_hdgo["season"]] = $max_all_season;
	
	if($serial_hdgo["translate"] != "" && $serial_hdgo["translate"] != "-")
	{
		if($serial_hdgo["format_translate"] == 2)
			$xf_hdgo[$serial_hdgo["translate"]] = implode(" / ", $translator_array);
		else
			$xf_hdgo[$serial_hdgo["translate"]] = implode(", ", $translator_array);
	}
	$temp_season_array = array();
	if($serial_hdgo["season_in"] != "-" && $serial_hdgo["season_in"])
	{
		if($serial_hdgo["season_serial_site"] == 1)
			$xf_hdgo[$serial_hdgo["season_in"]] = $season . " сезон";
		else
		{
			if($max_all_season > 1)
			{
				if($serial_hdgo["format_season"] == 2)
					$xf_hdgo[$serial_hdgo["season_in"]] =  "1-" . $max_all_season . " сезон";
				elseif($serial_hdgo["format_season"] == 3)
				{
					for($zet = 1; $zet < $max_all_season; $zet++)
						$temp_season_array[] = $zet;
					$xf_hdgo[$serial_hdgo["season_in"]] = implode(",",$temp_season_array) . "," . $max_all_season . " сезон";
				}
				else
					$xf_hdgo[$serial_hdgo["season_in"]] = $max_all_season . " сезон";
			}
			else
				$xf_hdgo[$serial_hdgo["season_in"]] = $max_all_season . " сезон";
		}
	}
	
	$temp_season_array = false;
	$temp_season_array = array();
	
	if($serial_hdgo["seria_in"] != "-" && $serial_hdgo["seria_in"])
	{
		if($max_all_seria > 1)
		{
			if($serial_hdgo["format_seria"] == 2)
				$xf_hdgo[$serial_hdgo["seria_in"]] =  "1-" . $max_all_seria . " серия";
			elseif($serial_hdgo["format_seria"] == 3)
			{
				for($zet = 1; $zet < $max_all_seria; $zet++)
					$temp_season_array[] = $zet;
				$xf_hdgo[$serial_hdgo["seria_in"]] = implode(",",$temp_season_array) . "," . $max_all_seria . " серия";
			}
			else
				$xf_hdgo[$serial_hdgo["seria_in"]] = $max_all_seria . " серия";
		}
		else
			$xf_hdgo[$serial_hdgo["seria_in"]] = $max_all_seria . " серия";
	}
	
	$temp_season_array = false;
	$temp_season_array = array();
	
	$xfields_array_update_for_news = array();
	
	foreach($xf_hdgo as $key => $value)
		$xfields_array_update_for_news[] = $key . "|" . str_replace('|', '&#124;', $value);
		
	$xfields_array_update_for_news = implode('||', $xfields_array_update_for_news);
	$xfields_array_update_for_news = $db->safesql($xfields_array_update_for_news);
	
	if(!function_exists('update_meta'))
	{
		function update_meta($name, $serial_hdgo, $max_all_season, $max_all_seria, $end, $db, $xf_hdgo, $row_hdgo, $season)
		{
			if($serial_hdgo["season_serial_site"] == 1)
				$serial_hdgo[$name] = str_ireplace("{season}", $season, $serial_hdgo[$name]);
			else
				$serial_hdgo[$name] = str_ireplace("{season}", $max_all_season, $serial_hdgo[$name]);
			
			$serial_hdgo[$name] = str_ireplace("{seria}", $max_all_seria, $serial_hdgo[$name]);
			
			if(substr_count($serial_hdgo[$name],"{season_format}"))
			{
				if($serial_hdgo["season_serial_site"] == 1)
					$serial_hdgo[$name] = str_ireplace("{season_format}", $season . " сезон", $serial_hdgo[$name]);
				else
				{
					$temp_season_array = array();
					if($max_all_season > 1)
					{
						if($serial_hdgo["format_season_" . $end] == 2)
							$serial_hdgo[$name] = str_ireplace("{season_format}", "1-" . $max_all_season . " сезон", $serial_hdgo[$name]);
						elseif($serial_hdgo["format_season_" . $end] == 3)
						{
							for($zet = 1; $zet < $max_all_season; $zet++)
								$temp_season_array[] = $zet;
							$serial_hdgo[$name] = str_ireplace("{season_format}", implode(",",$temp_season_array) . "," . $max_all_season . " сезон", $serial_hdgo[$name]);
						}
						else
							$serial_hdgo[$name] = str_ireplace("{season_format}", $max_all_season . " сезон", $serial_hdgo[$name]);
					}
					else
						$serial_hdgo[$name] = str_ireplace("{season_format}", $max_all_season . " сезон", $serial_hdgo[$name]);
				}
			}
			
			if(substr_count($serial_hdgo[$name],"{seria_format}"))
			{
				if($serial_hdgo["add_seria_" . $end] >= 1)
				{
					if($serial_hdgo["add_seria_" . $end] == 1)
						$add_seria_d = ", " . (intval($max_all_seria) + 1);
					elseif($serial_hdgo["add_seria_" . $end] == 2)
						$add_seria_d = ", " . (intval($max_all_seria + 1)) . ", " . (intval($max_all_seria) + 2);
					elseif($serial_hdgo["add_seria_" . $end] == 3)
						$add_seria_d = ", " . (intval($max_all_seria) + 1) . ", " . (intval($max_all_seria) + 2) . ", " . (intval($max_all_seria) + 3);
				}
				
				if($max_all_seria > 1)
				{
					$temp_season_array = array();
					if($serial_hdgo["format_seria_" . $end] == 2)
						$serial_hdgo[$name] = str_ireplace("{seria_format}", "1-" . $max_all_seria . $add_seria_d . " серия", $serial_hdgo[$name]);
					elseif($serial_hdgo["format_seria_" . $end] == 3)
					{
						for($zet = 1; $zet < $max_all_seria; $zet++)
							$temp_season_array[] = $zet;
						
						$serial_hdgo[$name] = str_ireplace("{seria_format}", implode(",",$temp_season_array) . "," . $max_all_seria . $add_seria_d . " серия", $serial_hdgo[$name]);
					}
					else
						$serial_hdgo[$name] = str_ireplace("{seria_format}", $max_all_seria . $add_seria_d . " серия", $serial_hdgo[$name]);
				}
				else
					$serial_hdgo[$name] = str_ireplace("{seria_format}", $max_all_seria . $add_seria_d . " серия", $serial_hdgo[$name]);
			}

			$serial_hdgo[$name] = str_ireplace("{title}", $row_hdgo["title"], $serial_hdgo[$name]);
			
			foreach($xf_hdgo AS $name_xf => $data_xf)
			{
				if(!isset($xf_hdgo[$name_xf]))
				{
					$serial_hdgo[$name] = preg_replace("'\\[xf_{$name_xf}\\](.*?)\\[/xf_{$name_xf}\\]'is", "", $serial_hdgo[$name]);
					$serial_hdgo[$name] = str_replace("{xf_{$name_xf}}", '', $serial_hdgo[$name]);
					$serial_hdgo[$name] = preg_replace("'\\[xf_not_{$name_xf}\\](.*?)\\[/xf_not_{$name_xf}\\]'si", "\\1", $serial_hdgo[$name]);
				}
				else
				{
					$serial_hdgo[$name] = preg_replace("'\\[xf_not_{$name_xf}\\](.*?)\\[/xf_not_{$name_xf}\\]'is", "", $serial_hdgo[$name]);
					$serial_hdgo[$name] = str_replace("{xf_{$name_xf}}", $data_xf,	$serial_hdgo[$name]);
					$serial_hdgo[$name] = preg_replace("'\\[xf_{$name_xf}\\](.*?)\\[/xf_{$name_xf}\\]'si", "\\1", $serial_hdgo[$name]);
				}
			}
			
			$serial_hdgo[$name] = $db->safesql($serial_hdgo[$name]);
			
			if($name == "meta_title" || $name == "meta_title_end")
				return ", metatitle='{$serial_hdgo[$name]}'";
			else
				return ", title='{$serial_hdgo[$name]}'";
		}
	}
	
	if(trim($serial_hdgo["end_serial_seria"]) != "-" && isset($xf_hdgo[trim($serial_hdgo["end_serial_seria"])]) && $xf_hdgo[trim($serial_hdgo["end_serial_seria"])] == $max_all_seria || trim($serial_hdgo["end_serial"]) != "-" && isset($serial_hdgo["end_serial"]) && isset($xf_hdgo[trim($serial_hdgo["end_serial"])]) && $xf_hdgo[trim($serial_hdgo["end_serial"])] == trim($serial_hdgo["end_serial_text"]))
	{
		if($serial_hdgo["change_meta"])
		{
			if($serial_hdgo["meta_title_end"])
			{
				$meta_title = update_meta("meta_title_end", $serial_hdgo, $max_all_season, $max_all_seria, "meta", $db, $xf_hdgo, $row_hdgo, $season);
				$meta_title = preg_replace("'\\[xf_(.*?)\\](.*?)\\[/xf_(.*?)\\]'is", "", $meta_title);
				$meta_title = preg_replace("'\\[xf_not_(.*?)\\](.*?)\\[/xf_not_(.*?)\\]'si", "\\1", $meta_title);
			}
		}
		if($serial_hdgo["change_title"])
		{
			if($serial_hdgo["news_title_end"])
			{
				$title_news = update_meta("news_title_end", $serial_hdgo, $max_all_season, $max_all_seria, "title", $db, $xf_hdgo, $row_hdgo, $season);
				$title_news = preg_replace("'\\[xf_(.*?)\\](.*?)\\[/xf_(.*?)\\]'is", "", $title_news);
				$title_news = preg_replace("'\\[xf_not_(.*?)\\](.*?)\\[/xf_not_(.*?)\\]'si", "\\1", $title_news);
			}
		}
		$end_serial_update = ", end_serial_hdgo=1";
	}
	else
	{
		if($serial_hdgo["change_meta"])
		{
			if($serial_hdgo["meta_title"])
			{
				$meta_title = update_meta("meta_title", $serial_hdgo, $max_all_season, $max_all_seria, "meta", $db, $xf_hdgo, $row_hdgo, $season);
				$meta_title = preg_replace("'\\[xf_(.*?)\\](.*?)\\[/xf_(.*?)\\]'is", "", $meta_title);
				$meta_title = preg_replace("'\\[xf_not_(.*?)\\](.*?)\\[/xf_not_(.*?)\\]'si", "\\1", $meta_title);
			}
		}
		if($serial_hdgo["change_title"])
		{
			if($serial_hdgo["news_title"])
			{
				$title_news = update_meta("news_title", $serial_hdgo, $max_all_season, $max_all_seria, "title", $db, $xf_hdgo, $row_hdgo, $season);
				$title_news = preg_replace("'\\[xf_(.*?)\\](.*?)\\[/xf_(.*?)\\]'is", "", $title_news);
				$title_news = preg_replace("'\\[xf_not_(.*?)\\](.*?)\\[/xf_not_(.*?)\\]'si", "\\1", $title_news);
			}
		}
	}
	
	if($serial_hdgo["update_news"] && ($check_voice_update || $seria < $max_all_seria && $serial_hdgo["season_serial_site"] == 1 || ($seria < $max_all_seria || $season < $max_all_season) && $serial_hdgo["season_serial_site"] != 1))
		$upd_date = "date='{$thistime}',";	
	
	$db->query("UPDATE " . PREFIX . "_post SET {$upd_date} xfields='{$xfields_array_update_for_news}' {$meta_title} {$title_news} {$upd_first_title} {$end_serial_update} WHERE id=$news_id");
	
	if($serial_hdgo["socialposting"])
	{
		define ( 'ROOT_DIR', "../../../" );
		define ( 'ENGINE_DIR', ROOT_DIR . '/engine' );
		$category_list = explode( ",", $row_hdgo['category'] );
		array_unshift( $category_list, "0" );
		$sqlExport = array();
		$sqlPosting = $db->query( "SELECT id FROM " . PREFIX . "_socialposting_conf WHERE category regexp '[[:<:]](" . implode( '|', $category_list ) . ")[[:>:]]' AND activ='1'" );
		while( $rowP = $db->get_row( $sqlPosting ) ) {
			$sqlExport[] = "('" . $rowP['id'] . "', '" . $news_id . "')";
		}
		if( count( $sqlExport ) > 0 ) {
			$db->query("DELETE FROM " . PREFIX . "_socialposting_list WHERE `post_id`='" . $news_id . "'" );
			$db->query("INSERT IGNORE INTO " . PREFIX . "_socialposting_list (`conf_id`, `post_id`) VALUES " . implode( ',', $sqlExport ) );
		}
		require_once ENGINE_DIR . '/data/config.posting.php';
		if( $config_posting['cron_posting'] == "off" ) {
			$config_posting['cron_posting'] = "on";
			include ENGINE_DIR . "/modules/socialposting/cron.php";
		}
	}
	
	clear_cache("full");
}
?>