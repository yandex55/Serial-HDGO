[design]<div class="b-translators__block"> 
	<ul id="translators-list" class="b-translators__list">
		{voice}
	</ul>
</div>
<div id="player" class="b-player" style="text-align: center;">
	[season]<ul id="simple-seasons-tabs" class="b-simple_seasons__list clearfix">
		{season}
	</ul>[/season]
	<div id="ibox">
		<div id="player-loader-overlay"></div>
		<div id="moon" style="height: 100%; margin: 0 auto; width: 100%;">
			<iframe id="videoMoonAjax" src="[/design]{url}[design]" width="680" height="400" frameborder="0" allowfullscreen=""></iframe>
		</div>
		<div id="simple-episodes-tabs">
			<div id="simple-episodes-list" class="b-simple_episodes__list clearfix" style="display: -webkit-flex;display: -ms-flexbox;display: flex;overflow-x:auto;overflow-y: hidden;">
				{seria}
			</div>
		</div>
	</div>
</div>[/design]