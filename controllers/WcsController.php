<?php
/**
 * @file
 * Defines the base WcsController class.
 */
class WcsController
{
  protected $_base_name;
  
  function __construct( $base_name )
  {
    $this->_base_name = $base_name;
  }
  
  /**
   * Renders a view
   * 
   * @param string $view
   * @param array $data
   */
  public function render( $view, $data = array(), $type = 'item' )
  {
    extract( $data );
    $model = 'Wcs' . ucwords( $this->_base_name );
    
    require_once WCS_PLUGIN_DIR . '/views/' . $view . 'View.php';
    
    if ( isset( $_GET['wcsop'] ) && isset( $_GET['wcsid'] ) ) {
      $op = $_GET['wcsop'];
      $id = ( is_numeric( $_GET['wcsid'] ) ) ? $_GET['wcsid'] : NULL;
      
      if ( $id !== NULL ) {
        if ( $type == 'item' )
          $this->renderItemEditForm( $model, $id );
        elseif ( $type == 'schedule' ) 
          $this->renderScheduleEditForm( $model, $id );
      } 
      else {
        die( __( 'Bad request' ) );
      }
    } 
    else {
      if ( $type == 'item' )
        $this->renderItemForm( $model, $data );
      elseif ( $type == 'schedule' )  
        $this->renderScheduleForm( $model, $data );
    }
  }
  
  /**
   * Renders a form
   * 
   * @param string $model
   * @param array $data
   */
  public function renderItemForm( $model, $data = array() )
  {
    /* Process form */
    if ( isset( $_POST['add_item'] ) ) {
      $op = 'add_item';
    } 
    elseif ( isset( $_POST['delete_items'] ) ){
      $op = 'delete_items';
    } 
    
    if ( isset( $op ) ) {
      WcsForm::processForm( 'WcsItem', $model, $op );
      unset( $_POST );
      
      $instance = new $model();
      $data['items'] = $instance->getCols( array( 
        'id',
      	$this->_base_name . '_name', 
      	$this->_base_name . '_description' 
      ) );
      self::renderItemForm( $model, $data );
    }
    
    /* Create view vars */
    $data['edit_url'] = WcsApp::getBaseUrl()  . '&wcsop=edit';
    
    extract( $data );
    
    echo "<form action='' method='post' id='$model' name='$model'>";
    
    require_once WCS_PLUGIN_DIR . '/views/WcsItemForm.php';
    
    wp_nonce_field( $model ); 
    echo '</form>';
  }
  
  /**
   * Renders the WCS Item edit form
   * 
   * @param string $model
   * @param int $id
   * 	The item id
   */
  public function renderItemEditForm( $model, $id )
  {
    /* Process form */
    if ( isset( $_POST['save_item'] ) ) {
      $op = 'save_item';
    }
    
    if ( isset( $op ) ) {
      WcsForm::processForm( 'WcsItem', $model, $op, $id );
      unset( $_POST );
    }
    
    /* Create view vars */
    $instance = new $model();
    $item = $instance->getById( $id );
    $attr_name = $this->_base_name . '_name';
    $attr_desc = $this->_base_name . '_description';
    $item_name = stripslashes( $item->$attr_name );
    $item_description = stripslashes( $item->$attr_desc ); 
    
    echo "<form action='' method='post' id='$model' name='$model'>";
    
    require_once WCS_PLUGIN_DIR . '/views/WcsItemEditForm.php';
    
    wp_nonce_field( $model ); 
    echo '</form>';
  }
  
  /**
   * Returns the schedule edit form.
   * 
   * @param string $model
   * @param array $data
   */
  public function renderScheduleForm( $model, $data = array() )
  {
    /* Process form */
    if ( isset( $_POST['add_item'] ) ) {
      $op = 'add_item';
    }
    elseif ( isset( $_POST['delete_items'] ) ){
      $op = 'delete_items';
    }
    
    if ( isset( $op ) ) {
      WcsForm::processForm( 'WcsSchedule', $model, $op );
      $data['post'] = $_POST;
      unset( $_POST );
    
      self::renderScheduleForm( $model, $data );
    }
    
    /* Create view vars */
    $data['edit_url'] = WcsApp::getBaseUrl()  . '&wcsop=edit';
    $data['classes'] = WcsClass::model()->getCol( 'class_name' );
    $data['instructors'] = WcsInstructor::model()->getCol( 'instructor_name' );
    $data['classrooms'] = WcsClassroom::model()->getCol( 'classroom_name' );
    $data['weekdays_array'] = WcsSchedule::model()->getWeekDaysArray( TRUE );
    $data['weekdays'] = ( is_array( WcsSchedule::model()->getDbSortedWeekdays() ) ? WcsSchedule::model()->getDbSortedWeekdays() : array() );
    $data['visibility'] = WcsSchedule::model()->getVisibilityOptions();
    
    $data['items'] = WcsSchedule::model()->getAllRecords( array( 'start_hour', 'ASC' ) );
  
    extract( $data );
  
    /* Generate form */
    echo "<form action='' method='post' id='$model' name='$model'>";
  
    require_once WCS_PLUGIN_DIR . '/views/WcsScheduleForm.php';
      
    wp_nonce_field( $model );
    echo '</form>';
  }
  
  /**
   * Renders the WCS Schedule entry edit form
   *
   * @param string $model
   * @param int $id
   * 	The item id
   */
  public function renderScheduleEditForm( $model, $id )
  {
    /* Process form */
    if ( isset( $_POST['save_item'] ) ) {
      $op = 'save_item';
    }
    
    if ( isset( $op ) ) {
      WcsForm::processForm( 'WcsSchedule', $model, $op, $id );
      unset( $_POST );
    }
    
    /* Create view vars */
    $data['classes'] = WcsClass::model()->getCol( 'class_name' );
    $data['instructors'] = WcsInstructor::model()->getCol( 'instructor_name' );
    $data['classrooms'] = WcsClassroom::model()->getCol( 'classroom_name' );
    $data['weekdays_array'] = WcsSchedule::model()->getWeekDaysArray( TRUE );
    $data['weekdays'] = array_unique( WcsSchedule::model()->getCol( 'weekday' ) );
    $data['visibility'] = WcsSchedule::model()->getVisibilityOptions();
    
    $instance = new $model();
    $data['entry'] = $instance->getById( $id );
    
    extract( $data );
    
    echo "<form action='' method='post' id='$model' name='$model'>";
        
    require_once WCS_PLUGIN_DIR . '/views/WcsScheduleEditForm.php';
        
    wp_nonce_field( $model );
    echo '</form>';
  }
}