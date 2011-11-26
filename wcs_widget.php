<?php
/**
 * WCS_Day_Widget Class
 */
class WCS_Day_Widget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::WP_Widget( /* Base ID */'wcs_day_widget', /* Name */'WCS Day Widget', array( 
		'description' => 'Use this widget to display a list of classes for the current day' 
		) );
	}

	/** WP_Widget::widget */
	function widget( $args, $instance ) {
		global $wpdb;
		$enable_24h = get_option( 'enable_24h' );
		$enable_classrooms = get_option( 'enable_classrooms' );
		
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$no_classes_message = apply_filters( 'widget_text ', $instance['no_classes'] );
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title; ?>
		<?php
		// Print schedule
		$sql = $wpdb->prepare(
			"SELECT * FROM " 
			. $wpdb->prefix . 
			"wcs_studio_schedule WHERE week_day = '" . ucwords(date('l'))  . "'"
		);
		$results = $wpdb->get_results($sql);
		
		if ( $results ) {
			echo "<ul>";
			foreach($results as $record) {
				if ( $enable_24h == "on" ) {
					$class_start = clean_time_format( $record->start_hour );
					$class_end = clean_time_format( $record->end_hour );
				} else {
					$class_start = convert_to_am_pm( $record->start_hour );
					$class_end = convert_to_am_pm( $record->end_hour );
				}
				$desc_sql = $wpdb->prepare("SELECT item_description FROM " . $wpdb->prefix . "wcs_class WHERE id = '" . $record->class_id . "'"); 
				$inst_sql = $wpdb->prepare("SELECT item_description FROM " . $wpdb->prefix . "wcs_instructor WHERE id = '" . $record->instructor_id . "'");
				$classroom_sql = $wpdb->prepare("SELECT item_description FROM " . $wpdb->prefix . "wcs_classroom WHERE id = '" . $record->instructor_id . "'");
				  
				$class = $record->class;
				$inst = $record->instructor;
				$classroom = $record->classroom;
				$class_desc = $wpdb->get_var($desc_sql);
				$inst_desc = $wpdb->get_var($inst_sql);
				if ( $enable_classrooms == 'on' ) {
					$classroom_desc = $wpdb->get_var($classroom_sql);
				} 
				
				$output = "<li><a class='qtip-target' title='"; 
				$output .= $class_desc . "'>" . $class . "</a> - "; 
				$output .= $class_start . " to " . $class_end . " with <a class='qtip-target' title='";
				$output .= $inst_desc . "'>" . $inst . "</a>";
				
				if ( $enable_classrooms == 'on' ) {
					$output .= " in <a class='qtip-target' title='" . $classroom_desc . "'>" . $classroom . "</a>";
				} 
				$output .= "</li>";
				echo $output;
			}
			echo "</ul>";
		} else {
			echo $no_classes_message;
		}
		echo $after_widget;
	}

	/** WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['no_classes'] = strip_tags($new_instance['no_classes']);
		return $instance;
	}

	/** WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			$no_classes = esc_attr( $instance[ 'no_classes' ] );
		}
		else {
			$title = __( 'New title', 'text_domain' );
			$no_classes = __( 'No classes today', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		
		<label for="<?php echo $this->get_field_id('no_classes'); ?>"><?php _e('No Classes Message:'); ?></label> 
		<textarea class="widefat" id="<?php echo $this->get_field_id('no_classes'); ?>" name="<?php echo $this->get_field_name('no_classes'); ?>"><?php echo $no_classes; ?></textarea>
		</p>
		<?php 
	}

}
add_action( 'widgets_init', create_function( '', 'register_widget("WCS_Day_Widget");' ) );