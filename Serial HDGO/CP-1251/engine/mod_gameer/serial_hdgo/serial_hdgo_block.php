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

include ENGINE_DIR . "/data/serial_hdgo.php";
$thisdate = date("Y-m-d H:i:s", time());
$serial_hdgo["interval_date"] = isset($serial_hdgo["interval_date"]) && intval($serial_hdgo["interval_date"]) > 0 ? intval($serial_hdgo["interval_date"]) : 3;
$serial_hdgo["limit_news"] = isset($serial_hdgo["limit_news"]) && intval($serial_hdgo["limit_news"]) > 0 ? intval($serial_hdgo["limit_news"]) : 20;

if($serial_hdgo["block_one"] == 1)
{
	$order_block = " GROUP BY news_id ORDER BY date_update DESC, season DESC, seria DESC";
	$group_block = " GROUP BY news_id ";
}
else
{
	$order_block = " ORDER BY date_update DESC ";
	$group_block = "";
}

if($serial_hdgo["pagination"])
{
	$sql_count = $db->super_query("SELECT COUNT(*) as count FROM ". PREFIX . "_serial_hdgo as s, " . PREFIX . "_post as p WHERE s.news_id=p.id $where_date AND approve=1 {$group_block}");
	$count_all = $sql_count['count'];
	if (!isset($_GET["cstart"]) || ($_GET["cstart"]<1))
		$cstart = 0;
	else
		$cstart = ($_GET["cstart"]-1)*$serial_hdgo["limit_news"];
	
	$sql_serial_hdgo = $db->query("SELECT s.id as sid, p.id as pid, voice, season, seria, date_update, news_id, title, category, date, alt_name, short_story, xfields, end_serial_hdgo FROM " . PREFIX . "_serial_hdgo as s, " . PREFIX . "_post as p WHERE s.news_id=p.id AND approve=1 {$order_block} LIMIT {$cstart},{$serial_hdgo["limit_news"]}");
	$news_count = $cstart + $serial_hdgo["limit_news"];
}
else
{
	$sql_serial_hdgo = $db->query("SELECT s.id as sid, p.id as pid, voice, season, seria, date_update, news_id, title, category, date, alt_name, short_story, xfields, end_serial_hdgo FROM " . PREFIX . "_serial_hdgo as s, " . PREFIX . "_post as p WHERE s.news_id=p.id AND date_update >= DATE_SUB(CURRENT_DATE, INTERVAL {$serial_hdgo["interval_date"]} DAY) AND approve=1 {$order_block} LIMIT 0,{$serial_hdgo["limit_news"]}");
}
$news_id_array = array();

$tpl1 = new dle_template();
$tpl1->dir = TEMPLATE_DIR;
$tpl1->load_template('serial_hdgo/block.tpl');
while ($row = $db->get_row($sql_serial_hdgo))
	$block_news[substr($row['date_update'], 0, 10) ][$row['sid']] = $row;

foreach($block_news as $date => $news)
{
	$dates = strtotime($date);
	$timeformat = "d F";
	if (date('Ymd', $dates) == date('Ymd', $_TIME))
		$tpl1->set('{date}', $lang['time_heute'] . langdate(", $timeformat", $dates));
	elseif (date('Ymd', $dates) == date('Ymd', ($_TIME - 86400)))
		$tpl1->set('{date}', $lang['time_gestern'] . langdate(", $timeformat", $dates));
	else $tpl1->set('{date}', langdate("$timeformat", $dates));
	$news_date = $dates;
	$tpl1->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl1->copy_template );
	
	$tpl12 = new dle_template();
	$tpl12->dir = TEMPLATE_DIR;
	$tpl12->load_template('serial_hdgo/block_content.tpl');
	
	foreach($news as $id => $serial_hdgo_content)
	{
		if($serial_hdgo_content["end_serial_hdgo"] == 1)
		{
			$tpl->set_block( "'\\[start_serial\\](.*?)\\[/start_serial\\]'si", "" );
			$tpl->set_block( "'\\[end_serial\\](.*?)\\[/end_serial\\]'si", "\\1" );
		}
		else
		{
			$tpl->set_block( "'\\[start_serial\\](.*?)\\[/start_serial\\]'si", "\\1" );
			$tpl->set_block( "'\\[end_serial\\](.*?)\\[/end_serial\\]'si", "" );
		}
		$tpl12->set("{voice}", $serial_hdgo_content["voice"]);
		$tpl12->set("{season}", $serial_hdgo_content["season"]);
		$tpl12->set("{seria}", $serial_hdgo_content["seria"]);
		if ($config['allow_alt_url'])
		{
			if ($config['seo_type'] == 1 OR $config['seo_type'] == 2)
			{
				if ($serial_hdgo_content['category'] and $config['seo_type'] == 2)
				{
					$serial_hdgo_content['category'] = intval( $serial_hdgo_content['category'] );
					$full_link = $config['http_home_url'] . get_url( $serial_hdgo_content['category'] ) . "/" . $serial_hdgo_content['pid'] . "-" . $serial_hdgo_content['alt_name'] . ".html";
				}
				else
				{
					$full_link = $config['http_home_url'] . $serial_hdgo_content['pid'] . "-" . $serial_hdgo_content['alt_name'] . ".html";
				}
			}
			else
			{
				$full_link = $config['http_home_url'] . date('Y/m/d/', $serial_hdgo_content['date']) . $serial_hdgo_content['alt_name'] . ".html";
			}
		}
		else
		{
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $serial_hdgo_content['pid'];
		}
		$row['xfields'] = stripslashes( $row['xfields'] );
		$xfields = xfieldsload();
		if(count($xfields))
		{
			$xfieldsdata = xfieldsdataload($serial_hdgo_content['xfields']);
			foreach($xfields as $value)
			{
				$preg_safe_name = preg_quote($value[0], "'");
				if ($value[6] AND !empty($xfieldsdata[$value[0]]))
				{
					$temp_array = explode(",", $xfieldsdata[$value[0]]);
					$value3 = array();
					foreach($temp_array as $value2)
					{
						$value2 = trim($value2);
						$value2 = str_replace("&#039;", "'", $value2);
						if ($config['allow_alt_url'])
							$value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" . urlencode($value2) . "/\">" . $value2 . "</a>";
						else
							$value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xf=" . urlencode($value2) . "\">" . $value2 . "</a>";
					}

					$xfieldsdata[$value[0]] = implode(", ", $value3);
					unset($temp_array);
					unset($value2);
					unset($value3);
				}

				if (empty($xfieldsdata[$value[0]]))
				{
					$tpl12->copy_template = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl12->copy_template);
					$tpl12->copy_template = str_replace("[xfnotgiven_{$value[0]}]", "", $tpl12->copy_template);
					$tpl12->copy_template = str_replace("[/xfnotgiven_{$value[0]}]", "", $tpl12->copy_template);
				}
				else
				{
					$tpl12->copy_template = preg_replace("'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl12->copy_template);
					$tpl12->copy_template = str_replace("[xfgiven_{$value[0]}]", "", $tpl12->copy_template);
					$tpl12->copy_template = str_replace("[/xfgiven_{$value[0]}]", "", $tpl12->copy_template);
				}

				$xfieldsdata[$value[0]] = stripslashes($xfieldsdata[$value[0]]);
				
				$tpl12->copy_template = str_replace("[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]], $tpl12->copy_template);
				if (preg_match("#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl12->copy_template, $matches))
				{
					$count = intval($matches[1]);
					$xfieldsdata[$value[0]] = str_replace("</p><p>", " ", $xfieldsdata[$value[0]]);
					$xfieldsdata[$value[0]] = strip_tags($xfieldsdata[$value[0]], "<br />");
					$xfieldsdata[$value[0]] = trim(str_replace("<br />", " ", str_replace("<br />", " ", str_replace("\n", " ", str_replace("\r", "", $xfieldsdata[$value[0]])))));
					if ($count AND dle_strlen($xfieldsdata[$value[0]], $config['charset']) > $count)
					{
						$xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $count, $config['charset']);
						if (($temp_dmax = dle_strrpos($xfieldsdata[$value[0]], ' ', $config['charset'])))
							$xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $temp_dmax, $config['charset']);
					}
					$tpl12->set($matches[0], $xfieldsdata[$value[0]]);
				}
			}
		}
		$serial_hdgo_content['date'] = strtotime($serial_hdgo_content['date']);
		if (date('Ymd', $serial_hdgo_content['date']) == date('Ymd', $_TIME))
			$tpl12->set('{date}', $lang['time_heute'] . langdate(", $timeformat", $serial_hdgo_content['date']));
		elseif (date('Ymd', $serial_hdgo_content['date']) == date('Ymd', ($_TIME - 86400)))
			$tpl12->set('{date}', $lang['time_gestern'] . langdate(", $timeformat", $serial_hdgo_content['date']));
		else
			$tpl12->set('{date}', langdate($timeformat, $serial_hdgo_content['date']));
		$news_date = $serial_hdgo_content['date'];
		$tpl12->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl12->copy_template );
		if (!$serial_hdgo_content['category'])
		{
			$my_cat = "---";
			$my_cat_link = "---";
		}
		else
		{
			$my_cat = array();
			$my_cat_link = array();
			$cat_list = explode(',', $serial_hdgo_content['category']);
			if (count($cat_list) == 1)
			{
				$my_cat[] = $cat_info[$cat_list[0]]['name'];
				$my_cat_link = get_categories($cat_list[0]);
			}
			else
			{
				foreach($cat_list as $element)
				{
					if ($element)
					{
						$my_cat[] = $cat_info[$element]['name'];
						if ($config['allow_alt_url'])
							$my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url($element) . "/\">{$cat_info[$element]['name']}</a>";
						else
							$my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
					}
				}

				$my_cat_link = implode(', ', $my_cat_link);
			}

			$my_cat = implode(', ', $my_cat);
		}
		/*Обработка категорий конец*/
		$tpl12->set('[full-link]', "<a href=\"" . $full_link . "\">");
		$tpl12->set('[/full-link]', "</a>");
		$tpl12->set('{full-link}', $full_link);
		$tpl12->set("{title}", $serial_hdgo_content['title']);
		$tpl12->set("{category-link}", $my_cat_link);
		$tpl12->set("{category}", $my_cat);
		$tpl12->compile('serial_hdgo');
	}
	$tpl1->set("{serial_hdgo}", $tpl12->result['serial_hdgo']);
	unset($tpl12);
	$tpl1->compile('serial_hdgoed');
}
if($serial_hdgo["pagination"])
{
	if( $count_all > $serial_hdgo["limit_news"] )
	{
		$tpl->load_template( 'navigation.tpl' );
		$no_prev = false;
		$no_next = false;

		if( isset( $cstart ) and $cstart != "" and $cstart > 0 )
		{
			$prev = $cstart / $serial_hdgo["limit_news"];
			if ($prev == 1) $prev_page = $url_page . "/";
			else $prev_page = $url_page . "/page/" . $prev . "/";
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"" . $prev_page . "\">\\1</a>" );
		}
		else
		{
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>" );
			$no_prev = TRUE;
		}

		if( $serial_hdgo["limit_news"] )
		{
			$pages = "";
			if( $count_all > $serial_hdgo["limit_news"] )
			{
				$enpages_count = @ceil( $count_all / $serial_hdgo["limit_news"] );
				$cstart = ($cstart / $serial_hdgo["limit_news"]) + 1;
				if( $enpages_count <= 10 )
				{
					for($j = 1; $j <= $enpages_count; $j ++)
					{
						if( $j != $cstart )
						{
							if ($j == 1) $pages .= "<a href=\"" . $url_page . "/\">$j</a> ";
							else $pages .= "<a href=\"" . $url_page . "/page/" . $j . "/\">$j</a> ";
						}
						else
							$pages .= "<span>$j</span> ";
					}
				}
				else
				{
					$start = 1;
					$end = 10;
					$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
					if( $cstart > 0 )
					{
						if( $cstart > 6 )
						{
							$start = $cstart - 4;
							$end = $start + 8;
							if( $end >= $enpages_count-1 )
							{
								$start = $enpages_count - 9;
								$end = $enpages_count - 1;
							}
						}
					}
					
					if( $end >= $enpages_count-1 ) $nav_prefix = "";
					else $nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
					if( $start >= 2 )
					{
						if( $start >= 3 ) $before_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> "; else $before_prefix = "";
						$pages .= "<a href=\"" . $url_page . "/\">1</a> ".$before_prefix;
					} 
					
					for($j = $start; $j <= $end; $j ++)
					{
						if( $j != $cstart )
						{
							if ($j == 1) $pages .= "<a href=\"" . $url_page . "/\">$j</a> ";
							else $pages .= "<a href=\"" . $url_page . "/page/" . $j . "/\">$j</a> ";
						}
						else
							$pages .= "<span>$j</span> ";
					}
					
					if( $cstart != $enpages_count )
						$pages .= $nav_prefix . "<a href=\"" . $url_page . "/page/{$enpages_count}/\">{$enpages_count}</a>";
					else
						$pages .= "<span>{$enpages_count}</span> ";
				}
			}
			$tpl->set( '{pages}', $pages );
		}


		if( $serial_hdgo["limit_news"] AND $serial_hdgo["limit_news"] < $count_all AND $news_count < $count_all )
		{
			$next_page = $news_count / $serial_hdgo["limit_news"] + 1;
			$next = $url_page . '/page/' . $next_page . '/';
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"" . $next . "\">\\1</a>" );
		}
		else
		{
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>" );
			$no_next = TRUE;
		}

		if( !$no_prev OR !$no_next )
		{
			$tpl->compile( 'navi' );
			switch ( $config['news_navigation'] )
			{
				case "2" :
					$tpl1->result['serial_hdgoed'] = $tpl->result['navi'].$tpl1->result['serial_hdgoed'];
					break;
				case "3" :
					$tpl1->result['serial_hdgoed'] = $tpl->result['navi'].$tpl1->result['serial_hdgoed'].$tpl->result['navi'];
					break;
				default :
					$tpl1->result['serial_hdgoed'] .= $tpl->result['navi'];
					break;
			}
		}
		$tpl->clear();
	}
}
echo $tpl1->result['serial_hdgoed'];
?>