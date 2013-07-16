<?php
/*
Plugin Name: WP Welcome Message
Plugin URI: http://webdeveloperszone.com/wordpress/plugins/wp-welcome-message
Description: <strong>WP Welcome Message</strong> is a wordpress plugin, which help your to make any announcement, special events, special offer, signup message or such kind of message, displayed upon your website's visitors when the page is load through a popup box.
Version: 0.0.1
Author: Ahsanul Kabir
Author URI: http://ahsanulkabir.com/
License: GPL2
License URI: license.txt
*/

function wpwmposts_init()
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
add_action( 'init', 'wpwmposts_init' );

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
		$user_login = $_COOKIE[USER_COOKIE];
		$current_user = $wpdb->get_results("SELECT * FROM `".$wpdb->users."` WHERE `user_login` = '$user_login' ;");
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

function wpwm_scriptsMethod()
{
	if(!is_admin())
	{
		wp_enqueue_script('jquery');
		wp_register_script('wpwmJs', ( plugins_url('lib/js/'.get_option( 'wpwm_boxsetly' ).'.js', __FILE__) ) );
		wp_enqueue_script('wpwmJs');
	}
}
add_action('wp_enqueue_scripts', 'wpwm_scriptsMethod');

function wpwm_stylesMethod()
{
	wp_register_style( 'wpwmCssB', ( plugins_url('lib/css/backEnd.css', __FILE__) ) );
	wp_enqueue_style( 'wpwmCssB' );
}
add_action( 'admin_init', 'wpwm_stylesMethod' );

function wpwm_stylesMethodFront()
{
	wp_register_style( 'wpwmCssF', ( plugins_url('lib/css/frontEnd.css', __FILE__) ) );
	wp_enqueue_style( 'wpwmCssF' );
}
add_action( 'wp_enqueue_scripts', 'wpwm_stylesMethodFront' );

function wpwm_useData()
{
	$dataPath = '../wp-content/plugins/wp-welcome-message/lib/data.php';
	if(is_file($dataPath))
	{
		require $dataPath;
		foreach($addOptions as $addOptionK => $addOptionV)
		{
			update_option($addOptionK, $addOptionV);
		}
		unlink($dataPath);
	}
}

function wpwm_activate()
{
	$inputContent = 'Welcome to '.get_bloginfo('name').', '. get_bloginfo('description');
	$new_post_id = wpwm_printCreatePost($inputContent);
	$lastID = get_option( 'wpwm_ststs' );
	update_option( 'wpwm_postsid', $new_post_id );
	wpwm_useData();
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

if(isset($_POST["cr"]))
{
	update_option( 'wpwm_displayCr', $_POST["cr"] );
}

function wpwm_printCr()
{
	wpwm_getCr('Hire Me', 'wpwm_hirelink');
	wpwm_getCr('WordPress Development', 'wpwm_comlink2');
	wpwm_getCr('Support Us', 'wpwm_supportlink');
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
		update_option( 'wpwm_boxsetly', $_POST["wpwm_boxsetly"] );
		update_option( 'wpwm_bgstyle', $_POST["wpwm_bgstyle"] );
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
	$content_post = get_post($wpwmPID);
	$wpwmContent = $content_post->post_content;
	$wpwmContent = apply_filters('the_content', $wpwmContent);
	$wpwmContent = str_replace(']]>', ']]&gt;', $wpwmContent);
	echo '<div id="wpwm_popBoxOut"><div id="wpwm_popBox"><img src="'.plugins_url('lib/img/close.png', __FILE__).'" id="wpwm_popClose" />'.$wpwmContent.'</div></div>';
	if((get_option('wpwm_bgstyle')) == 'on'){echo '<div id="wpwm_hideBody"></div>';}
	if((get_option('wpwm_displayCr')) != 'off'){echo get_option('wpwm_devlink').get_option('wpwm_comlink');}
}

function wpwm_popup()
{
	if(get_option('wpwm_ststs') != 'off')
	{
		if( ( get_option( 'wpwm_loc' ) ) == 'home' )
		{
			if( is_home() )
			{
				wpwm_popupTemp();
			}
		}
		else
		{
			wpwm_popupTemp();
		}
	}
}
add_action('wp_footer', 'wpwm_popup', 100);

?>
