<?php
/**
 * Plugin Name: matriphe colorspam
 * Plugin URI: http://wordpress.org/plugins/matriphe-colorspam/
 * Description: Fight SPAM using simple color recognition.
 * Version: 3.0
 * Author: Muhammad Zamroni
 * Author URI: http://matriphe.com
 * License: GPL2
 */

add_action('init', 'matriphe_session_start', 1);
add_action('wp_logout', 'matriphe_session_end');
add_action('wp_login', 'matriphe_session_end');
function matriphe_session_start() 
{
    if(!session_id()) 
    {
        session_start();
    }
}

function matriphe_session_end() 
{
    session_destroy ();
}

if (!defined('MATRIPHE_COLORSPAM_OPTION_NAME') ) define('MATRIPHE_COLORSPAM_OPTION_NAME', 'matriphe_colorspam_colors');
if (!defined('MATRIPHE_COLORSPAM_VERSION') ) define('MATRIPHE_COLORSPAM_VERSION', 'matriphe_colorspam_version');

$version = '3.0';

$now = strtotime('now');
$colors = array(
	'red' => array(
		'hex' => '#ff0000',
		'name' => 'red',
	),
	'green' => array(
		'hex' => '#008000',
		'name' => 'green',
	),
	'blue' => array(
		'hex' => '#0060b6',
		'name' => 'blue',
	),
	'yellow' => array(
		'hex' => '#ffd700',
		'name' => 'yellow',
	),
	'black' => array(
		'hex' => '#000000',
		'name' => 'black',
	),
	'white' => array(
		'hex' => '#ffffff',
		'name' => 'white',
	),
	'pink' => array(
		'hex' => '#ff69b4',
		'name' => 'pink',
	),
	'orange' => array(
		'hex' => '#ffa500',
		'name' => 'orange',
	),
	'purple' => array(
		'hex' => '#800080',
		'name' => 'purple',
	),
	'grey' => array(
		'hex' => '#7f7f7f',
		'name' => 'grey',
	),
	
);

if ( get_option(MATRIPHE_COLORSPAM_VERSION) == '' || get_option(MATRIPHE_COLORSPAM_VERSION) != $version)
{
	update_option(MATRIPHE_COLORSPAM_VERSION,$version);
	shuffle($colors);
	update_option(MATRIPHE_COLORSPAM_OPTION_NAME,$colors);
}

add_action('register_form','matriphe_colorspam_form');
function matriphe_colorspam_form ()
{
	global $index;
	
	$colors = get_option(MATRIPHE_COLORSPAM_OPTION_NAME);
	
	$index = $_SESSION['matriphe_color_index'];
	$_SESSION['matriphe_color_index'] = $index;
	
	$color_name = ( isset( $_POST['color_name'] ) ) ? $_POST['color_name']: '';
	$color_index = ( isset( $_POST['color_index'] ) ) ? $_POST['color_index']: '';
	
	$selections = array();
	foreach ($colors as $c) { array_push($selections, '<span class="matriphe_colors" id="matriphe_color_'.$c['name'].'" style="background-color:'.$c['hex'].'">'.$c['name'].'</span>'); }
	sort($selections);
	?>
	<p id="colorspamjs"></p>
	<style>
	.matriphe_colors { display: inline-block; margin: 1px 0; padding: 2px 3px; font-weight: bold; color: #fff; border: #ddd 1px solid; }
	#matriphe_color_white, #matriphe_color_yellow { color: #000; }
	</style>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var html = '<label for="color_name"><?php _e('What Color is it?','matriphe_colorspam') ?></label><div style="width: 100%; height: 50px; background-image:url(<?php echo add_query_arg('matriphe','colorspam',site_url()) ?>); background-repeat: repeat; border: #ddd 1px solid; margin-bottom: 5px;"></div><input type="text" name="color_name" id="color_name" class="input" value="" size="25" /><input type="hidden" name="color_index" value="<?php echo $index ?>" /></p><p style="margin-bottom: 10px; border-bottom: #ccc 1px solid; padding-bottom: 10px;"><strong>Selection:</strong><br /><?php echo implode(' ', $selections); ?>';
		$('#colorspamjs').append(html);
	});
	</script>
	<?php
}

add_filter('registration_errors', 'matriphe_colorspam_errors', 10, 3);
function matriphe_colorspam_errors ($errors, $sanitized_user_login, $user_email)
{
	$colors = get_option(MATRIPHE_COLORSPAM_OPTION_NAME);
	
	$color_name = $_POST['color_name'];
	$color_name = strtolower(trim($color_name));
	
	$color_index = $_POST['color_index'];
	$index = $color_index;
	
	$answer_color_name = $colors[$index]['name'];
	$answer_color_name = strtolower(trim($answer_color_name));
	
	if (empty($color_name))
		$errors->add( 'color_name_error', __('<strong>ERROR</strong>: You must fill color name.','matriphe_colorspam') );
	elseif ( !($color_name == $answer_color_name) )
		$errors->add( 'color_name_error', __('<strong>ERROR</strong>: The color name is not right.','matriphe_colorspam') );
	
	return $errors;
}

add_action('user_register', 'matriphe_colorspam_register');
function matriphe_colorspam_register ($user_id)
{
	unset($_SESSION['matriphe_color_index']);
	return true;
}

add_action( 'login_enqueue_scripts', 'matriphe_colorspam_js', 1 );
function matriphe_colorspam_js()
{
	wp_enqueue_script( 'jquery' );
}

add_action('parse_request', 'matriphe_colorspam_parse_request');
function matriphe_colorspam_parse_request($wp) 
{
    $colors = get_option(MATRIPHE_COLORSPAM_OPTION_NAME);
    
    $index = rand(0,count($colors)-1);
    $_SESSION['matriphe_color_index'] = $index;
    
    if (array_key_exists('matriphe', $wp->query_vars) && $wp->query_vars['matriphe'] == 'colorspam') 
    {
		$hex = $colors[$index]['hex'];
		$hex = str_replace('#', '', $hex);
		
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2)); 
		
		header("Content-type: image/png");
		header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
		header('Pragma: no-cache'); // HTTP 1.0.
		header('Expires: 0'); 
		$im = imagecreatetruecolor(1,1);
		$color = imagecolorallocate($im, $r, $g, $b);
		imagefilledrectangle($im, 0, 0, 1, 1, $color);
		imagepng($im);
		imagedestroy($im);
		exit();
    }
}

add_filter('query_vars', 'matriphe_colorspam_query_vars');
function matriphe_colorspam_query_vars($vars) 
{
    $vars[] = 'matriphe';
    return $vars;
}
