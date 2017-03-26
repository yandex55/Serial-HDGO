$('body').on('click', '[data-translator_id]', function(){
    $('#player-loader-overlay').animate({opacity: "show"}, 450);
    var $t_id = $(this).attr('data-translator_id'),
        $n_id = $(this).attr('data-news_id');
    $.post(dle_root + "engine/mod_gameer/serial_hdgo/serial_hdgo_ajax.php", {news_id : $n_id, translator_id : $t_id }, function(data){
        $("#ajaxPlayerm").html(data);
    });
    $('#player-loader-overlay').animate({opacity: "hide"}, 450);
});
$('body').on('click', '[data-season_id]', function(){
    $('#player-loader-overlay').animate({opacity: "show"}, 450);
    var $t_id = $(this).attr('data-voice_id'),
        $s_id = $(this).attr('data-season_id'),
        $n_id = $(this).attr('data-news_id');
    $.post(dle_root + "engine/mod_gameer/serial_hdgo/serial_hdgo_ajax.php", {news_id : $n_id, translator_id : $t_id, season_id : $s_id }, function(data){
        $("#ajaxPlayerm").html(data);
		console.log(1);
    });
    $('#player-loader-overlay').animate({opacity: "hide"}, 450);
});
$('body').on('click', '[data-seria_id]', function(){
    $('.b-simple_episode__item.active').removeClass('active');
    $(this).addClass('active');
    var $s_id = $(this).attr('data-seria_id'),
        $v_id = $("#videoMoonAjax").attr('src'),
        $n_id = $(this).attr('data-news_id');
    $.post(dle_root + "engine/mod_gameer/serial_hdgo/serial_hdgo_ajax.php", {news_id : $n_id, video_id : $v_id, seria_id : $s_id }, function(data){
        $("#videoMoonAjax").attr('src', data);
    });
});