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
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) die( "Hacking attempt!" );
include ENGINE_DIR . '/data/serial_hdgo.php';
function showRow($title = "", $description = "", $field = "")
{
	echo "<tr>
	<td class=\"col-xs-10 col-sm-6 col-md-7\"><h6>{$title}</h6><span class=\"note large\">{$description}</span></td>
	<td class=\"col-xs-2 col-md-5 settingstd\">{$field}</td>
	</tr>";
}
function SetOption(&$item, $key)
{
	$item = "<option value=\"{$item}\">" . $item . "</option>";
}
function makeCheckBox($name, $selected)
{
	$selected = $selected ? "checked" : "";
	return "<input class=\"iButton-icons-tab\" type=\"checkbox\" name=\"$name\" value=\"1\" {$selected}>";
}
function showInput($name, $value)
{
	return "<input type=text style=\"width: 400px;text-align: center;\" name=\"{$name}\" value=\"{$value}\" size=20>";
}
function showTextarea($name, $value)
{
	return "<textarea style=\"width: 400px;\" name=\"{$name}\">" . $value . "</textarea>";
}
function showSelect($name, $value, $check = false)
{
	if(!$check) $multiple = "multiple";
	return "<select data-placeholder=\"Я ВЫБИРАЮ ТЕБЯ, НАКАЧУ!\" name=\"{$name}\" id=\"category\" class=\"valueselect\" {$multiple} style=\"width:100%;max-width:350px;\">{$value}</select>";
}
function makeDropDown($options, $selected, $check = false)
{
	if(in_array("-", $selected))
		$output .= '<option value="-" selected> --- </option>';
	else
		$output .= '<option value="-"> --- </option>';
	
	foreach ( $options as $index => $value )
	{
		if($check)
		{
			$output .= "<option value=\"$value\"";
		}
		else
		{
			$output .= "<option value=\"$index\"";
		}
		if( is_array( $selected ) )
		{
			foreach ( $selected as $element )
			{
				if($check && $element == $value)
					$output .= " selected ";
				elseif(!$check && $element == $index)
					$output .= " selected ";
			}
		}
		elseif( $selected == $index && !$check) $output .= " selected ";
		elseif( $selected == $value && $check) $output .= " selected ";
		$output .= ">$value</option>\n";
	}
	return $output;
}
function clearsss($find, $replace, $value, $config)
{
	$value = trim( strip_tags(stripslashes( $value )) );
	$value = htmlspecialchars( $value, ENT_QUOTES, $config);
	$value = preg_replace( $find, $replace, $value );
	return $value;
}
if($action == "save")
{
	$handler = fopen(ENGINE_DIR . '/data/serial_hdgo.php', "w");

	$find = array();
	$replace = array();
	$cat_serial = $_POST["cat_serial"];

	if($cat_serial)
	{
		foreach($cat_serial as $index => $val)
		{
			$cat_serial[$index] = intval($val);
		}
		$cat_serial = implode(",", $cat_serial);
	}
	
	$cat_anime = $_POST["cat_anime"];

	if($cat_anime)
	{
		foreach($cat_anime as $index => $val)
		{
			$cat_anime[$index] = intval($val);
		}
		$cat_anime = implode(",", $cat_anime);
	}
	
	$ex_voice = $_POST["ex_voice"];

	if($ex_voice)
	{
		foreach($ex_voice as $index => $val)
		{
			$ex_voice[$index] = intval($val);
		}
		$ex_voice = implode(",", $ex_voice);
	}
	
	$save_con = $_POST["save_con"];
	$world_art = isset($_POST["world_art"]) ? $db->safesql(trim(strip_tags(stripslashes($_POST["world_art"])))) : false;
	$find[] = "'\r'";
	$replace[] = "";
	$find[] = "'\n'";
	$replace[] = "";
	fwrite($handler, "<?PHP \n\n//Serial HDGO by Gameer\n\n\$serial_hdgo = array (\n\n'version' => \"v2\",\n\n");
	fwrite($handler, "'cat_serial' => '{$cat_serial}',\n\n");
	fwrite($handler, "'cat_anime' => '{$cat_anime}',\n\n");
	fwrite($handler, "'ex_voice' => '{$ex_voice}',\n\n");
	foreach ( $save_con as $name => $value ) {
		fwrite($handler, "'{$name}' => '{$value}',\n\n");
	}
	fwrite($handler, ");\n\n?>");
	fclose($handler);
	
	clear_cache();
	msg("info", $lang['opt_sysok'], "<b>{$lang['opt_sysok_1']}</b>", "$PHP_SELF?mod=serial_hdgo");
}
else
{
	$select_field_name = array();
	$all_field = xfieldsload();
	for($i = 0; $i < count($all_field); $i++)
	{
		$xf_select[$all_field[$i][0]] = $all_field[$i][1];
	}
echoheader( "<i class=\"icon-list-alt\"></i> Serial HDGO", "Главная страница модуля Serial HDGO" );
$cat_serial = CategoryNewsSelection((empty($serial_hdgo['cat_serial']) ? 0 : explode(",",$serial_hdgo['cat_serial'])));
$cat_anime = CategoryNewsSelection((empty($serial_hdgo['cat_anime']) ? 0 : explode(",",$serial_hdgo['cat_anime'])));

echo <<< HTML
<style>
.settingsb { text-align: center;}
.settingsb li { margin: 5px 5px 0 5px; position: relative; display: inline-block; text-align: center; }
.settingsb li a { 
	background: #f7f7f7;
	background: -moz-linear-gradient(top,  #f7f7f7 0%, #efefef 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f7f7f7), color-stop(100%,#efefef));
	background: -webkit-linear-gradient(top,  #f7f7f7 0%,#efefef 100%);
	background: -o-linear-gradient(top,  #f7f7f7 0%,#efefef 100%);
	background: -ms-linear-gradient(top,  #f7f7f7 0%,#efefef 100%);
	background: linear-gradient(top,  #f7f7f7 0%,#efefef 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f7f7f7', endColorstr='#efefef',GradientType=0 );
	border: 1px solid #d5d5d5;
	box-shadow: 0 0 0 1px #fcfcfc inset, 0 1px 1px #d5d5d5;
	-webkit-box-shadow: 0 0 0 1px #fcfcfc inset, 0 1px 1px #d5d5d5;
	-moz-box-shadow: 0 0 0 1px #fcfcfc inset, 0 1px 1px #d5d5d5;
	padding: 10px 10px 2px 10px;
	display: block;
	font-weight: 600;
	white-space: nowrap;
	color: #626262;
}
.settingsb li a:hover {  
	background: #f7f7f7;
	background: -moz-linear-gradient(top,  #f7f7f7 0%, #f2f2f2 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f7f7f7), color-stop(100%,#e6ef2f2f26e6));
	background: -webkit-linear-gradient(top,  #f7f7f7 0%,#f2f2f2 100%);
	background: -o-linear-gradient(top,  #f7f7f7 0%,#f2f2f2 100%);
	background: -ms-linear-gradient(top,  #f7f7f7 0%,#f2f2f2 100%);
	background: linear-gradient(top,  #f7f7f7 0%,#f2f2f2 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f7f7f7', endColorstr='#f2f2f2',GradientType=0 );
}
.settingsb li a:active {  
	box-shadow: none;
	background: #f4f4f4;
	background: -moz-linear-gradient(top,  #f4f4f4 0%, #f7f7f7 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f2f2f2), color-stop(100%,#f7f7f7));
	background: -webkit-linear-gradient(top,  #f4f4f4 0%,#f7f7f7 100%);
	background: -o-linear-gradient(top,  #f4f4f4 0%,#f7f7f7 100%);
	background: -ms-linear-gradient(top,  #f4f4f4 0%,#f7f7f7 100%);
	background: linear-gradient(top,  #f4f4f4 0%,#f7f7f7 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f4f4f4', endColorstr='#f7f7f7',GradientType=0 );
}
.settingsb li a > span { display: block; padding-top: 4px; }

.settingsb a > i {
	font-size: 32px;
	color: #808080;
}
</style>
<script type="text/javascript">
	function ChangeOption(selectedOption)
	{
		document.getElementById('general').style.display = "none";
		document.getElementById('data').style.display = "none";
		document.getElementById('meta').style.display = "none";
		document.getElementById('titled').style.display = "none";
		document.getElementById('block_out').style.display = "none";
		
		document.getElementById(selectedOption).style.display = "";
		$('#'+selectedOption).find(".iButton-icons-tab").iButton({
			labelOn: "<i class='icon-ok'></i>",
			labelOff: "<i class='icon-remove'></i>",
			handleWidth: 30
		});
	}

$(document).ready(function(){
	$("#showHideContent").click(function (){
		if ($("#content_help").is(":hidden"))
			$("#content_help").show("slow");
		else
			$("#content_help").hide("slow");
		return false;
	});
	$("#showHideContents").click(function (){
		if ($("#content_helps").is(":hidden"))
			$("#content_helps").show("slow");
		else
			$("#content_helps").hide("slow");
		return false;
	});
});
</script>
<div class="box">
	<div class="box-content">
		<div class="row box-section">
			<ul class="settingsb">
				<li style="min-width:90px;">
					<a href="javascript:ChangeOption('general');" class="tip" title="" data-original-title="Общие настройки"><i class="icon-cogs"></i><span>Общие</span></a>
				</li>
				<li style="min-width:90px;">
					<a href="javascript:ChangeOption('data');" class="tip" title="" data-original-title="Настройки данных"><i class="icon-file-alt"></i><span>Данные</span></a>
				</li>
				<li style="min-width:90px;">
					<a href="javascript:ChangeOption('meta');" class="tip" title="" data-original-title="Настройки META TITLE"><i class="icon-list-alt"></i><span>META TITLE</span></a>
				</li>
				<li style="min-width:90px;">
					<a href="javascript:ChangeOption('titled');" class="tip" title="" data-original-title="Настройки TITLE"><i class="icon-edit"></i><span>TITLE</span></a>
				</li>
				<li style="min-width:90px;">
					<a href="javascript:ChangeOption('block_out');" class="tip" title="" data-original-title="Настройки блока вывода"><i class="icon-reorder"></i><span>Блок вывода</span></a>
				</li>
			</ul>
		</div>
	</div>
</div>
<form action="" method="post">
	<div id="general">
		<div class="box">
			<div class="box-header"><div class="title">Настройки</div></div>
			<div class="box-content">
				<table class="table table-normal">
HTML;
	showRow("Категориия Сериалов", "Выберите все категории которые относяться к сериалам", showSelect("cat_serial[]", $cat_serial, false));
	showRow("Категориия Аниме", "Выберите все категории которые относяться к аниме", showSelect("cat_anime[]", $cat_anime, false));
	showRow("Посезонный вывод сериалов", "Посезонный вывод сериалов на сайте?", makeCheckBox( "save_con[season_serial_site]", "{$serial_hdgo['season_serial_site']}" ));
	showRow("ID Kinopoisk", "Выберите доп поле в котором находится ID Кинопоиска", showSelect("save_con[id_kp]", makeDropDown($xf_select, (empty($serial_hdgo['id_kp']) ? false : $serial_hdgo['id_kp'])), true));
	showRow("ID World-Art", "Выберите доп поле в котором находится ID World-Art", showSelect("save_con[world_art]", makeDropDown($xf_select, (empty($serial_hdgo['world_art']) ? false : $serial_hdgo['world_art'])), true));
	showRow("Номер сезона", "Выберите доп поле в котором находится номер сезона (только число)", showSelect("save_con[season]", makeDropDown($xf_select, (empty($serial_hdgo['season']) ? false : $serial_hdgo['season'])), true));
	showRow("Номер серии", "Выберите доп поле в котором находится номер серии (только число)", showSelect("save_con[seria]", makeDropDown($xf_select, (empty($serial_hdgo['seria']) ? false : $serial_hdgo['seria'])), true));
	showRow("Поднимать новость", "Поднимать новость при выходе новой серии?", makeCheckBox( "save_con[update_news]", "{$serial_hdgo['update_news']}" ));
	showRow("Social Posting", "Включить интеграцию с Social Posting? (Только если он у вас есть)", makeCheckBox( "save_con[socialposting]", "{$serial_hdgo['socialposting']}" ));
	showRow("API TOKEN", "Введите свой api token", showInput("save_con[token]", $serial_hdgo['token']));
echo <<<HTML
				</table>
			</div>
			<div class="box-footer padded">
				<input type="submit" class="btn btn-lg btn-green" value="{$lang['user_save']}">
			</div>
		</div>
	</div>
	<div id="block_out" style="display:none;">
		<div class="box">
			<div class="box-header"><div class="title">Блок вывода</div></div>
			<div class="box-content">
				<table class="table table-normal">
HTML;
	showRow("Вывод дней", "За сколько дней выводить данные?", showInput("save_con[interval_date]", $serial_hdgo['interval_date']));
	showRow("Лимит новостей", "Сколько новостей в общем выводить?", showInput("save_con[limit_news]", $serial_hdgo['limit_news']));
	showRow("Пагинация", "Включить переключатель страниц? (только если вы выводите подключение вывода вместо тега {content}", makeCheckBox( "save_con[pagination]", "{$serial_hdgo['pagination']}" ));
echo <<<HTML
				</table>
			</div>
			<div class="box-footer padded">
				<input type="submit" class="btn btn-lg btn-green" value="{$lang['user_save']}">
			</div>
		</div>
	</div>
	<div id="data" style="display:none;">
		<div class="box">
			<div class="box-header"><div class="title">Данные</div></div>
			<div class="box-content">
				<table class="table table-normal">
HTML;
	showRow("Вывод сезона", "Выберите доп поле в которое записывать сезон", showSelect("save_con[season_in]", makeDropDown($xf_select, (empty($serial_hdgo['season_in']) ? false : $serial_hdgo['season_in'])), true));
	showRow("Вывод серии", "Выберите доп поле в которое записывать серию", showSelect("save_con[seria_in]", makeDropDown($xf_select, (empty($serial_hdgo['seria_in']) ? false : $serial_hdgo['seria_in'])), true));
	showRow("Формат сезона", "Выберите в каком формате будет записываться в доп поле сезон сериала", showSelect("save_con[format_season]", makeDropDown(array("1" => "1 сезон", "2" => "1-2 сезон", "3" => "1,2 сезон"), (empty($serial_hdgo['format_season']) ? false : $serial_hdgo['format_season'])), true));
	showRow("Формат серии", "Выберите в каком формате будет записываться в доп поле серия сериала", showSelect("save_con[format_seria]", makeDropDown(array("1" => "1 серия", "2" => "1-2 серия", "3" => "1,2 серия"), (empty($serial_hdgo['format_seria']) ? false : $serial_hdgo['format_seria'])), true));
	showRow("Вывод озвучек", "Выберите доп поле в которое записывать доступные озвучки", showSelect("save_con[translate]", makeDropDown($xf_select, (empty($serial_hdgo['translate']) ? false : $serial_hdgo['translate'])), true));
	showRow("Формат озвучки", "Выберите в каком формате будет записываться в доп поле озвучки сериала", showSelect("save_con[format_translate]", makeDropDown(array("1" => "Lostfilm, Baibako", "2" => "Lostfilm / Baibako"), (empty($serial_hdgo['format_translate']) ? false : $serial_hdgo['format_translate'])), true));
	showRow("Закончен сериал", "Выберите доп поле которое отвечает за то что закончен сериал или нет. Нужно для того что бы модуль больше не слал запросы.", showSelect("save_con[end_serial]", makeDropDown($xf_select, (empty($serial_hdgo['end_serial']) ? false : $serial_hdgo['end_serial'])), true));
	showRow("Данные окончания сериала", "Введите значение с доп поля которое обозначает что сериал закончился", showInput("save_con[end_serial_text]", $serial_hdgo['end_serial_text']));
	showRow("Закончен сериал (количество серий)", "Выберите доп поле которое содержит в себе окончательное количество серий сериала (альтернатива двум верхним пунктам на проверку сериала).", showSelect("save_con[end_serial_seria]", makeDropDown($xf_select, (empty($serial_hdgo['end_serial_seria']) ? false : $serial_hdgo['end_serial_seria'])), true));
	$arr_two = array_merge(array("post_or_title" => "Заголовок новости", "hdgo_get_title" => "HDGO"), $xf_select);
	showRow("Откуда брать оригинальное русское название", "Выберите откуда брать оригинальное русское название для формирования тега {title} в meta title / title обновлении (рекомендуется выбрать HDGO)", showSelect("save_con[where_get_title]", makeDropDown($arr_two, (empty($serial_hdgo['where_get_title']) ? false : $serial_hdgo['where_get_title'])), true));
echo <<<HTML
				</table>
			</div>
			<div class="box-footer padded">
				<input type="submit" class="btn btn-lg btn-green" value="{$lang['user_save']}">
			</div>
		</div>
	</div>
	<div id="meta" style="display:none;">
		<div class="box">
			<div class="box-header"><div class="title">META TITLE</div></div>
			<div class="box-content">
				<div class="row box-section">
					<div class="well relative">
						<span class="triangle-button green"><i class="icon-bell"></i></span>
						Описание допустимых тегов для заполнения META TITLE.
						<br><br>
						<button type="button" class="btn btn-green" id="showHideContent">Описание тегов <i class="icon-caret-down"></i></button>
						<div id="content_help" style="display:none;">
							<table class="table table-normal table-hover">
								<thead>
									<tr>
										<td style="width: 200px">Название тега</td>
										<td>Описание</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><b>{title}</b></td>
										<td>Значение что заполнено в названии новости</td>
									</tr>
									<tr>
										<td><b>{xf_name}</b></td>
										<td>Где name это название доп поля, вместо которого будет выведено значение доп поля</td>
									</tr>
									<tr>
										<td><b>[xf_name] *text* [/xf_name]</b></td>
										<td>Где name это название доп поля, если доп поле для новости заполнено то текст внутри тегов будет показан</td>
									</tr>
									<tr>
										<td><b>[xf_not_name] *text* [/xf_not_name]</b></td>
										<td>Где name это название доп поля, если доп поле для новости не заполнено то текст внутри тегов будет показан</td>
									</tr>
									<tr>
										<td><b>{season}</b></td>
										<td>Номер сезона</td>
									</tr>
									<tr>
										<td><b>{season_format}</b></td>
										<td>Форматированный вывод сезона</td>
									</tr>
									<tr>
										<td><b>{seria}</b></td>
										<td>Номер серии</td>
									</tr>
									<tr>
										<td><b>{seria_format}</b></td>
										<td>Форматированный вывод серии</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<table class="table table-normal">
HTML;
	showRow("Изменять Meta title", "Изменять Meta title при выходе новой серии ?", makeCheckBox( "save_con[change_meta]", "{$serial_hdgo['change_meta']}" ));
	showRow("Формат сезона", "Выберите в каком формате будет записываться в доп поле сезон сериала", showSelect("save_con[format_season_meta]", makeDropDown(array("1" => "1 сезон", "2" => "1-2 сезон", "3" => "1,2 сезон"), (empty($serial_hdgo['format_season_meta']) ? false : $serial_hdgo['format_season_meta'])), true));
	showRow("Формат серии", "Выберите в каком формате будет записываться в доп поле серия сериала", showSelect("save_con[format_seria_meta]", makeDropDown(array("1" => "1 серия", "2" => "1-2 серия", "3" => "1,2 серия"), (empty($serial_hdgo['format_seria_meta']) ? false : $serial_hdgo['format_seria_meta'])), true));
	showRow("Прибавлять к серии", "Выберите сколько прибавлять к серии (т.е вышла серия 10, к ней прибавиться еще, 11, 12 и т.п и т.д)", showSelect("save_con[add_seria_meta]", makeDropDown(array("1" => "1", "2" => "2", "3" => "3"), (empty($serial_hdgo['add_seria_meta']) ? false : $serial_hdgo['add_seria_meta'])), true));
	showRow("Meta Title", "Введите тайтл который будет менятся", "<textarea style=\"width:100%;height:100px;\" name=\"save_con[meta_title]\">{$serial_hdgo['meta_title']}</textarea>");
	showRow("Meta Title по окончанию", "Введите тайтл который будет заменен когда сериал закончился", "<textarea style=\"width:100%;height:100px;\" name=\"save_con[meta_title_end]\">{$serial_hdgo['meta_title_end']}</textarea>");
echo <<<HTML
				</table>
			</div>
			<div class="box-footer padded">
				<input type="submit" class="btn btn-lg btn-green" value="{$lang['user_save']}">
			</div>
		</div>
	</div>
	<div id="titled" style="display:none;">
		<div class="box">
			<div class="box-header"><div class="title">TITLE</div></div>
			<div class="box-content">
				<div class="row box-section">
					<div class="well relative">
						<span class="triangle-button green"><i class="icon-bell"></i></span>
						Описание допустимых тегов для заполнения TITLE.
						<br><br>
						<button type="button" class="btn btn-green" id="showHideContents">Описание тегов <i class="icon-caret-down"></i></button>
						<div id="content_helps" style="display:none;">
							<table class="table table-normal table-hover">
								<thead>
									<tr>
										<td style="width: 200px">Название тега</td>
										<td>Описание</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><b>{title}</b></td>
										<td>Значение что заполнено в названии новости</td>
									</tr>
									<tr>
										<td><b>{xf_name}</b></td>
										<td>Где name это название доп поля, вместо которого будет выведено значение доп поля</td>
									</tr>
									<tr>
										<td><b>[xf_name] *text* [/xf_name]</b></td>
										<td>Где name это название доп поля, если доп поле для новости заполнено то текст внутри тегов будет показан</td>
									</tr>
									<tr>
										<td><b>[xf_not_name] *text* [/xf_not_name]</b></td>
										<td>Где name это название доп поля, если доп поле для новости не заполнено то текст внутри тегов будет показан</td>
									</tr>
									<tr>
										<td><b>{season}</b></td>
										<td>Номер сезона</td>
									</tr>
									<tr>
										<td><b>{season_format}</b></td>
										<td>Форматированный вывод сезона</td>
									</tr>
									<tr>
										<td><b>{seria}</b></td>
										<td>Номер серии</td>
									</tr>
									<tr>
										<td><b>{seria_format}</b></td>
										<td>Форматированный вывод серии</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<table class="table table-normal">
HTML;
	showRow("Изменять Title", "Изменять Title при выходе новой серии ?", makeCheckBox( "save_con[change_title]", "{$serial_hdgo['change_title']}" ));
	showRow("Формат сезона", "Выберите в каком формате будет записываться в доп поле сезон сериала", showSelect("save_con[format_season_title]", makeDropDown(array("1" => "1 сезон", "2" => "1-2 сезон", "3" => "1,2 сезон"), (empty($serial_hdgo['format_season_title']) ? false : $serial_hdgo['format_season_title'])), true));
	showRow("Формат серии", "Выберите в каком формате будет записываться в доп поле серия сериала", showSelect("save_con[format_seria_title]", makeDropDown(array("1" => "1 серия", "2" => "1-2 серия", "3" => "1,2 серия"), (empty($serial_hdgo['format_seria_title']) ? false : $serial_hdgo['format_seria_title'])), true));
	showRow("Прибавлять к серии", "Выберите сколько прибавлять к серии (т.е вышла серия 10, к ней прибавиться еще, 11, 12 и т.п и т.д)", showSelect("save_con[add_seria_title]", makeDropDown(array("1" => "1", "2" => "2", "3" => "3"), (empty($serial_hdgo['add_seria_title']) ? false : $serial_hdgo['add_seria_title'])), true));
	showRow("Title", "Введите тайтл который будет менятся", "<textarea style=\"width:100%;height:100px;\" name=\"save_con[news_title]\">{$serial_hdgo['news_title']}</textarea>");
	showRow("Title по окончанию", "Введите тайтл который будет заменен когда сериал закончился", "<textarea style=\"width:100%;height:100px;\" name=\"save_con[news_title_end]\">{$serial_hdgo['news_title_end']}</textarea>");
echo <<<HTML
				</table>
			</div>
			<div class="box-footer padded">
				<input type="submit" class="btn btn-lg btn-green" value="{$lang['user_save']}">
			</div>
		</div>
	</div>
	<input type="hidden" name="action" value="save" />
	<input type="hidden" name="mod" value="serial_hdgo" />
</form>

<center><b>by Gameer</b> - <a href="http://gameer.name">Gameer.name</a></center><br /><br />
<script>
	$(function()
	{
		$('.valueselect').chosen({allow_single_deselect:true, no_results_text: 'Ничего не найдено'});
	});
</script>
HTML;
echofooter();
}
?>