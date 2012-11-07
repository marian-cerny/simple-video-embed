<?php
	
	$s_flash_player_url = ASSETS_URL . 'flvplayer.swf';
	$s_image_dir_url = ASSETS_URL . 'images';
	$s_video_url = $this->video_dir_url . $filename;
	
	$a_existing_video_extensions = $this->get_existing_extensions( 'video', $filename );
	$a_existing_image_extensions = $this->get_existing_extensions( 'image', $filename );
	$a_existing_flash_extensions = $this->get_existing_extensions( 'flash', $filename );
	
	$s_output = '';
	
/* INSERT POPUP LINK WITH IMAGE */
if ( $is_popup )
{
	$s_output .= "
		<a 
			class='sve_popup_link'
			href='#sve_video_container_{$filename}'
		>
			<img 
				src='{$s_video_url}.{$a_existing_image_extensions[0]}' 
				style='width: {$popup_link_width}px; height: {$popup_link_height}px;'
			/>
		</a>
	";
}

$style = ( $is_popup ) ? 'display: none;' : '';	

/* START CONTAINER DIV AND VIDEO TAG */
$s_output .= "
<div 
	id='sve_video_container_{$filename}'
	class='sve_video_container'
	style='width: {$width}px; height: {$height}px; {$style}'
>
";

$s_output .= "
<video 
	id='sve_video_{$filename}' 
	class='sve_video' 
	width='{$width}' 
	height='{$height}' ";
	
/* INSERT AUTOPLAY ATTR IF OPTION IS ENABLED */
if ( $autoplay && !$is_popup )
	$s_output .= "autoplay='autoplay' ";
	
/* INSERT MUTED ATTR IF OPTION IS ENABLED */
if ( $start_muted )
	$s_output .= "muted='muted' ";
	

/* INSERT POSTER IF FILE IS PRESENT */
if ( !empty( $a_existing_image_extensions ) )
	$s_output .= "poster='" . $s_video_url . '.' . $a_existing_image_extensions[0] . "' ";
	
/* CLOSE STARTING VIDEO TAG */	
$s_output .= ">";
	

/* ADD SRC TAGS FOR ALL EXISTING VIDEO FILES */
foreach ( $a_existing_video_extensions as $s_ext )
{
	$s_video_type = 'video/'.$s_ext;
	$s_output .= "<source src='{$s_video_url}.{$s_ext}' type='{$s_video_type}' />";
}

/* OUTPUT FLASH TAG */
if ( !empty( $a_existing_flash_extensions ) )
{
	//build flashvars attribute
	$s_flashvars = "";
	$s_flashvars .= ($autoplay) ? 'autostart=true&amp;' : '';
	$s_flashvars .= $s_video_url . '.' . $a_existing_flash_extensions[0]; 
	
	$s_output .= "
	
	<embed 
		type='application/x-shockwave-flash' 
		src='{$s_flash_player_url}' 
		width='{$width}' 
		height='{$height}' 
		id='sve_flash_player' 
		quality='high' 
		wmode='transparent' 
		allowscriptaccess='always'
		flashvars='{$s_flashvars}'
	>
	
	";
}

/* FINISH VIDEO TAG */ 
$s_output .= "</video>";

/* OUTPUT CONTROLLER IMAGES */
$i_play_button_top = $height/2 - 20;
$i_play_button_left = $width/2 - 20;
$s_mute_image = ( $start_muted ) ? 'unmute' : 'mute';

$s_output .= "
<img 
	id='sve_mute_button_{$filename}' 
	class='sve_mute_button button'
	title='Mute video' 
	src='{$s_image_dir_url}/{$s_mute_image}.png'  
	width='32' 
	height='32' 
/>

<img 
	id='sve_play_button_{$filename}'
	class='sve_play_button button'
	src='{$s_image_dir_url}/play.png' 
	width='40' 
	height='40'
	style='top: {$i_play_button_top}px; left: {$i_play_button_left}px;' 
/>

<img 
	id='sve_cover_image_{$filename}'
	class='sve_cover_image'
	src='{$s_video_url}.{$a_existing_image_extensions[0]}'
	width='{$width}'
	height='{$height}' 
/>
";

/* FINISH CONTAINER DIV */
$s_output .= "</div>";




