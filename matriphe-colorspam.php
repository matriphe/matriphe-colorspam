<?php
/**
 * Plugin Name: matriphe!colorspam
 * Plugin URI: http://matriphe.com
 * Description: Fight SPAM using simple color recognition.
 * Version: 1.0
 * Author: Muhammad Zamroni
 * Author URI: http://matriphe.com
 * License: GPL2
 */


//1. Add a new form element...
add_action('register_form','matriphe_colorspam_form');

$colors = array('red','green','blue','yellow','black','white','pink','orange','purple', 'grey');
$styles = array(
	'red' => '#ff0000',
	'green' => '#008000',
	'blue' => '#0060b6',
	'yellow' => '#ffd700',
	'black' => '#000000',
	'white' => '#ffffff',
	'pink' => '#ff69b4',
	'orange' => '#ffa500',
	'purple' => '#800080',
	'grey' => '#7f7f7f',
);

function matriphe_colorspam_form ()
{
	global $colors, $styles;
	
	$color = rand(0,count($colors)-1);
	
	$color_name = ( isset( $_POST['color_name'] ) ) ? $_POST['color_name']: '';
	$color_id = ( isset( $_POST['color_id'] ) ) ? $_POST['color_id']: '';
	
	?>
	<p>
		<label for="color_name"><?php _e('What Color is it?','matriphe_colorspam') ?></label>
		<div style="width: 100%; height: 50px; background-color:<?php echo $styles[$colors[$color]]; ?>; border-color: rgb(221, 221, 221); box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.07); margin-bottom: 5px;"></div>
		<input type="text" name="color_name" id="color_name" class="input" value="<?php echo esc_attr(stripslashes($first_name)); ?>" size="25" />
		<input type="hidden" name="color_id" value="<?php echo $color; ?>" />
	</p>
	<p style="margin-bottom: 10px; border-bottom: rgb(221,221,221) 1px solid; padding-bottom: 10px;">Selection: <?php echo implode(', ', $colors); ?></p>
	<?php
}

//2. Add validation. In this case, we make sure first_name is required.
add_filter('registration_errors', 'matriphe_colorspam_errors', 10, 3);
function matriphe_colorspam_errors ($errors, $sanitized_user_login, $user_email)
{
	global $colors, $styles;
	
	if ( empty( $_POST['color_name'] ) )
	    $errors->add( 'color_name_error', __('<strong>ERROR</strong>: You must fill color name.','matriphe_colorspam') );
	
	else if ( strtolower($_POST['color_name']) != strtolower( $colors[$_POST['color_id']] )  )
		$errors->add( 'color_name_error', __('<strong>ERROR</strong>: The color name is not right.','matriphe_colorspam') );
	
	return $errors;
}

//3. Finally, save our extra registration user meta.
add_action('user_register', 'matriphe_colorspam_register');
function matriphe_colorspam_register ($user_id)
{
	if ( strtolower($_POST['color_name']) == strtolower( $colors[$_POST['color_id']] )  ) return true;
}