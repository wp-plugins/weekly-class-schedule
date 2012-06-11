<?php
/**
 * @file
 * WCS Forms base class.
 */
class WcsForm
{
  protected $_form_id;
  protected $_base_name;
  protected $_table_name;
  protected $_class_name;
  
  /**
   * Set instance variables
   */
  function __construct( $form_id )
  {
    global $wpdb;
  
    $this->_form_id = $form_id;
    $this->_base_name = strtolower( str_replace( 'Wcs', '', $form_id ) );
    $this->_table_name = $wpdb->prefix . 'wcs2_' . $this->_base_name;
    $this->_class_name = 'Wcs' . ucwords( $this->_base_name );
  }
  
  /**
   * Begin processing of form.
   * 
   * @param string $model
   * 	Name of form model to use
   * @param string $form_id
   * @param string $op
   * 	Form operation ('submit', 'delete', etc...)
   */
  public static function processForm( $model, $form_id, $op, $id = NULL )
  {
    $class = $model . 'Form';
    require_once WCS_PLUGIN_DIR . '/models/' . $class . '.php';
    $form = new $class( $form_id );

    self::validateNonce( $form_id );
    
    $form->begin( $op, $id );
  }
  
  protected static function setFormError( $form_errors = array() )
  {
    if ( ! empty( $form_errors ) ) {
      WcsHtml::show_wp_message( $form_errors );
    }
  }
  
  /**
   * Nonce validation (CSRF)
   * 
   * @param string $form_id
   * 	Name of nonce (when created)
   */
  private static function validateNonce( $form_id )
  {
    $nonce=$_REQUEST['_wpnonce'];
    if ( ! wp_verify_nonce( $nonce, $form_id ) ) die( 'Security check' ); 
  }
}