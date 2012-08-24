<?php
/**
 * @file
 * HTML Helper class
 */

abstract class WcsHtml
{
  /**
   * Creates a simple HTML select list
   * 
   * @param array $items
   * @param array $attributes
   * 	Define html attributes such as class, id, etc... (e.g. array( 'class' => 'class-select' ))
   * @param boolean $is_assoc
   * 	If the array is not associative, the item value will be used as the option value.
   * @param string $default
   * 	Name of default option
   */
  public static function generateSelectList( $items, $attributes = array(), $is_assoc = FALSE, $default = NULL  )
  {
    $attrs = '';
    if ( ! empty( $attributes ) ) {
      foreach ( $attributes as $key => $value ) {
        $attrs .= $key . '="' . $value . '" '; 
      }
    }
    
    $output = "<select $attrs>";
    
    if ( ! is_array( $items ) || empty ( $items ) ) {
      $output .= '<option value="_none">- None -</option>';
    }
    else {
      foreach ( $items as $key => $value ) {
        if ( !is_array( $value ) ) {
          if ( $is_assoc == FALSE ) {
            $key = $value;
            $value = stripslashes( $value );
          }
        }
        else {
          $key = $value[key( $value )];
          next( $value );
          $value = stripslashes( $value[key( $value )] );
        }
        if ( $key == $default || $value == $default )
          $output .= "<option selected='selected' value='$key'>$value</option>";
        else
          $output .= "<option value='$key'>$value</option>";
      }
    }
    
    $output .= '</select>';
    
    return $output;
  }
  
  /**
  * Displays a formatted WordPress message
  *
  * @param string or array of strings $message
  * @param string $type
  *	Options include 'error' and 'updated'
  */
  public static function show_wp_message( $message, $type = 'error' ) {
    ?>
    	<div id="message" class="<?php echo $type;?>">
    			<?php 
    			  if ( is_array( $message ) ) {
    			    echo '<ul>';
    			    foreach ( $message as $string ) {
    			      echo '<li>' . $string . '</li>';
    			    }
    			    echo '</ul>';
    			  }
    			  else {
    			    echo '<p>' . $message . '</p>';
    			  }
    			?>
    	</div>
    <?php
    }
}