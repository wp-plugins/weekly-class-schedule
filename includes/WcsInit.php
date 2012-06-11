<?php
/**
 * @file
 * Defines the initializer class for WCS
 */

class WcsInit
{
  /**
  * Handles the version checking and update procedures.
  */
  public static function wcsVersion()
  {
    global $wpdb;
    $version = get_option( 'wcs_version' );
    
    $sql = "SHOW TABLES IN " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "wcs2%'";
    $tables = $wpdb->query( $sql );
  
    if ( $tables == 0 ) {
      // Pre 2.0
      WcsDb::createWcs2Tables();
      
      $sql = "SHOW TABLES IN " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "wcs\_%'";
      $tables = $wpdb->query( $sql );
      
      if ( $tables == 0 ) {
        // Fresh installation 
      }
      else {
        // 1.x installation
        WcsDb::migrateOldData();
        
        // TODO: Remove v1.x tables when upgrading from 2.0 to 2.x
        // WcsDb::dropOldWcsTables();
      }
      
      // Update version
      update_option( 'wcs_version', WCS_VERSION);
    } 
    
    if ( $version < WCS_VERSION) {
      // Older post 2.0 version - Run update procedures if necessary 
      
      /* 
       * [UPDATE PROCEDURES] 
       */
      
      update_option( 'wcs_version', WCS_VERSION);
    }
  }
  
  /* Runs wcs_version() when WCS is activated */
  public static function wcActivate()
  {
    self::wcs_version();
  }
  
  /* wp_head callback for injecting dynamic css to header */
  public static function wcs_get_dynamic_css()
  {
    if ( ! is_admin() )
      echo WcsStyle::getDynamicCss();
  }
  
  public static function wcsLoadPluginTextdomain()
  {
    load_plugin_textdomain( 'wcs', false, WCS_PLUGIN_NAME . '/languages' );
  }
  
  public static function loadClasses()
  {
    /* Load Application Classes */
    require_once WCS_PLUGIN_DIR . '/includes/WcsDb.php';
    require_once WCS_PLUGIN_DIR . '/includes/WcsAdmin.php';
    require_once WCS_PLUGIN_DIR . '/includes/WcsOptions.php';
    require_once WCS_PLUGIN_DIR . '/includes/WcsHtml.php';
    require_once WCS_PLUGIN_DIR . '/includes/WcsTime.php';
    require_once WCS_PLUGIN_DIR . '/includes/IWcsForm.php';
    
    /* Load Base Classes */
    require_once WCS_PLUGIN_DIR . '/includes/WcsActiveRecord.php';
    require_once WCS_PLUGIN_DIR . '/includes/WcsForm.php';
  
    /* Load Models */
    require_once WCS_PLUGIN_DIR . '/models/WcsSchedule.php';
    require_once WCS_PLUGIN_DIR . '/models/WcsClass.php';
    require_once WCS_PLUGIN_DIR . '/models/WcsInstructor.php';
    require_once WCS_PLUGIN_DIR . '/models/WcsClassroom.php';
    require_once WCS_PLUGIN_DIR . '/models/WcsStyle.php';
    require_once WCS_PLUGIN_DIR . '/models/WcsTodayClassesWidget.php';
    
    /* Load controllers */
    require_once WCS_PLUGIN_DIR . '/controllers/WcsController.php';
    require_once WCS_PLUGIN_DIR . '/controllers/WcsOutputController.php';
  }
  
  public static function addShortcode()
  {
    add_shortcode( 'wcs', array( 'WcsOutputController', 'wcsShortcodeCallback' ) );
  }
  
  /**
  * Load WCS's non-admin styles and scripts.
  */
  public static function queueStylesAndScripts()
  {
    /* Load styles */
    wp_register_style( 'wcs_stylesheet', WCS_PLUGIN_URL . '/css/wcs.css' );
    wp_enqueue_style( 'wcs_stylesheet' );
  
    /* Register jQuery */
    wp_enqueue_script( 'jquery' );
  
    /* Load WCS script */
    wp_register_script( 'wcs', WCS_PLUGIN_URL . '/js/wcs.js' );
    wp_enqueue_script( 'wcs' );
  
    /* Load hover intent */
    wp_register_script( 'wcs_hoverintent', WCS_PLUGIN_URL . '/plugins/hoverintent/jquery.hoverIntent.minified.js' );
    wp_enqueue_script( 'wcs_hoverintent' );
    
    /* Load qTip */
    wp_register_script( 'wcs_qtip', WCS_PLUGIN_URL . '/plugins/qtip/jquery.qtip-1.0.0-rc3.min.js' );
    wp_enqueue_script( 'wcs_qtip' );
  }
}