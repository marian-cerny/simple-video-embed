<?php
/*
Plugin Name: Simple Video Embed
Plugin URI: 
Description: Insert HTML5 videos with simple controls, a cover image and Flash fallback using a single shortcode. Can insert all videos in a folder too.
Version: 1.0
Author: Marian Cerny
Author URI: http://mariancerny.com
License: GPL2
*/



class mc_simple_video_embed 
{	
	
	
// *******************************************************************
// ------------------------------------------------------------------
// 					INITIALIZATION 
// ------------------------------------------------------------------
// *******************************************************************
	
	var $settings = array(
		'autoplay' => 0,
		'start_muted' => 0,
		'video_directory' => '/video/',
		'width' => 320,
		'height' => 240,
		'popup_link_width' => 320,
		'popup_link_height' => 240
	);
	var $video_dir_path;
	var $video_dir_url;
		
	/* CONSTRUCTOR */
	public function __construct()
	{		
		$this->get_settings();
		
		/* GET PATH AND URL TO DEFAULT VIDEO FOLDER */ 
		$s_upload_dir = wp_upload_dir();
		$this->video_dir_url = $s_upload_dir['baseurl'] . $this->settings['video_directory'];
		$this->video_dir_path = $s_upload_dir['basedir'] . $this->settings['video_directory'];
		
		/* DEFINE CONSTANTS */
		define( 'PLUGIN_NAME', 'Simple Video Embed' );
		define( 'PLUGIN_SLUG', 'simple-video-embed' );
		define( 'PLUGIN_URL', plugins_url( '', __FILE__ ) );
		define( 'ASSETS_URL', plugins_url( '', __FILE__ ) . '/assets/' );
		
		/* CREATE THE VIDEO DIRECTORY */
		if ( !file_exists( $this->video_dir_path ) )
			mkdir( $this->video_dir_path );
		
		/* ADD ACTIONS, SHORTCODES AND FILTERS */
  		add_action( 'admin_menu', array( $this, 'mc_register_settings') );
		add_action( 'wp_enqueue_scripts', array($this, 'mc_enqueue_scripts_and_styles' ));
		add_shortcode('sve', array($this, 'mc_shortcode'));  
		add_shortcode('sve_popup', array($this, 'mc_shortcode_popup'));  
		add_shortcode('sve_folder', array($this, 'mc_shortcode_folder'));  
    	add_filter('widget_text', 'do_shortcode');
	}
	
	/* ENQUEUE SCRIPTS */
	function mc_enqueue_scripts_and_styles() 
	{
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'sve-fancybox', ASSETS_URL . 'fancybox/jquery.fancybox.pack.js' );
		//wp_enqueue_script( 'divbox', ASSETS_URL . 'divbox/divbox.js' );
		
		wp_enqueue_script( 'sve-player_controller', ASSETS_URL . 'player-controller.js' );
		wp_localize_script( 'sve-player_controller', 'plugin_settings', $this->settings );
		
		wp_enqueue_style( 'sve-fancybox', ASSETS_URL . 'fancybox/jquery.fancybox.css' );
		//wp_enqueue_style( 'divbox', ASSETS_URL . 'divbox/css/divbox.css' );
		wp_enqueue_style( 'sve-main', ASSETS_URL . 'styles.css' );
	}
	
	
	/* ASSIGN SETTINGS FROM PLUGIN OPTIONS TO SETTINGS VARIABLE */
	function get_settings()
	{
		foreach ( $this->settings as $key => $value )
		{
			$option = get_option( 'sve_options_' . $key );
			//echo $key  . ' - ' . $option . ' - ' . $value . ' <br/> ';
			if ( !empty( $option ) )
				$this->settings[$key] = $option;
		}
	}
	
	
// *******************************************************************
// ------------------------------------------------------------------
// 					CREATING THE OPTIONS MENU 
// ------------------------------------------------------------------
// *******************************************************************
	/* CREATE AN ENTRY IN THE SETTINGS MENU AND REGISTER SETTINGS */
	function mc_register_settings() 
	{		
		add_options_page(
			PLUGIN_NAME, 
			PLUGIN_NAME, 
			'manage_options', 
			PLUGIN_SLUG, 
			array( $this, 'mc_output_options_page' )
		);
		
		// ********************** GENERAL ********************** //
		// CREATE 'GENERAL OPTIONS' SECTION 
		add_settings_section( 
			'sve_general_options', 
			'General settings', 
			array( $this, 'mc_output_settings_section_general' ), 
			PLUGIN_SLUG 
		);
		
		// ADD 'WIDTH' SETTING
		add_settings_field( 
			'sve_options_width', 
			'Video width in px (default is 320)',
			array($this, 'mc_output_option_width'), 
			PLUGIN_SLUG, 
			'sve_general_options' 
		);
		
		// ADD 'HEIGHT' SETTING
		add_settings_field( 
			'sve_options_height', 
			'Video height in px (default is 240',
			array($this, 'mc_output_option_height'), 
			PLUGIN_SLUG, 
			'sve_general_options' 
		);
		
		// ADD 'AUTOPLAY' SETTING
		add_settings_field( 
			'sve_options_autoplay', 
			'Automatically play videos on page load',
			array($this, 'mc_output_option_autoplay'), 
			PLUGIN_SLUG, 
			'sve_general_options' 
		);
		
		// ADD 'START MUTED' SETTING
		add_settings_field( 
			'sve_options_start_muted', 
			'Start videos muted',
			array($this, 'mc_output_option_start_muted'), 
			PLUGIN_SLUG, 
			'sve_general_options' 
		);
		
		// ADD 'VIDEO DIRECTORY' SETTING
		add_settings_field( 
			'sve_options_video_directory', 
			'Video directory relative to \'/wp-content/uploads\' (default is \'/video/\')',
			array($this, 'mc_output_option_video_directory'), 
			PLUGIN_SLUG, 
			'sve_general_options' 
		);
		
		// ********************** POPUP ********************** //
		// CREATE 'POPUP OPTIONS' SECTION 
		add_settings_section( 
			'sve_popup_options', 
			'Pop-up settings', 
			array( $this, 'mc_output_settings_section_popup' ), 
			PLUGIN_SLUG 
		);
		
		// ADD 'POPUP LINK WIDTH' SETTING
		add_settings_field( 
			'sve_options_popup_link_width', 
			'Pop-up link image width in px (default is 320)',
			array($this, 'mc_output_option_popup_link_width'), 
			PLUGIN_SLUG, 
			'sve_popup_options' 
		);
		
		// ADD 'POPUP LINK HEIGHT' SETTING
		add_settings_field( 
			'sve_options_popup_link_height', 
			'Pop-up link image height in px (default is 240)',
			array($this, 'mc_output_option_popup_link_height'), 
			PLUGIN_SLUG, 
			'sve_popup_options' 
		);
		
		register_setting('mc_sve_options', 'sve_options_height');
		register_setting('mc_sve_options', 'sve_options_width');
		register_setting('mc_sve_options', 'sve_options_autoplay');
		register_setting('mc_sve_options', 'sve_options_start_muted');
		register_setting('mc_sve_options', 'sve_options_video_directory');
		register_setting('mc_sve_options', 'sve_options_popup_link_width');
		register_setting('mc_sve_options', 'sve_options_popup_link_height');
	}
	
	
	/* OUTPUT GENERAL SETTINGS SECTION */
	function mc_output_settings_section_general()
	{
		echo '';
	}
	
	
	/* OUTPUT POPUP SETTINGS SECTION */
	function mc_output_settings_section_popup()
	{
		echo '';
	}
	
	
	/* OUTPUT WIDTH SETTING FIELD */
	function mc_output_option_width()
	{
		echo '<input name="sve_options_width" id="sve_options_width" type="number" value="'.get_option('sve_options_width'). '" />';
	}
	
	
	/* OUTPUT HEIGHT SETTING FIELD */
	function mc_output_option_height()
	{
		echo '<input name="sve_options_height" id="sve_options_height" type="number" value="'.get_option('sve_options_height'). '" />';
	}
	
	
	/* OUTPUT AUTOPLAY SETTINGS FIELD */
	function mc_output_option_autoplay()
	{
		echo '<input name="sve_options_autoplay" id="sve_options_autoplay" type="checkbox" value="1" class="code" ' . checked( 1, get_option('sve_options_autoplay'), false ) . ' /> Enabled';
	}
	
	
	/* OUTPUT START MUTED SETTING FIELD */
	function mc_output_option_start_muted()
	{
		echo '<input name="sve_options_start_muted" id="sve_options_start_muted" type="checkbox" value="1" class="code" ' . checked( 1, get_option('sve_options_start_muted'), false ) . ' /> Enabled';
	}
	
	
	/* OUTPUT DEFAULT DIRECTORY SETTING FIELD */
	function mc_output_option_video_directory()
	{
		echo '<input name="sve_options_video_directory" id="sve_options_video_directory" type="text" value="'.get_option('sve_options_video_directory'). '" />';
	}
	
	
	/* OUTPUT POPUP LINK WIDTH SETTING FIELD */
	function mc_output_option_popup_link_width()
	{
		echo '<input name="sve_options_popup_link_width" id="sve_options_popup_link_width" type="number" value="'.get_option('sve_options_popup_link_width'). '" />';
	}
	
	
	/* OUTPUT POPUP LINK HEIGHT SETTING FIELD */
	function mc_output_option_popup_link_height()
	{
		echo '<input name="sve_options_popup_link_height" id="sve_options_popup_link_height" type="number" value="'.get_option('sve_options_popup_link_height'). '" />';
	}
	
	
	/* OUTPUT OPTIONS PAGE */
	function mc_output_options_page()
	{
		?>
		<div class="wrap">
		<h2><?php echo PLUGIN_NAME; ?> Settings</h2>
        <p>Values explicitly defined in the shortcode have higher priority than these settings.</p>
        <p>If you leave these fields and shortcode attributes empty, the default values will be used.</p>
		
		<form method="post" action="options.php">
		
			<?php settings_fields( 'mc_sve_options' ); ?>
			<?php do_settings_sections( PLUGIN_SLUG  ); ?>     
			<?php submit_button(); ?>
		
		</form>
		</div>
		<?php
	}
	
// *******************************************************************
// ------------------------------------------------------------------
// 					MAIN FUNCTIONS 
// ------------------------------------------------------------------
// *******************************************************************
	
	  
	/* EXECUTE SHORTCODE */
    public function mc_shortcode( $atts )  
    { 
		// EXTRACT ATTRIBUTES FROM SHORTCODE 
		extract( shortcode_atts( array(
			'filename' => 'video',  
			'width' => $this->settings['width'],
			'height' => $this->settings['height'],
			'autoplay' => $this->settings['autoplay'],  
			'start_muted' => $this->settings['start_muted'],
			'video_directory' => $this->settings['video_directory'],  
		), $atts));
		
		/* IF VIDEO DIRECTORY IS SET, CHANGE THE CLASS VARIABLE */
		if ( !empty($video_directory) )	
		{ 
			$s_upload_dir = wp_upload_dir();
			$this->video_dir_url = $s_upload_dir['baseurl'] . $video_directory;
			$this->video_dir_path = $s_upload_dir['basedir'] . $video_directory;
		}
		
		/* CREATE OUTPUT VARIABLE AND ASSIGN THE VALUE IN THE OUTPUT FILE */ 
		include('assets/output-video.php');
		return $s_output;	
    }
	  
	  
	/* EXECUTE POPUP SHORTCODE */
    public function mc_shortcode_popup( $atts )  
    { 
		// EXTRACT ATTRIBUTES FROM SHORTCODE 
		extract( shortcode_atts( array(
			'filename' => 'video',
			'popup_link_width' => $this->settings['popup_link_width'],
			'popup_link_height' => $this->settings['popup_link_height'],
			'width' => $this->settings['width'],
			'height' => $this->settings['height'],
			'autoplay' => $this->settings['autoplay'],  
			'start_muted' => $this->settings['start_muted'],
			'video_directory' => $this->settings['video_directory'],  
		), $atts));
		
		/* IF VIDEO DIRECTORY IS SET, CHANGE THE CLASS VARIABLE */
		if ( !empty($video_directory) )	
		{ 
			$s_upload_dir = wp_upload_dir();
			$this->video_dir_url = $s_upload_dir['baseurl'] . $video_directory;
			$this->video_dir_path = $s_upload_dir['basedir'] . $video_directory;
		}
		
		/* CREATE OUTPUT VARIABLE AND ASSIGN THE VALUE IN THE OUTPUT FILE */
		$is_popup = true; 		
		include('assets/output-video.php'); 
		return $s_output;	
		
    }
	  
	  
	/* EXECUTE POPUP SHORTCODE */
    public function mc_shortcode_folder( $atts )  
    { 
		// EXTRACT ATTRIBUTES FROM SHORTCODE 
		extract( shortcode_atts( array(
			'video_directory' => $this->settings['video_directory'],
			'popup_link_width' => $this->settings['popup_link_width'],
			'popup_link_height' => $this->settings['popup_link_height'],
			'width' => $this->settings['width'],
			'height' => $this->settings['height'],
			'autoplay' => $this->settings['autoplay'],  
			'start_muted' => $this->settings['start_muted'],  
		), $atts));
		
		/* IF VIDEO DIRECTORY IS SET, CHANGE THE CLASS VARIABLE */
		if ( !empty($video_directory) )	
		{ 
			$s_upload_dir = wp_upload_dir();
			$this->video_dir_url = $s_upload_dir['baseurl'] . $video_directory;
			$this->video_dir_path = $s_upload_dir['basedir'] . $video_directory;
		}
		
		/* 		GET ALL UNIQUE FILENAMES		*/
		$a_filenames = array();
		//OPEN VIDEO DIRECTORY
		$r_directory = opendir( $this->video_dir_path );
		//GET ALL FILENAMES WITHOUT EXTENSIONS
		while ( ( $file = readdir( $r_directory) ) !== false )
		{
			if ( !empty($file) && ($file != '.') && ($file != '..') && ( !is_dir( $this->video_dir_path .'/'. $file ) ) )
				$a_filenames[] = substr( $file, 0, strpos( $file, '.' ) );
		}
		closedir($r_directory);
		//DELETE DUPLICATES
		$a_filenames = array_unique( $a_filenames );
		
		//PASS VARIABLES TO SCRIPT
		$this->player_vars = array(
			'autoplay' => $autoplay,
		);
		
		/* CREATE OUTPUT VARIABLE AND ASSIGN THE VALUE IN THE OUTPUT FILE */
		include('assets/output-video_folder.php'); 
		return $s_output;		
		
    }
	
	
	/* RETURN A LIST OF EXISTING EXTENSIONS  */
	function get_existing_extensions( $s_type, $s_filename )
	{		
		$a_possible_extensions = array();
	
		//DEFINE POSSIBLE FILE EXTENSIONS
		switch ( $s_type )
		{ 
			case 'image' :
			{
				$a_possible_extensions = array(
					'jpg', 'png', 'gif', 'bmp'
				);
				break;
			}
			case 'video' :
			{
				$a_possible_extensions = array(
					'mp4', 'ogg', 'ogv', 'webm'	
				);
				break;
			}
			case 'flash' :
			{
				$a_possible_extensions = array(
					'flv', 'swf'
				);
				break;
			}
		}
		
		$a_result = array(); 
		
		foreach ( $a_possible_extensions as $s_ext )		
		{	
			$s_possible_filename = $s_filename . '.' . $s_ext;
			if ( file_exists( $this->video_dir_path . $s_possible_filename ) )
			{
				$a_result[] = $s_ext;
			}
		}
		
		return $a_result;
	}
		
	
}

$mc_simple_video_embed = new mc_simple_video_embed();





?>