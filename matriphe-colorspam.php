<?php
/**
 * Plugin Name: matriphe colorspam
 * Plugin URI: http://wordpress.org/plugins/matriphe-colorspam/
 * Description: Fight SPAM using simple color recognition.
 * Version: 2.0
 * Author: Muhammad Zamroni
 * Author URI: http://matriphe.com
 * License: GPL2
 */


if (!defined('MATRIPHE_COLORSPAM_OPTION_NAME') ) define('MATRIPHE_COLORSPAM_OPTION_NAME', 'matriphe_colorspam_colors');

add_action('register_form','matriphe_colorspam_form');

$now = strtotime('now');
$colors = array(
	'red' => array(
		'hex' => '#ff0000',
		'hash' => md5('red'.$now),
		'name' => 'red',
	),
	'green' => array(
		'hex' => '#008000',
		'hash' => md5('green'.$now),
		'name' => 'green',
	),
	'blue' => array(
		'hex' => '#0060b6',
		'hash' => md5('blue'.$now),
		'name' => 'blue',
	),
	'yellow' => array(
		'hex' => '#ffd700',
		'hash' => md5('yellow'.$now),
		'name' => 'yellow',
	),
	'black' => array(
		'hex' => '#000000',
		'hash' => md5('black'.$now),
		'name' => 'black',
	),
	'white' => array(
		'hex' => '#ffffff',
		'hash' => md5('white'.$now),
		'name' => 'white',
	),
	'pink' => array(
		'hex' => '#ff69b4',
		'hash' => md5('pink'.$now),
		'name' => 'pink',
	),
	/*
	'orange' => array(
		'hex' => '#ffa500',
		'hash' => md5('orange'.$now),
		'name' => 'orange',
	),
	*/
	'purple' => array(
		'hex' => '#800080',
		'hash' => md5('purple'.$now),
		'name' => 'purple',
	),
	/*
	'grey' => array(
		'hex' => '#7f7f7f',
		'hash' => md5('grey'.$now),
		'name' => 'grey',
	),
	*/
);
shuffle($colors);
add_option(MATRIPHE_COLORSPAM_OPTION_NAME, $colors);

function matriphe_colorspam_form ()
{
	$colors = get_option(MATRIPHE_COLORSPAM_OPTION_NAME);
	shuffle($colors);
	update_option(MATRIPHE_COLORSPAM_OPTION_NAME,$colors);
	
	$index = rand(0,count($colors)-1);
	
	$color_name = ( isset( $_POST['color_name'] ) ) ? $_POST['color_name']: '';
	$color_id = ( isset( $_POST['color_id'] ) ) ? $_POST['color_id']: '';
	$color_hash = ( isset( $_POST['color_hash'] ) ) ? $_POST['color_hash']: '';
	
	$colorhex = adjustBrightness($colors[$index]['hex'], rand(0,10));
	
	$selections = array();
	foreach ($colors as $c) { array_push($selections, $c['name']); }
	sort($selections);
	
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var html = '<label for="color_name"><?php _e('What Color is it?','matriphe_colorspam') ?></label><div style="width: 100%; height: 50px; background-color:<?php echo $colorhex; ?>; border-color: #ddd; margin-bottom: 5px;"></div><input type="text" name="color_name" id="color_name" class="input" value="" size="25" /><input type="hidden" name="color_id" value="<?php echo $index; ?>" /><input type="hidden" name="color_hash" value="<?php echo $colors[$index]['hash'] ?>" />';
		$('#colorspamjs').append(html);
	});
	</script>
	<p id="colorspamjs"></p>
	<p style="margin-bottom: 10px; border-bottom: rgb(221,221,221) 1px solid; padding-bottom: 10px;">Selection: <?php echo implode(', ', $selections); ?></p>
	<?php
}

add_filter('registration_errors', 'matriphe_colorspam_errors', 10, 3);
function matriphe_colorspam_errors ($errors, $sanitized_user_login, $user_email)
{
	$colors = get_option(MATRIPHE_COLORSPAM_OPTION_NAME);
	
	$color_name = $_POST['color_name'];
	$color_id = $_POST['color_id'];
	$color_hash = $_POST['color_hash'];
	
	$hash = $colors[$color_id]['hash'];
	$name = $colors[$color_id]['name'];
	
	if (empty($color_name))
		$errors->add( 'color_name_error', __('<strong>ERROR</strong>: You must fill color name.','matriphe_colorspam') );
	elseif ( !(strtolower($color_name) == $name && $hash == $color_hash) )
		$errors->add( 'color_name_error', __('<strong>ERROR</strong>: The color name is not right.','matriphe_colorspam') );
	
	return $errors;
}

add_action('user_register', 'matriphe_colorspam_register');
function matriphe_colorspam_register ($user_id)
{
	return true;
}

function matriphe_colorspam_js()
{
	wp_enqueue_script( 'jquery' );
}
add_action( 'login_enqueue_scripts', 'matriphe_colorspam_js', 1 );

function adjustBrightness($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Format the hex color string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }

    // Get decimal values
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));

    // Adjust number of steps and keep it inside 0 to 255
    $r = max(0,min(255,$r + $steps));
    $g = max(0,min(255,$g + $steps));  
    $b = max(0,min(255,$b + $steps));

    $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
    $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
    $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

    return '#'.$r_hex.$g_hex.$b_hex;
}