<?php
/**
 * @file
 * Defines the WCS Today's Classes widget.
 */

require_once WCS_PLUGIN_DIR . '/models/WcsSchedule.php';
require_once WCS_PLUGIN_DIR . '/models/WcsClassroom.php';

class WcsTodayClassesWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'wcs_today_classes_widget', // Base ID
			__( "WCS Today's Classes" ), // Name
			array( 'description' => __( "Displays a list of today's classes" ) ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	  global $wp_locale;
	  extract( $args );
	  
	  
		$title = apply_filters( 'widget_title', $instance['title'] );
		$max_classes = apply_filters( 'widget_title', $instance['max_classes'] );
		$classroom = apply_filters( 'widget_title', $instance['classroom'] );
		$today = date( 'w', strtotime( 'now' ) );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		
		/* Content */
		if ( isset( $classroom ) && $classroom != 'all' ) {
		  $classroom = WcsClassroom::model()->getByAttribute( 'classroom_name', $classroom );
		  $classes = WcsSchedule::model()->getByAttributes( array( 'weekday' => $today, 'classroom_id' => $classroom->id ), array( 'col' => 'start_hour', 'order' => 'ASC' ) );
		}
		else {
		  $classes = WcsSchedule::model()->getByAttributes( array( 'weekday' => $today ), array( 'col' => 'start_hour', 'order' => 'ASC' ) );
		}
		
		if ( isset( $max_classes ) && is_numeric( $max_classes ) && $classes != NULL )
		  $classes = array_slice( $classes, 0, $max_classes );
		
		if ( ! empty( $classes ) ) {
		  echo '<ul>';
		  
		  foreach ( $classes as $class ) {
		    $class_name = $class->getClassName();
		    $start_hour = $class->getStartHour();
		    echo "<li>$start_hour - $class_name</li>";
		  }
		  
		  echo '</ul>';
		}
		else {
		  _e( 'No Classes Today' );  
		}
		
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max_classes'] = strip_tags( $new_instance['max_classes'] );
		$instance['classroom'] = strip_tags( $new_instance['classroom'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		/* Set initial values/defaults */
	  if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( "Today's Classes" );
		}

		if ( isset( $instance[ 'max_classes'] ) ) {
		   $max_classes = $instance[ 'max_classes'];
		}
		else {
		  $max_classes = 5;
		}

		if ( isset( $instance[ 'classroom' ] ) ) {
		  $classroom = $instance[ 'classroom'];
		}
		else {
		  $classroom = 'all';
		}

		$classrooms = WcsClassroom::model()->getAllRecords();

		/* Print Form */
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'max_classes' ); ?>"><?php _e( 'Max Classes:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'max_classes' ); ?>" name="<?php echo $this->get_field_name( 'max_classes' ); ?>" type="text" value="<?php echo esc_attr( $max_classes ); ?>" />
		<span class='description'><?php __( 'Maximum number of classes to display' ); ?></span>
		</p>

		<p>
		<select class="widefat" id="<?php echo $this->get_field_id( 'classroom' ); ?>" name="<?php echo $this->get_field_name( 'classroom' ); ?>">
			<option <?php if ( $classroom == 'all' ) echo "selected='selected' "; ?>value="all"><?php _e( 'All' ); ?></option>
			<?php foreach ( $classrooms as $value ): ?>
			<option <?php if ( $classroom == $value->classroom_name ) echo "selected='selected'"; ?>value="<?php echo $value->classroom_name; ?>"><?php echo $value->classroom_name; ?></option>
			<?php endforeach; ?>
    </select>
		</p>
		<?php
	}

}