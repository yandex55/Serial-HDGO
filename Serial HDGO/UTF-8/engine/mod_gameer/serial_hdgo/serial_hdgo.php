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
defined("DATALIFEENGINE") || die("Hack");

if(intval($news_id) <= 0) return;

include ENGINE_DIR . "/data/serial_hdgo.php";

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
			{
				$serial_hdgo[$name] = str_ireplace("{season_format}", $season . " сезон", $serial_hdgo[$name]);
			}
			else
			{
				if($max_all_season > 1)
				{
					if($serial_hdgo["format_season_" . $end] == 2)
					{
						$serial_hdgo[$name] = str_ireplace("{season_format}", "1-" . $max_all_season . " сезон", $serial_hdgo[$name]);
					}
					elseif($serial_hdgo["format_season_" . $end] == 3)
					{
						for($zet = 1; $zet < $max_all_season; $zet++)
							$temp_season_array[] = $zet;
						$serial_hdgo[$name] = str_ireplace("{season_format}", implode(",",$temp_season_array) . "," . $max_all_season . " сезон", $serial_hdgo[$name]);
						unset($temp_season_array);
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
				{
					$add_seria_d = ", " . (intval($max_all_seria) + 1);
				}
				elseif($serial_hdgo["add_seria_" . $end] == 2)
				{
					$add_seria_d = ", " . ($max_all_seria + 1) . ", " . ($max_all_seria + 2);
				}
				elseif($serial_hdgo["add_seria_" . $end] == 3)
				{
					$add_seria_d = ", " . ($max_all_seria + 1) . ", " . ($max_all_seria + 2) . ", " . ($max_all_seria + 3);
				}
			}
			if($max_all_seria > 1)
			{
				if($serial_hdgo["format_seria_" . $end] == 2)
				{
					$serial_hdgo[$name] = str_ireplace("{seria_format}", "1-" . $max_all_seria . $add_seria_d . " серия", $serial_hdgo[$name]);
				}
				elseif($serial_hdgo["format_seria_" . $end] == 3)
				{
					for($zet = 1; $zet < $max_all_seria; $zet++)
						$temp_season_array[] = $zet;
					$serial_hdgo[$name] = str_ireplace("{seria_format}", implode(",",$temp_season_array) . "," . $max_all_seria . $add_seria_d . " серия", $serial_hdgo[$name]);
					unset($temp_season_array);
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
			if(isset($xf_hdgo[$name_xf]))
			{
				$serial_hdgo[$name] = preg_replace("'\\[xf_not_{$name_xf}\\](.*?)\\[/xf_not_{$name_xf}\\]'uis", "", $serial_hdgo[$name]);
				$serial_hdgo[$name] = str_replace("{xf_{$name_xf}}", $data_xf,	$serial_hdgo[$name]);
				$serial_hdgo[$name] = preg_replace("'\\[xf_{$name_xf}\\](.*?)\\[/xf_{$name_xf}\\]'usi", "\\1", $serial_hdgo[$name]);
			}
		}
		$serial_hdgo[$name] = $db->safesql($serial_hdgo[$name]);
		return ", metatitle='{$serial_hdgo[$name]}'";
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
$row_hdgo = $db->super_query("SELECT title, date, category, xfields, end_serial FROM " . PREFIX . "_post WHERE id=$news_id");

if(!$row_hdgo["xfields"]) return;
$cat_ex = explode(",", $serial_hdgo["cat_ex"]);
$cat_serial = explode(",", $serial_hdgo["cat_serial"]);
$cat_all = explode(",", $serial_hdgo["cat_all"]);
$cat_noall = explode(",", $serial_hdgo["cat_noall"]);
$cat_anime = explode(",", $serial_hdgo["cat_anime"]);
$cat_arr_ex = explode(",", $row_hdgo["category"]);

if($serial_hdgo["cat_ex"])
{
	$excat_bool = false;
	foreach($cat_arr_ex as $val_cat)
	{
		if(in_array($val_cat, $cat_ex))
		{
			$excat_bool = true;
			break;
		}
	}
	if($excat_bool == true) return;
}

if($serial_hdgo["season_serial_site"] == 1 && $serial_hdgo["cat_all"])
{
	$allcat_bool = false;

	foreach($cat_arr_ex as $val_cat)
	{
		if(in_array($val_cat, $cat_all))
		{
			$allcat_bool = true;
			break;
		}
	}
	
	if($allcat_bool == true) $serial_hdgo['season_serial_site'] = 0;
}

if($serial_hdgo["season_serial_site"] != 1 && $serial_hdgo["cat_noall"] && !$allcat_bool)
{
	$noallcat_bool = false;

	foreach($cat_arr_ex as $val_cat)
	{
		if(in_array($val_cat, $cat_noall))
		{
			$noallcat_bool = true;
			break;
		}
	}
	if($noallcat_bool == true) $serial_hdgo['season_serial_site'] = 1;
}

if($allcat_bool == false && $noallcat_bool == false)
{
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
}

$xf_hdgo = xfieldsdataload( $row_hdgo['xfields'] );
$id_kp = false;

if($serial_hdgo["world_art"] != "-" && $xf_hdgo[$serial_hdgo["world_art"]])
{
	$api_send = "world_art_id";
	$id_kp = $xf_hdgo[$serial_hdgo["world_art"]];
}
elseif($serial_hdgo["id_kp"] != "-" && $xf_hdgo[$serial_hdgo["id_kp"]])
{
	$api_send = "kinopoisk_id";
	$id_kp = $xf_hdgo[$serial_hdgo["id_kp"]];
}
else return;

$season = isset($xf_hdgo[$serial_hdgo["season"]]) ? intval(trim(str_ireplace("сезон", "", $xf_hdgo[$serial_hdgo["season"]]))) : 1;
$seria = isset($xf_hdgo[$serial_hdgo["seria"]]) ? intval(trim(str_ireplace("серия", "", $xf_hdgo[$serial_hdgo["seria"]]))) : 0;

if(!$id_kp) return;
$check_voice_update = false;

$data = requestToHDGO("http://hdgo.cc/api/video.json?{$api_send}={$id_kp}&token={$serial_hdgo['token']}");
$data_new = json_decode( $data, true );

$thistime = date("Y-m-d H:i:s", time());

$max_seria_array = array();
$min_seria_array = array();
$season_trans = array();
$season_array = array();
$translator_array = array();
$trans_season_array = array();
$expl_trans_ex = explode(",", $serial_hdgo["ex_voice"]);
$max_season_by_voice = array();
$max_seria_by_voice = array();
$min_seria_by_voice = array();
$iframe_url = array();
$check_iframe_url = false;
$found_season_true = false;

$cookie = isset($_COOKIE["trans_" . $news_id]) ? $_COOKIE["trans_" . $news_id] : false;
$season_cookie = isset($_COOKIE["season_" . $news_id]) ? $_COOKIE["season_" . $news_id] : false;
$seria_cookie = isset($_COOKIE["seria_" . $news_id]) ? $_COOKIE["seria_" . $news_id] : false;

foreach($data_new as $key => $val)
{
	if($val['id_hdgo'] == NULL || !$val['id_hdgo']) continue;
	
	$voice_data = false;
	$voice_data = requestToHdgo("http://hdgo.cc/api/serial_episodes.json?id={$val['id_hdgo']}&token={$serial_hdgo['token']}");
	$voice_data = json_decode($voice_data, true);

	foreach($voice_data["season_episodes_count"] as $pepsi => $liter)
	{
		if($serial_hdgo["season_serial_site"] == 1 && $liter["season_number"] != $season) continue;
		$max_season_by_voice[$val['id_hdgo']][] = $liter["season_number"];
		$max_seria_by_voice[$val['id_hdgo']][$liter["season_number"]] = $liter["episodes_count"];
	}
		
	$max_season_by_voice_max = max($max_season_by_voice[$val['id_hdgo']]);
	$season_array[] = $max_season_by_voice_max;
	
	$max_seria_by_voice_max = $max_seria_by_voice[$val['id_hdgo']][$max_season_by_voice_max];
	$seria_array[$max_season_by_voice_max][$val['id_hdgo']] = $max_seria_by_voice_max;

	if($serial_hdgo["season_serial_site"] != 1) $season = $max_season_by_voice_max;

	foreach($voice_data["season_episodes_count"] as $index => $value)
	{
		if($serial_hdgo["season_serial_site"] != 1)
		{
			if($check_iframe_url == false && $cookie == false)
			{
				if($season_cookie != false && $serial_hdgo["season_serial_site"] != 1 && $season_cookie == $value["season_number"])
				{
					$voice_desgn .= "<li class=\"b-translator__item active\" data-news_id=\"{$news_id}\" data-translator_id=\"{$val['id_hdgo']}\">" . $voice_data['serial']['translator'] . "</li>";
					$trans_id_a = $val['id_hdgo'];
					$iframe_url[$trans_id_a] = $voice_data["serial"]["iframe_url"];
					$check_iframe_url = true;
				}
				elseif($season_cookie == false && $serial_hdgo["season_serial_site"] != 1)
				{
					$voice_desgn .= "<li class=\"b-translator__item active_{$val['id_hdgo']}\" data-news_id=\"{$news_id}\" data-translator_id=\"{$val['id_hdgo']}\">" . $voice_data['serial']['translator'] . "</li>";
					$trans_id_a = $val['id_hdgo'];
					$iframe_url[$trans_id_a] = $voice_data["serial"]["iframe_url"];
					$check_iframe_url = true;
				}
			}
			elseif($check_iframe_url == false && $cookie != false && $cookie == $val['id_hdgo'])
			{
				if($season_cookie != false && $serial_hdgo["season_serial_site"] != 1 && $season_cookie == $value["season_number"])
				{
					$voice_desgn .= "<li class=\"b-translator__item active_{$val['id_hdgo']}\" data-news_id=\"{$news_id}\" data-translator_id=\"{$val['id_hdgo']}\">" . $voice_data['serial']['translator'] . "</li>";
					$trans_id_a = $val['id_hdgo'];
					$iframe_url[$trans_id_a] = $voice_data["serial"]["iframe_url"];
					$check_iframe_url = true;
				}
				elseif($season_cookie == false && $serial_hdgo["season_serial_site"] != 1)
				{
					$voice_desgn .= "<li class=\"b-translator__item active_{$val['id_hdgo']}\" data-news_id=\"{$news_id}\" data-translator_id=\"{$val['id_hdgo']}\">" . $voice_data['serial']['translator'] . "</li>";
					$trans_id_a = $val['id_hdgo'];
					$iframe_url[$trans_id_a] = $voice_data["serial"]["iframe_url"];
					$check_iframe_url = true;
				}
			}
			else
			{
				if($trans_bk != $voice_data['serial']['translator'])
				{
					$voice_desgn .= "<li class=\"b-translator__item active_{$val['id_hdgo']}\" data-news_id=\"{$news_id}\" data-translator_id=\"{$val['id_hdgo']}\">" . $voice_data['serial']['translator'] . "</li>";
					$iframe_url[$val['id_hdgo']] = $voice_data["serial"]["iframe_url"];
				}
			}
			$trans_bk = $voice_data['serial']['translator'];
		}
		
		$trans_season_array[$value["season_number"]][$val['id_hdgo']] = $value["episodes_count"];
		$trans_seasonm_array[$value["season_number"]][$val['id_hdgo']] = 1;
		$season_trans[$val['id_hdgo']][] = $value["season_number"];
		
		if($value["season_number"] == $season)
		{
			$found_season_true = true;
			$voice_data['serial']['translator'] = $db->safesql($voice_data['serial']['translator']);
			$translator_array[] = $voice_data['serial']['translator'];
			
			$max_seria = $value["episodes_count"];
			$min_seria = 1;
			
			if($serial_hdgo["season_serial_site"] != 1)
				$max_seria_array[$val['id_hdgo']][$season] = $max_seria;
			else
				$max_seria_array[] = $max_seria;
			
			if($serial_hdgo["season_serial_site"] == 1)
			{
				if($check_iframe_url == false && $cookie == false)
				{
					$voice_desgn .= "<li class=\"b-translator__item active_{$val['id_hdgo']}\" data-news_id=\"{$news_id}\" data-translator_id=\"{$val['id_hdgo']}\">" . $voice_data['serial']['translator'] . "</li>";
					$trans_id_a = $val['id_hdgo'];
					$iframe_url[$trans_id_a] = $voice_data["serial"]["iframe_url"];
					$max_tseria_array[$trans_id_a] = $max_seria;
					$min_tseria_array[$trans_id_a] = $min_seria;
					$check_iframe_url = true;
				}
				elseif($check_iframe_url == false && $cookie != false && $cookie == $val['id_hdgo'])
				{
					$voice_desgn .= "<li class=\"b-translator__item active_{$val['id_hdgo']}\" data-news_id=\"{$news_id}\" data-translator_id=\"{$val['id_hdgo']}\">" . $voice_data['serial']['translator'] . "</li>";
					$trans_id_a = $val['id_hdgo'];
					$iframe_url[$trans_id_a] = $voice_data["serial"]["iframe_url"];
					$max_tseria_array[$trans_id_a] = $max_seria;
					$min_tseria_array[$trans_id_a] = $min_seria;
					$check_iframe_url = true;
				}
				else
				{
					$voice_desgn .= "<li class=\"b-translator__item active_{$val['id_hdgo']}\" data-news_id=\"{$news_id}\" data-translator_id=\"{$val['id_hdgo']}\">" . $voice_data['serial']['translator'] . "</li>";
					$max_tseria_array[$val['id_hdgo']] = $max_seria;
					$min_tseria_array[$val['id_hdgo']] = $min_seria;
					$iframe_url[$val['id_hdgo']] = $voice_data["serial"]["iframe_url"];
				}
			}
			
			if($serial_hdgo["season_serial_site"] == 1)
				$seria_data = $db->super_query("SELECT seria, season FROM " . PREFIX . "_serial_hdgo WHERE news_id=$news_id AND voice='{$voice_data['serial']['translator']}' AND season=$season");
			else
				$seria_data = $db->super_query("SELECT seria, season FROM " . PREFIX . "_serial_hdgo WHERE news_id=$news_id AND voice='{$voice_data['serial']['translator']}'");
			
			$date_moon = $thistime;
			
			if($seria_data != NULL && intval($seria_data["seria"]) >= 0 && $max_seria > $seria_data["seria"])
			{
				$thistime = date("Y-m-d H:i:s", time());

				if($serial_hdgo["season_serial_site"] == 1 && $max_seria > $seria_data["seria"])
				{
					$check_voice_update = true;
					$db->query("UPDATE " . PREFIX . "_serial_hdgo SET seria=$max_seria, date_update='{$date_moon}' WHERE news_id=$news_id AND voice='{$voice_data['serial']['translator']}' AND season=$season");
				}
				else
				{
					if($serial_hdgo["season_serial_site"] != 1 && ($seria_data["season"] < $season || $max_seria > $seria_data["seria"]))
					{
						$check_voice_update = true;
						$db->query("UPDATE " . PREFIX . "_serial_hdgo SET seria=$max_seria, season=$season, date_update='{$date_moon}' WHERE news_id=$news_id AND voice='{$voice_data['serial']['translator']}'");
					}
				}
			}
			else if(!$seria_data["season"] || $seria_data["season"] == NULL)
			{
				$first_update = true;
				
				$db->query("INSERT INTO " . PREFIX . "_serial_hdgo (news_id, voice, season, seria, date_update) VALUES ($news_id, '{$voice_data['serial']['translator']}', $season, $max_seria, '{$date_moon}');");
			}
		}
	}
}

if($serial_hdgo["season_serial_site"] == 1)
	$max_all_season = $season;
else
	$max_all_season = max($season_array);

$key_max = array_keys($seria_array[$max_all_season], max($seria_array[$max_all_season]));
$key_max = $key_max[0];
$max_tr_id = $key_max;

if($ajax_season || $cookie != false)
	$key_max = $trans_id_a;

$iframe_url = $iframe_url[$key_max];

if($serial_hdgo["season_serial_site"] != 1)
{
	$min_trans_season = min($season_trans[$key_max]);
	$max_trans_season = max($season_trans[$key_max]);
	$max_trans_seasons = max($season_trans[$max_tr_id]);
	$max_all_seria = $max_seria_array[$max_tr_id][$max_trans_seasons];
	$max_check = ($season_cookie != false) ? $season_cookie : $max_trans_season;
	$max_trans_seria = $trans_season_array[$max_check][$key_max];
	$min_trans_seria = $trans_seasonm_array[$max_check][$key_max];
}
else
{
	$max_all_seria = max($max_seria_array);
	$max_trans_seria = $trans_season_array[$season][$key_max];
	$min_trans_seria = $trans_seasonm_array[$season][$key_max];
}

if($serial_hdgo["min_player_seria"] == 1)
	$player_seria_video = $min_trans_seria;
else
	$player_seria_video = $max_trans_seria;

if($min_trans_seria == 0)
	$max_trans_seria = $max_trans_seria - 1;
if($serial_hdgo["player"] && $found_season_true)
{
	$tpl->load_template('serial_hdgo/player.tpl');
	if(!$serial_hdgo["design"])
	{
		for($iz = $min_trans_seria; $iz <= $max_trans_seria; $iz++)
		{
			if($seria_cookie != false && $seria_cookie == $iz)
				$seria_trans .= "<div class=\"b-simple_episode__item active\" data-news_id=\"{$news_id}\" data-seria_id=\"{$iz}\">Серия {$iz}</div>";
			elseif($seria_cookie == false && $iz == $max_trans_seria && $serial_hdgo["min_player_seria"] != 1)
				$seria_trans .= "<div class=\"b-simple_episode__item active\" data-news_id=\"{$news_id}\" data-seria_id=\"{$iz}\">Серия {$iz}</div>";
			elseif($seria_cookie == false && $iz == $min_trans_seria && $serial_hdgo["min_player_seria"] == 1)
				$seria_trans .= "<div class=\"b-simple_episode__item active\" data-news_id=\"{$news_id}\" data-seria_id=\"{$iz}\">Серия {$iz}</div>";
			else
				$seria_trans .= "<div class=\"b-simple_episode__item\" data-news_id=\"{$news_id}\" data-seria_id=\"{$iz}\">Серия {$iz}</div>";
		}
		if($serial_hdgo["season_serial_site"] != 1)
		{
			for($iz = $min_trans_season; $iz <= $max_trans_season; $iz++)
			{
				if($season_cookie != false && $season_cookie == $iz)
					$season_transes .= "<li class=\"b-simple_season__item active\" data-news_id=\"{$news_id}\" data-voice_id=\"{$trans_id_a}\" data-season_id=\"{$iz}\">Сезон {$iz}</li>";
				elseif($season_cookie == false && $iz == $max_trans_season)
					$season_transes .= "<li class=\"b-simple_season__item active\" data-news_id=\"{$news_id}\" data-voice_id=\"{$trans_id_a}\" data-season_id=\"{$iz}\">Сезон {$iz}</li>";
				else
					$season_transes .= "<li class=\"b-simple_season__item\" data-news_id=\"{$news_id}\" data-voice_id=\"{$trans_id_a}\" data-season_id=\"{$iz}\">Сезон {$iz}</li>";
			}
			$tpl->set_block( "'\\[season\\](.*?)\\[/season\\]'si", "\\1" );
		}
		else
			$tpl->set_block( "'\\[season\\](.*?)\\[/season\\]'si", "" );
		$tpl->set('{seria}', $seria_trans);
		$tpl->set('{season}', $season_transes);
		$tpl->set('{voice}', str_ireplace("active_{$key_max}", "active", $voice_desgn));
		if($serial_hdgo["season_serial_site"] == 1)
			$tpl->set('{url}', $iframe_url."?nocontrol=2&season={$season}&episode={$player_seria_video}");
		else
			$tpl->set('{url}', $iframe_url."?nocontrol=2&season={$max_check}&episode={$player_seria_video}");
		$tpl->set_block( "'\\[design\\](.*?)\\[/design\\]'si", "\\1" );
	}
	else
	{
		$tpl->set_block( "'\\[design\\](.*?)\\[/design\\]'si", "" );
		$tpl->set('{url}', $iframe_url);
	}
	$tpl->compile('serial_block_player');
	$tpl->clear();
}

if($check_voice_update || $first_update)
{	
	$check_out_update = $xf_hdgo[$serial_hdgo["seria"]];
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
	
	$end_serial_update = false;
	if(trim($serial_hdgo["end_serial_seria"]) != "-" && isset($xf_hdgo[trim($serial_hdgo["end_serial_seria"])]) && $xf_hdgo[trim($serial_hdgo["end_serial_seria"])] == $max_all_seria || $serial_hdgo["end_serial"] != "-" && strtolower(trim($xf_hdgo[$serial_hdgo["end_serial"]])) == strtolower(trim($serial_hdgo["end_serial_text"])))
	{
		if($serial_hdgo["change_meta"])
		{
			if($serial_hdgo["meta_title_end"])
			{
				$meta_title = update_meta("meta_title_end", $serial_hdgo, $max_all_season, $max_all_seria, "meta", $db, $xf_hdgo, $row_hdgo, $season);
				$meta_title = preg_replace("'\\[xf_(.*?)\\](.*?)\\[/xf_(.*?)\\]'uis", "", $meta_title);
				$meta_title = preg_replace("'\\[xf_not_(.*?)\\](.*?)\\[/xf_not_(.*?)\\]'usi", "\\1", $meta_title);
			}
		}
		$end_serial_update = ", end_serial=1";
	}
	else
	{
		if($serial_hdgo["change_meta"])
		{
			if($serial_hdgo["meta_title"])
			{
				$meta_title = update_meta("meta_title", $serial_hdgo, $max_all_season, $max_all_seria, "meta", $db, $xf_hdgo, $row_hdgo, $season);
				$meta_title = preg_replace("'\\[xf_(.*?)\\](.*?)\\[/xf_(.*?)\\]'uis", "", $meta_title);
				$meta_title = preg_replace("'\\[xf_not_(.*?)\\](.*?)\\[/xf_not_(.*?)\\]'usi", "\\1", $meta_title);
			}
		}
	}
	
	$upd_date = '';
	
	if($serial_hdgo["update_news"] && $check_voice_update && $row_hdgo["end_serial_hdgo"] != 1)
		$upd_date = "date='{$thistime}',";
	
	$db->query("UPDATE " . PREFIX . "_post SET {$upd_date} xfields='{$xfields_array_update_for_news}' {$meta_title} {$end_serial_update} WHERE id=$news_id");
		
	if($check_voice_update && $serial_hdgo["socialposting"] && $check_out_update < $max_all_seria)
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
		if(file_exists(ENGINE_DIR . '/data/config.posting.php'))
		{
			require_once ENGINE_DIR . '/data/config.posting.php';
			if( $config_posting['cron_posting'] == "off" ) {
				$config_posting['cron_posting'] = "on";
				include ENGINE_DIR . "/modules/socialposting/cron.php";
			}
		}
	}
	
	if($check_voice_update && $serial_hdgo["podpiska"] && $row_hdgo["end_serial_hdgo"] != 1)
	{
		if (file_exists(ENGINE_DIR . '/modules/mailer.php'))
		{
			$item_db[0] = $news_id;
			include ENGINE_DIR . '/modules/mailer.php';
		}
	}
	
	clear_cache("full_" . $news_id);
}

if($serial_hdgo["player"] && $found_season_true == true)
	echo $tpl->result["serial_block_player"];
?>