<?php
define( 'DC_VERSION', '1.0' );

define( 'DC_VERSION_REQUIRED_WP_VERSION', '3.8' );

if ( ! defined( 'DC_PLUGIN_BASENAME' ) )
	define( 'DC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'DC_PLUGIN_NAME' ) )
	define( 'DC_PLUGIN_NAME', trim( dirname( DC_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'DC_PLUGIN_DIR' ) )
	define( 'DC_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'DC_PLUGIN_URL' ) )
	define( 'DC_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
if ( ! defined( 'DC_PLUGIN_UPLOAD_PATH' ) ){
	$upload_dir = wp_upload_dir();

	define( 'DC_PLUGIN_UPLOAD_PATH', $upload_dir['basedir']."/data-cube/" );
}

if ( ! defined( 'DC_LOAD_JS' ) )
	define( 'DC_LOAD_JS', true );

if ( ! defined( 'DC_LOAD_CSS' ) )
	define( 'DC_LOAD_CSS', true );
	
add_action('admin_init', 'admin_load_scripts'); 
function admin_load_scripts() 
{
	$js_file = plugins_url( 'js/admin-scripts.js', __FILE__ ); 
	wp_enqueue_script('data-cube-admin-script', $js_file, array('jquery')); 

	$js_file = plugins_url( 'js/chosen/chosen.jquery.js', __FILE__ ); 
	wp_enqueue_script('data-cube-chosen-script', $js_file, array('jquery'), '1.1'); 	
	
	$js_file = plugins_url( 'js/toastr/toastr.min.js', __FILE__ ); 
	wp_enqueue_script('data-cube-toastr-script', $js_file, array('jquery'), '1.1'); 	
	
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script('jquery-ui-core');
	
	$js_file = plugins_url( 'js/ketchup/jquery.ketchup.all.min.js', __FILE__ ); 
	wp_enqueue_script('data-cube-jquery-ketchup', $js_file, array('jquery'), '1.1'); 	
}

add_action('admin_init', 'admin_load_style'); 
function admin_load_style() 
{
	$css_file = plugins_url( 'css/admin-style.css', __FILE__ ); 
	wp_enqueue_style('data-cube-admin-style', $css_file, array()); 
	
	$css_file = plugins_url( 'js/chosen/chosen.css', __FILE__ ); 
	wp_enqueue_style('data-cube-chosen-style', $css_file, array(), '1.1'); 
	
	$css_file = plugins_url( 'js/toastr/toastr.min.css', __FILE__ ); 
	wp_enqueue_style('data-cube-toastr-style', $css_file, array(), '1.1'); 
	
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
	
	$css_file = plugins_url( 'js/ketchup/jquery.ketchup.css', __FILE__ ); 
	wp_enqueue_style('data-cube-ketchup-style', $css_file, array(), '1.1'); 
} 

function dc_plugin_url( $path = '' ) 
{
	$url = untrailingslashit( DC_PLUGIN_URL );

	if ( ! empty( $path ) && is_string( $path ) && false === strpos( $path, '..' ) )
		$url .= '/' . ltrim( $path, '/' );
	
	return $url;
}

/**
 * Admin menu settings
 */
function dc_admin_menu()
{
    add_menu_page('Data Cube', 'Data Cube', 'manage_options', 'wp-data-cube', 'wp_data_cube', WP_CONTENT_URL . "/plugins/data-cube/images/icon.png");
}

add_action("admin_menu", "dc_admin_menu");

function wp_data_cube_shortcode( $atts, $content = null)	{
	global $controller;
	$controller->dc_shortcode($atts, $content = null);
	
}
add_shortcode('wp-data-cube', 'wp_data_cube_shortcode');

add_action('admin_head','dc_content_url');
function dc_content_url() {
?>
	<script type="text/javascript">
		var contenturl = '<?php echo WP_CONTENT_URL; ?>';
	</script>
<?php
}
?>