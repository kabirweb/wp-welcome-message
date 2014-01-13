<?php
/*
Plugin Name: WP Welcome Message
Plugin URI: http://www.a1netsolutions.com/Products/WP-Welcome-Message
Description: <strong>WP Welcome Message</strong> is a wordpress plugin, which help your to make any announcement, special events, special offer, signup message or such kind of message, displayed upon your website's visitors when the page is load through a popup box.
Version: 0.1.4
Author: Ahsanul Kabir
Author URI: http://www.ahsanulkabir.com/
License: GPL2
License URI: license.txt
*/

function wpwm_posts_init()
{
  $args = array
  (
    'public' => false,
    'publicly_queryable' => false,
    'show_ui' => false, 
    'show_in_menu' => false, 
    'rewrite' => array( 'slug' => 'wpwmposts' ),
    'capability_type' => 'post',
    'has_archive' => false, 
    'supports' => array( 'title', 'editor', 'excerpt' )
  ); 
  register_post_type( 'wpwmposts', $args );
}
add_action( 'init', 'wpwm_posts_init' );

function wpwm_getCurrentUser()
{
	if (function_exists('wp_get_current_user'))
	{
		return wp_get_current_user();
	}
	else if (function_exists('get_currentuserinfo'))
	{
		global $userdata;
		get_currentuserinfo();
		return $userdata;
	}
	else
	{
		$user_login = $_COOKIE["USER_COOKIE"];
		$current_user = $wpdb->get_results("SELECT * FROM `".$wpdb->users."` WHERE `user_login` = '".$user_login."' ;");
		return $current_user;
	}
}

function wpwm_printCreatePost($inputContent)
{
	$newPostAuthor = wpwm_getCurrentUser();
	$newPostArg = array
	(
		'post_author' => $newPostAuthor->ID,
		'post_content' => $inputContent,
		'post_status' => 'publish',
		'post_type' => 'wpwmposts'
	);
	$new_post_id = wp_insert_post($newPostArg);
	return $new_post_id;
}

function wpwm_updatePost($inputContent, $id)
{
	$newPostAuthor = wpwm_getCurrentUser();
	$newPostArg = array
	(
		'ID' => $id,
		'post_author' => $newPostAuthor->ID,
		'post_content' => $inputContent,
		'post_status' => 'publish',
		'post_type' => 'wpwmposts'
	);
	$new_post_id = wp_insert_post($newPostArg);
	return $new_post_id;
}

function wpwm_frontEndJS()
{
	wp_enqueue_script('wpwm-frontEndJS', ( plugins_url('lib/js/wpwm_'.get_option( 'wpwm_boxsetly' ).'.js', __FILE__) ), array('jquery'));
}
add_action('wp_enqueue_scripts', 'wpwm_frontEndJS');

function wpwm_backEndCss()
{
	wp_enqueue_style( 'wpwm-cssB', ( plugins_url('lib/css/wpwm_backEnd.css', __FILE__) ) );
}
add_action( 'admin_init', 'wpwm_backEndCss' );

function wpwm_frontEndCss()
{
	wp_enqueue_style( 'wpwm-cssF', ( plugins_url('lib/css/wpwm_frontEnd.css', __FILE__) ) );
}
add_action( 'wp_enqueue_scripts', 'wpwm_frontEndCss' );

define(WPWM_LIB, "../wp-content/plugins/wp-welcome-message/lib/");

function wpwm_defaults()
{
	$wpwm_defaults = WPWM_LIB.'wpwm_defaults.php';
	if(is_file($wpwm_defaults))
	{
		require $wpwm_defaults;
		foreach($addOptions as $addOptionK => $addOptionV)
		{
			update_option($addOptionK, $addOptionV);
		}
		unlink($wpwm_defaults);
	}
}

function wpwm_activate()
{
	$inputContent = 'Welcome to '.get_bloginfo('name').', '. get_bloginfo('description');
	$new_post_id = wpwm_printCreatePost($inputContent);
	$lastID = get_option( 'wpwm_ststs' );
	update_option( 'wpwm_postsid', $new_post_id );
	wpwm_defaults();
}
register_activation_hook( __FILE__, 'wpwm_activate' );

function wpwm_AdminMenu()
{
	add_menu_page('Welcome Message', 'Welcome Msg', 'manage_options', 'wpwellmsg', 'wpWellMsg', (plugins_url('lib/img/icon.png', __FILE__)));
}
add_action('admin_menu', 'wpwm_AdminMenu');

function wpwm_getCr($k, $v)
{
	echo '<div class="postbox wpwm_cr"><h3 class="hndle"><span>'.$k.'</span></h3><div class="inside">'.get_option($v).'</div></div>';
}

function wpwm_printCr()
{
	wpwm_getCr('Plugins &amp; Themes', 'wpwm_other');
	wpwm_getCr('WordPress Development', 'wpwm_hire');
}

function wpwm_select( $iget, $iset, $itxt )
{
	if( $iget == $iset )
	{
		echo '<option value="'.$iset.'" selected="selected">'.$itxt.'</option>';
	}
	else
	{
		echo '<option value="'.$iset.'">'.$itxt.'</option>';
	}
}

function wpWellMsg()
{
	if( isset($_POST["stats"]) )
	{
		if( $_POST["stats"] == 'Disable')
		{
			update_option( 'wpwm_ststs', 'off' );
		}
		else
		{
			update_option( 'wpwm_ststs', 'on' );
		}
	}
	if( isset($_POST["settinsg"]) )
	{
		update_option( 'wpwm_loc', $_POST["wpwm_loc"] );
		update_option( 'wpwm_log', $_POST["wpwm_log"] );
		update_option( 'wpwm_boxsetly', $_POST["wpwm_boxsetly"] );
		update_option( 'wpwm_bgstyle', $_POST["wpwm_bgstyle"] );
		update_option( 'wpwmTemplate', $_POST["wpwmTemplate"] );
		update_option( 'wpwm_onlyFirstVisit', $_POST["wpwm_onlyFirstVisit"] );
	}
	if( isset($_POST["wpwmeditor"]) )
	{
		$update_post_id = get_option( 'wpwm_postsid' );
		wpwm_updatePost($_POST["wpwmeditor"], $update_post_id);
		update_option( 'wpwm_postsid', $update_post_id );
	}
	?>
<div id="wpwm_container" class="wrap">
  <div id="wpwm_body">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br />
    </div>
    <h2>WP Welcome Message</h2>
    <a href="http://www.youtube.com/watch?v=31eTM1kXnnE" target="_blank">
    <img src="<?php echo plugins_url('lib/img/uvg.png', __FILE__); ?>" style="border:0 none;float:right;height:50px;position:relative;width:auto;z-index:200;top:-40px;" />
    </a>
    <?php $wpwm_defaults = get_option('wpwm_defaults'); if( !isset($wpwm_defaults) || empty($wpwm_defaults) ){echo '<div id="wpwm_errorMSG">>Error! please do the following -<br />1. Deactivate and Delete this plugin.<br />2. <a href="http://downloads.wordpress.org/plugin/wp-welcome-message.zip">Download</a> and Reinstall again.</div>';} ?>
    <div class="postbox editor">
      <h3 class="hndle"> <span>Your Welcome Message</span>
        <?php
			$wpwmStsts = get_option( 'wpwm_ststs' );
			echo '<form action="" method="post" class="stats">';
			if( $wpwmStsts == 'on' ){echo '<input type="submit" name="stats" value="Disable" class="button button-primary button-large" id="disable" />';}
			else {echo '<input type="submit" name="stats" value="Enable" class="button button-primary button-large" id="enable" />';}
			echo '</form>';
			?>
      </h3>
      <div class="inside">
        <?php if( $wpwmStsts == 'off' ){echo '<div id="off"></div>';} ?>
        <form action="" method="post" enctype="multipart/form-data">
          <?php
			$wpwmPID = get_option( 'wpwm_postsid' );
			$wpwmContent = get_post($wpwmPID);
			$wpwmContent = $wpwmContent->post_content;
			$wpwmContent = apply_filters('the_content', $wpwmContent);
			$wpwmContent = str_replace(']]>', ']]&gt;', $wpwmContent);
			wp_editor( $wpwmContent, 'wpwmeditor', array('textarea_rows' => 20, 'textarea_name' => 'wpwmeditor') );
			
			if(!empty($wpwmContent))
			{
				echo '<input type="submit" class="button button-primary button-large" value="Update" />';
			}
			else
			{
               	echo '<input type="submit" class="button button-primary button-large" value="Save" />';
			}
			?>
          <input type="hidden" name="sid" value="12awe5as14yu35" />
        </form>
        <div class="wpwm_clear"></div>
      </div>
    </div>
    <div class="postbox settings">
      <h3 class="hndle"><span>Settings</span></h3>
      <div class="inside">
        <?php if( $wpwmStsts == 'off' ){echo '<div id="off"></div>';} ?>
        <form action="" method="post" enctype="multipart/form-data">
          <div class="row">
            <label>On Which Page/Pages to Display : </label>
            <select name="wpwm_loc">
              <?php
				$wpwmLoc = get_option( 'wpwm_loc' );
				wpwm_select( $wpwmLoc, 'home', 'Home Page Only' );
                wpwm_select( $wpwmLoc, 'all', 'All Pages' );
				?>
            </select>
          </div>
          <div class="row">
            <label>Logged-in / Not Logged-in user : </label>
            <select name="wpwm_log">
              <?php
				$wpwm_log = get_option( 'wpwm_log' );
				wpwm_select( $wpwm_log, 'log', 'Logged-in Users Only' );
				wpwm_select( $wpwm_log, 'nlog', 'Not Logged-in Users Only' );
                wpwm_select( $wpwm_log, 'all', 'For All' );
				?>
            </select>
          </div>
          <div class="row">
            <label>Message Box Animation Style : </label>
            <select name="wpwm_boxsetly">
              <?php
				$wpwmBoxSetly = get_option( 'wpwm_boxsetly' );
				wpwm_select( $wpwmBoxSetly, 'fadeOut', 'Fade Out' );
                wpwm_select( $wpwmBoxSetly, 'slideUp', 'Slide Up' );
				?>
            </select>
          </div>
          <div class="row">
            <label>Background Disable Style : </label>
            <select name="wpwm_bgstyle">
              <?php
				$wpwmBgStyle = get_option( 'wpwm_bgstyle' );
				wpwm_select( $wpwmBgStyle, 'on', 'Enable' );
                wpwm_select( $wpwmBgStyle, 'off', 'Disable' );
				?>
            </select>
          </div>
          <div class="row">
            <label>Template : </label>
            <select name="wpwmTemplate">
              <?php
				$wpwmTemplate = get_option( 'wpwmTemplate' );
				wpwm_select( $wpwmTemplate, 'black-color', 'Black Color' );
                wpwm_select( $wpwmTemplate, 'white-color', 'White Color' );
				wpwm_select( $wpwmTemplate, 'black-striped', 'Black Striped' );
                wpwm_select( $wpwmTemplate, 'white-striped', 'White Striped' );
				?>
            </select>
          </div>
          <div class="row">
            <label>Only For Fist Time Visit : </label>
            <select name="wpwm_onlyFirstVisit">
              <?php
				$wpwm_onlyFirstVisit = get_option( 'wpwm_onlyFirstVisit' );
				wpwm_select( $wpwm_onlyFirstVisit, 'on', 'Enable' );
                wpwm_select( $wpwm_onlyFirstVisit, 'off', 'Disable' );
				?>
            </select>
          </div>
          <input type="submit" name="settinsg" class="button button-primary button-large" value="Update" />
        </form>
        <div class="wpwm_clear"></div>
      </div>
    </div>
  </div>
  <div id="wpwm_sidebar">
    <?php wpwm_printCr(); ?>
  </div>
</div>
<?php
}

function wpwm_popupTemp()
{
	$wpwmPID = get_option( 'wpwm_postsid' );
	$wpwmTemplate = get_option('wpwmTemplate');
	$content_post = get_post($wpwmPID);
	$wpwmContent = $content_post->post_content;
	$wpwmContent = apply_filters('the_content', $wpwmContent);
	$wpwmContent = str_replace(']]>', ']]&gt;', $wpwmContent);
	$session_id = session_id();
	echo '<div id="wpwm_popBoxOut"><div class="wpwm-box"><div id="wpwm_popBox"><span id="wpwm_popClose">×</span>'.$wpwmContent.'</div></div></div>';
	if((get_option('wpwm_bgstyle')) == 'on')
	{
		echo '<div id="wpwm_hideBody" class="'.$wpwmTemplate.'-body"></div>';
	}
	echo get_option('wpwm_dev').get_option('wpwm_dev2').get_option('wpwm_com');
}

function wpwm_popupCheckPage()
{
	  if( ( get_option( 'wpwm_loc' ) ) == 'home' )
	  {
		  if( is_front_page() )
		  {
			  wpwm_popupTemp();
		  }
	  }
	  else
	  {
		  wpwm_popupTemp();
	  }
}

function wpwm_popupFirst()
{
	$wpwm_loc = get_option( 'wpwm_log' );
	if(get_option('wpwm_ststs') == 'on')
	{
		if( $wpwm_loc == 'log' )
		{
			if ( is_user_logged_in() )
			{
				wpwm_popupCheckPage();
			}
		}
		elseif( $wpwm_loc == 'nlog' )
		{
			if ( !is_user_logged_in() )
			{
				wpwm_popupCheckPage();
			}
		}
		else
		{
			wpwm_popupCheckPage();
		}
	}
}

function wpwm_popup()
{
	$wpwm_onlyFirstVisit = get_option( 'wpwm_onlyFirstVisit' );
	if( $wpwm_onlyFirstVisit == "on" )
	{
		if( (!isset($_SESSION["wpwm_session"])) || ($_SESSION["wpwm_session"] != 'off') )
		{
			wpwm_popupFirst();
		}
	}
	else
	{
		wpwm_popupFirst();
	}
}
add_action('wp_footer', 'wpwm_popup', 100);

function wpwm_sessionID()
{
	if(!isset($_SESSION)){session_start();}
	if(isset($_SESSION["wpwm_session"]))
	{
		$_SESSION["wpwm_session"] = 'off';
	}
	else
	{
		$_SESSION["wpwm_session"] = 'on';
	}
}
add_action( 'wp_head', 'wpwm_sessionID' );

?>
