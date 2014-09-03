<?php
/**
Plugin Name: Nivo Slider
Plugin URI: http://ww.j11.ca/
Description: A simple nivo slider.
Version: 1.0.0
Author: J11 IT Services Inc.
Author URI: http://ww.j11.ca/
License: GPLv2 or later
Text Domain: tk
*/
add_action('init', 'create_post_type_slides');
function create_post_type_slides() {
	$args = array(
		'labels' => array('name' => 'Slides', 'singular_name' =>'Slide'),
		'public' => true,
		'has_archive' => true,
		'supports' => array('title', 'thumbnail')
	);
	register_post_type('slides', $args);
}
// Load Nivo Slider only on Homepage
if(!is_admin()) {
	wp_enqueue_style('nivo-slider', plugins_url('/css/nivo/default.css', __FILE__));
	wp_enqueue_style('nivo-slider-theme', plugins_url('/css/nivo-slider.css', __FILE__));
	wp_enqueue_script('nivo-slider-js', plugins_url('/js/jquery.nivo.slider.pack.js', __FILE__), array('jquery'));
}
add_shortcode('tk-nivo', 'tk_nivo_display');
function tk_nivo_display() { 
	$slides = new WP_Query(array('post_type' => 'slides', 'posts_per_page' => get_option('slide_count', 3)));
	if($slides->have_posts()): ?>
		<div id="slider" class="nivoSlider">
		<?php while($slides->have_posts()): $slides->the_post(); ?>
			<?php if(has_post_thumbnail()): ?>
			<?php
				$post_thumbnail_src = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), array(650, 340), false, '');
				$img_src = $post_thumbnail_src[0];
			?>
				<img src="<?php echo plugins_url('/inc/timthumb.php', __FILE__); ?>?src=<?php echo $img_src;?>&w=650&h=340&zc=1q=100" />
			<?php endif; ?>
	    <?php endwhile; ?>
	    </div>
	<?php endif; wp_reset_postdata(); ?>
<?php }

function tk_nivo_init_script() {
	echo "
		<script type='text/javascript'>
			jQuery(window).load(function() {
		        jQuery('#slider').nivoSlider({";
	if(get_option('slide_nav_bar') == 'no') {
		echo "controlNav: false, ";
	} else {
		echo "directionNav: true, ";
	}
	if(get_option('slide_control_nav') == 'no') {
		echo "directionNav: false";
	} else {
		echo "directionNav: true";
	}
	echo "	    });
		    });
		</script>
	";
}
add_action('wp_head', 'tk_nivo_init_script');

/**
 Theme Settings
*/
add_action('admin_menu', 'tk_add_settings_menu');
function tk_add_settings_menu(){
	add_submenu_page('edit.php?post_type=slides', 'Slider Settings', 'Settings', 'activate_plugins', 'tk-nivo-settings', 'tk_nivo_settings_page');
	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}

function register_mysettings() {
	//register our settings
	register_setting( 'nivo-settings-group', 'slide_count' );
	register_setting( 'nivo-settings-group', 'slide_nav_bar' );
	register_setting( 'nivo-settings-group', 'slide_control_nav' );
}

function tk_nivo_settings_page() {
?>
<div class="wrap">
<h2>Nivo Slider Settings</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'nivo-settings-group' ); ?>
    <?php do_settings_sections( 'nivo-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Slide Counts</th>
        <td><input type="number" name="slide_count" value="<?php echo get_option('slide_count'); ?>" />
        </td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Slider Navigation Bar</th>
        <td>
        	<select name="slide_nav_bar">
        		<option value="yes" <?php echo (get_option('slide_nav_bar')  == 'yes') ? "selected" : ""; ?>>Yes</option>
        		<option value="no" <?php echo (get_option('slide_nav_bar')  == 'no') ? "selected" : ""; ?>>No</option>
        	</select>
        	
       	</td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Slider Control Bar</th>
        <td>
        	<select name="slide_control_nav">
        		<option value="yes" <?php echo (get_option('slide_control_nav')  == 'yes') ? "selected" : ""; ?>>Yes</option>
        		<option value="no" <?php echo (get_option('slide_control_nav')  == 'no') ? "selected" : ""; ?>>No</option>
        	</select>
        	
       	</td>
        </tr>
        <tr>
        	<th>Shortcode</th>
        	<td><input type="text" readonly value="[tk-nivo]" onfocus="this.select();" /></td>
        </tr>
    </table>
    <?php submit_button(); ?>

</form>
</div>
<?php }