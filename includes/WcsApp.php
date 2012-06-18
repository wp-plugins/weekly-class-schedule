<?php
/**
 * Main WCS application class
 */

abstract class WcsApp
{
  /**
   * Initialize application
   */
  public static function init()
  {
    require_once WCS_PLUGIN_DIR . '/includes/WcsInit.php';
    
    /* Check version and run update procedures as necessary */
    add_action( 'init', array( 'WcsInit', 'wcsVersion' ) );
    
    /* Register activation hook */
    register_activation_hook( __FILE__, array( 'WcsInit', 'wcsActivate' ) );
    
    /* I18n */
    add_action( 'init', array( 'WcsInit', 'wcsLoadPluginTextdomain' ) );
    
    /* Load classes */
    WcsInit::loadClasses();
    
    /* Launch admin interface */
    if ( is_admin() )
      WcsAdmin::init();
    else
      add_action('wp_enqueue_scripts', array( 'WcsApp', 'enqueue_front_end_scripts' ) );
    
    /* Add shortcode */
    WcsInit::addShortcode();
    
    /* Call wp_head hook for injecting our custom CSS */
    add_action('wp_head', array( 'WcsInit', 'wcs_get_dynamic_css' ) );
    
    /* Register widgets */
    add_action( 'widgets_init', create_function( '', 'register_widget( "WcsTodayClassesWidget" );' ) );
  }
  
  public static function enqueue_front_end_scripts()
  {
    WcsInit::queueStylesAndScripts();
  }
  
  /**
   * Returns the current URL.
   */
  public static function getBaseUrl()
  {
    $server = esc_url( $_SERVER[SERVER_NAME] );
    $uri = esc_url( $_SERVER[REQUEST_URI] );
    
    $url = $server . $uri; 
    
    return $url;
  }
}