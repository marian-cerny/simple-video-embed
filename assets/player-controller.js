var $ = jQuery.noConflict();

$(document).ready(function(){

//ACTIVATE FANCYBOX AND CHANGE VIDEO CONTAINER ELEMENTS
$('.sve_popup_link').fancybox({
	// 'closeBtn': false,
	beforeLoad: function() {
		pause( $('#sve_video') );
	}
}).click(function( event ){
	event.preventDefault();
	swap_video_content( $(this) );
	// init();
});

$( '.fancybox-close' ).mouseover( function(){
	return false;
} );

var a_video_status = [];
var a_is_flash;
init();

// VIDEO CLICK
$('.sve_video_container').click(function()
{
	var video = $(this).children('.sve_video');
	if ( !is_playing(video) ) 
		play( video );
	else
		pause( video );
});

//MUTE BUTTON CLICK
$('.sve_mute_button').click(function(event)
{
	
	event.stopPropagation();
	var video = $(this).parent().children('.sve_video');	
	
	if ( is_muted( video ) ) 
		unmute( video )
	else 
		mute( video )
	
	
});

//CONTAINER HOVER
$('.sve_video_container').hover(
	function()
	{
		if ( !b_is_flash )
			show_controls( $(this) );
	},
	function()
	{
		hide_controls( $(this) );
	}
);

//AFTER VIDEO PLAYBACK
$(".sve_video").bind("ended", function() 
{	
	finish( $(this) )
});

$('.sve_mute_button').hover(
	//mouse in
	function()
	{
		$(this).css('opacity', 1);
	},
	//mouse out
	function()
	{
		$(this).css('opacity', 0.5);
	}
);


function play(video)
{
	//get DOM object and start playing video
	o_object = video.get(0);
	o_object.play();		
	//change video status variable
	a_video_status[video.attr('id')].playing = true;
	//hide cover image
	video.siblings('.sve_cover_image').fadeOut();
	//swap and show play button image
	swap_image_src( video.siblings('.sve_play_button'), 'pause' );
	video.siblings('.sve_play_button').fadeIn();
}


function pause(video)
{
	//get DOM object and pause the video
	o_object = video.get(0);
	// b_is_flash = (typeof HTMLVideoElement == 'function') ? false : true;
	if ( b_is_flash )
		return false;
	o_object.pause();
	//change video status variable
	a_video_status[video.attr('id')].playing = false;	
	//swap and show pause button
	swap_image_src( video.siblings('.sve_play_button'), 'play' );
	video.siblings('.sve_play_button').fadeIn();
}


function finish(video)
{
	//change video status variable
	a_video_status[video.attr('id')].playing = false;
	//swap image for replay button
	swap_image_src( video.siblings('.sve_play_button'), 'replay' );
	video.siblings('.sve_cover_image').fadeIn('slow');
}


function mute(video)
{
	video.prop('muted', true);
	a_video_status[video.attr('id')].muted = true;
	swap_image_src( video.siblings('.sve_mute_button'), 'unmute' );
	video.siblings('.sve_mute_button').attr('title', 'Unmute video');
}


function unmute(video)
{
	video.prop('muted', false);
	a_video_status[video.attr('id')].muted = false;
	swap_image_src( video.siblings('.sve_mute_button'), 'mute' );
	video.siblings('.sve_mute_button').attr('title', 'Mute video');
}


function show_controls(container)
{
	container.children('.button').fadeIn();
	setTimeout( function(){
		container.children('.button').fadeOut();
	}, 3000 );
}


function hide_controls(container)
{
	container.children('.button').fadeOut();
}


function swap_image_src ( img, new_name )
{
	var s_orig_src = img.attr('src');
	var s_new_src = s_orig_src.replace( /images\/.*\.png$/, '/images/' + new_name + '.png' );
	img.attr('src', s_new_src);		
}


function init()
{
	//check all videos if they are playing/muted
	$('.sve_video').each( function(){
		//set video status
		video_status = {
			'playing': $(this).attr('autoplay') == 'autoplay',
			'muted': $(this).attr('muted') == 'muted'
		}	
		a_video_status[$(this).attr('id')] = video_status;
		
		//change image to 'pause' if playing
		if (video_status.playing)
			swap_image_src( $(this).siblings('.sve_play_button'), 'pause' );
		
		//change image to 'unmute' if muted
		if (video_status.muted)
		{
			swap_image_src( $(this).siblings('.sve_mute_button'), 'unmute' );
			$(this).siblings('.sve_mute_button').attr('title', 'Unmute video');
		}
	});
	
	//b_is_flash = (typeof HTMLVideoElement == 'function') ? false : true;
	b_is_flash = ( $.browser.msie && jQuery.browser.version.substring(0, 2) == "8." );
	//hide controls
	$('.sve_cover_image').hide();
	$('.sve_mute_button').hide();
	$('.sve_play_button').hide();
	
	
}

function is_playing(video)
{
	var id = video.attr('id');
	return a_video_status[id].playing;
}

function is_muted(video)
{
	var id = video.attr('id');
	return a_video_status[id].muted;
}

function swap_video_content( link )
{
	// GET VIDEO CONTENT FROM HIDDEN FIELDS
	var a_sources = [];
	link.find('.hidden_content .source').each(function(){ 
		a_sources.push({
			src: $(this).find('.src').html(),
			type: $(this).find('.type').html()
		});
	});
	
	// get original video tag HTML
	var video_html = $( '#sve_video' ).clone().wrap('<div>').parent().html();
	// get first occurence of '<source'
	var first_src_pos = video_html.indexOf('<source');
	// trim string either before first source, or before closing video tag
	var video_start_tag_end = ( first_src_pos > 0 )
		? first_src_pos
		: video_html.length - 8;
	var video_start_tag = video_html.substring( 0, video_start_tag_end );
	
	// start video tag
	var video_tag_complete = video_start_tag;
	
	for( var i=0; i < a_sources.length; i++ )
	{
		// add all sources to video tag
		video_tag_complete += "<source src=\""+a_sources[i].src+"\" type=\""+a_sources[i].type+"\"/> ";
	}
	// end video tag
	video_tag_complete += '</video>';
	
	// remove old, insert new video tag
	var new_video = $('#sve_video').clone(); 
	var video_parent = $('#sve_video').parent();
	$('#sve_video').remove();
	video_parent.prepend( video_tag_complete );
}


});