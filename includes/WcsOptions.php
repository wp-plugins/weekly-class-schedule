<?php
/**
 * @file
 * Defines the WcsOptions class.
 */

abstract class WcsOptions
{
  /**
   * Display validation errors and confirmations in the options page.
   */
  public static function renderOptionsPage()
  {
    if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
      $settings = get_settings_errors();
      if ( ! empty( $settings) ) {
        foreach ( $settings as $value ) {
          WcsHtml::show_wp_message( $value['message'], $value['type'] );
        }
      }
    }

    require_once WCS_PLUGIN_DIR . '/views/OptionsView.php';
  }

  /**
   * Validate a valid hex value
   *
   * @param string $value
   */
  public static function validateHexValue( $value ) {
    if ( preg_match( "/^[0-9a-fA-F]{6}$/", $value ) == 0 ) {
      add_settings_error( $key, 'wcs_base_color', __( 'Invalid color. Please use a valid hex value.' ), 'error' );
    }
    else {
      return $value;
    }
  }

  public static function wcs_options()
  {
    /* Add help/instructions section */
    add_settings_section(
      'wcs_instructions',
      __( 'Using the Weekly Class Schedule' ),
      array('WcsOptions', 'instructions_section'),
      'wcs-options'
    );
    
    /* Add General Settings */
    add_settings_section(
      'wcs_general_settings',
      __( 'General Settings' ),
      array('WcsOptions', 'general_settings_section'),
      'wcs-options'
    );

    $title = __( 'First day of week' );
    $desc = __( 'The day the schedule will start in' );

    add_settings_field(
    	'wcs_first_day_of_week',
    	"$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'first_day_of_week_field' ),
    	'wcs-options',
    	'wcs_general_settings'
    );

    $title = __( 'Number of days to display' );
    $desc = __( 'The number of days to display including the first day of the week.' );

    add_settings_field(
    	'wcs_number_of_days',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'number_of_days_field' ),
    	'wcs-options',
    	'wcs_general_settings'
    );

    $title = __( 'Enable 24-hour mode' );
    $desc = __( 'Enabling this will display all the hours in a 24-hour clock mode as opposed to 12-hour clock mode (AM/PM).' );

    add_settings_field(
    	'wcs_24_hour_mode',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'wcs_24_hour_mode_field' ),
      'wcs-options',
      'wcs_general_settings'
    );

    $title = __( 'Time Increments' );
    $desc = __( 'Only affects the schedule entry form, not the final output.' );

    add_settings_field(
      'wcs_time_increments',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'time_increments_field' ),
      'wcs-options',
      'wcs_general_settings'
    );

    $title = __( 'Detect classroom collisions' );
    $desc = __( 'Enabling this feature will prevent scheduling of multiple classes at the same classroom at the same time.' );
    
    add_settings_field(
      'wcs_detect_classroom_collisions',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'detect_classroom_collisions_field' ),
      'wcs-options',
      'wcs_general_settings'
    );
    
    $title = __( 'Detect instructor collisions' );
    $desc = __( 'Enabling this feature will prevent the scheduling of an instructor for multiple classes at the same.' );
    
    add_settings_field(
      'wcs_detect_instructor_collisions',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'detect_instructor_collisions_field' ),
      'wcs-options',
      'wcs_general_settings'
    );

    $title = __( 'Enable Timezones' );
    $desc = __( 'Enabling this will add a timezone field to the schedule entry form. The final time output will be calculated based upon the site/server settings.' );
    
    add_settings_field(
      'wcs_use_timezones',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'use_timezones_field' ),
      'wcs-options',
      'wcs_general_settings'
    );

    $title = __( 'Use short day names' );
    $desc = __( "Displays the first 3 letters of the weekday on the schedule. For example 'Mon' instead of 'Monday'." );
    
    add_settings_field(
      'wcs_short_day_names',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'short_day_names_field' ),
        'wcs-options',
        'wcs_general_settings'
    );
    
    $title = __( 'Class Details Template' );
    $desc = __( 'Use placholders to design the way the class details appear in the schedule' ) . '<br/><br/>';
    $desc .= '<strong>' . __( 'Available placholders') . '</strong>';
    $desc .= ': [class], [instructor], [start hour], [end hour], [notes].';

    add_settings_field(
      'wcs_class_template',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'class_template_field' ),
      'wcs-options',
      'wcs_general_settings'
    );

    /* Register general settings */
    register_setting( 'wcs_options', 'wcs_first_day_of_week' );
    register_setting( 'wcs_options', 'wcs_number_of_days' );
    register_setting( 'wcs_options', 'wcs_24_hour_mode' );
    register_setting( 'wcs_options', 'wcs_time_increments' );
    register_setting( 'wcs_options', 'wcs_detect_classroom_collisions' );
    register_setting( 'wcs_options', 'wcs_detect_instructor_collisions' );
    register_setting( 'wcs_options', 'wcs_use_timezones' );
    register_setting( 'wcs_options', 'wcs_short_day_names' );
    register_setting( 'wcs_options', 'wcs_class_template' );

    /* Add Color Settings */
    add_settings_section(
      'wcs_appearance_settings',
      'Appearance Settings',
      array('WcsOptions', 'appearance_settings_section'),
      'wcs-options'
    );

    $title = __( 'Base class color' );
    $desc = __( 'The default color for classes in the schedule.' );
    
    add_settings_field(
      'wcs_base_class_color',
    	"$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'base_class_color_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );

    $title = __( 'Alternate class color' );
    $desc = __( 'In case there are more than one class in the same cell, colors will alternate between this and the base color.' );
    add_settings_field(
      'wcs_secondary_class_color',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'secondary_class_color_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );

    $title = __( 'Class details box' );
    $desc = __( 'Color of the class details box which appears when hovering over a class.' );
    
    add_settings_field(
      'wcs_hover_class_color',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'hover_class_color_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );

    $title = __( 'Border color' );
    $desc = __( 'This color is used for all borders in the schedule output' );
    
    add_settings_field(
      'wcs_border_color',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'border_color_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );

    $title = __( 'Schedule headings color' );
    $desc = __( 'Text color of the schedule headings (weekdays, hours).' );
    
    add_settings_field(
      'wcs_headings_color',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'headings_color_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );
    
    $title = __( 'Schedule headings background' );
    $desc = __( 'Background color of the schedule headings (weekdays, hours).' );

    add_settings_field(
      'wcs_headings_background_color',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'headings_background_color_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );

    $title = __( 'Text color' );
    $desc = __( 'Text color of schedule entries/classes.' );
    
    add_settings_field(
      'wcs_text_color',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'text_color_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );

    $title = __( 'Background color' );
    $desc = __( 'Background color for the entire schedule.' );
    
    add_settings_field(
    	'wcs_background_color',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'background_color_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );

    $title = __( 'qTip background color' );
    $desc = __( 'Background color of the qTip pop-up box.' );
    
    add_settings_field(
    	'wcs_qtip_background',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'qtip_background_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );
    
    $title = __( 'Links color' );
    $desc = __( 'The color of the links which appear in the class details box' );
    
    add_settings_field(
    	'wcs_links_color',
      "$title:<br/><span class='description'>$desc</span>",
      array('WcsOptions', 'links_color_field' ),
      'wcs-options',
      'wcs_appearance_settings'
    );

    /* Register appearance settings */
    register_setting( 'wcs_options', 'wcs_base_class_color', array( 'WcsOptions', 'validateHexValue' ) );
    register_setting( 'wcs_options', 'wcs_secondary_class_color', array( 'WcsOptions', 'validateHexValue' ) );
    register_setting( 'wcs_options', 'wcs_hover_class_color', array( 'WcsOptions', 'validateHexValue' ) );
    register_setting( 'wcs_options', 'wcs_border_color', array( 'WcsOptions', 'validateHexValue' ) );
    register_setting( 'wcs_options', 'wcs_headings_color', array( 'WcsOptions', 'validateHexValue' ) );
    register_setting( 'wcs_options', 'wcs_headings_background_color', array( 'WcsOptions', 'validateHexValue' ) );
    register_setting( 'wcs_options', 'wcs_text_color', array( 'WcsOptions', 'validateHexValue' ) );
    register_setting( 'wcs_options', 'wcs_background_color', array( 'WcsOptions', 'validateHexValue' ) );
    register_setting( 'wcs_options', 'wcs_qtip_background', array( 'WcsOptions', 'validateHexValue' ) );
    register_setting( 'wcs_options', 'wcs_links_color', array( 'WcsOptions', 'validateHexValue' ) );
  }

  /* Renders the instructions section */
  public static function instructions_section()
  {
    $help_text = __( 'To display all the classes in a single schedule, simply enter the shortcode <code>[wcs]</code> inside a page or a post.' ) . '<br/>';
    $help_text .= __( "The schedule layout is vertical by default but it's easy to switch to horizontal using the 'layout' attribute like this: <code>[wcs layout=horizontal]</code>. " );
    $help_text .= __( "It's also possible to output the schedule as a list using the list layout: <code>[wcs layout=list]</code>. The list layout is better for mobile devices." ) . '<br/>';
    $help_text .= __( 'In order to display a single classroom, use the classroom attribute like this: <code>[wcs classroom="Classroom A"]</code> Where "Classroom A" is the name of the classroom as it appears in the database.' ) . '<br/>';
    $help_text .= __( 'A finalized shortcode may look something like <code>[wcs classroom="Classroom A" layout=list]</code>.' );
    echo $help_text;
  }
  
  /* Render General Settings section */
  public static function general_settings_section()
  {
    /* Set default values */
    add_option( 'wcs_24_hour_mode', 'no' );
    add_option( 'wcs_detect_classroom_collisions', 'yes' );
    add_option( 'wcs_detect_instructor_collisions', 'yes' );
    add_option( 'wcs_use_timezones', 'yes' );
    add_option( 'wcs_short_day_names', 'no' );
    
    $template = "[class] with [instructor]\n[start hour] to [end hour]\n[notes]";
    add_option( 'wcs_class_template', $template );
  }

  /* Render first day of week field */
  public static function first_day_of_week_field()
  {
    $weekdays = WcsSchedule::model()->generateWeekdays();
    echo WcsHtml::generateSelectList( $weekdays, array( 'name' => 'wcs_first_day_of_week' ), TRUE, get_option( 'wcs_first_day_of_week', '0' ) );
  }

  /* Render number of days field */
  public static function number_of_days_field()
  {
    $number = array( 1, 2, 3, 4, 5, 6, 7 );
    echo WcsHtml::generateSelectList( $number, array( 'name' => 'wcs_number_of_days' ), FALSE, get_option( 'wcs_number_of_days', 7 ) );
  }

  /* Render 24 hour mode field */
  public static function wcs_24_hour_mode_field()
  {
    $checked = ( get_option( 'wcs_24_hour_mode' ) == 'yes' ) ? 'checked="checked"' : '';
    echo "<input type='checkbox' $checked name='wcs_24_hour_mode' value='yes' /> Yes";
  }

  /* Render time increments field */
  public static function time_increments_field()
  {
    $incs = array( 5, 10, 15, 30 );
    echo WcsHtml::generateSelectList( $incs, array( 'name' => 'wcs_time_increments' ), FALSE, get_option( 'wcs_time_increments', 15 ) ) . __( ' Minutes' );
  }

  /* Render detect classroom collisions field */
  public static function detect_classroom_collisions_field()
  {
    $checked = ( get_option( 'wcs_detect_classroom_collisions', 'yes' ) == 'yes' ) ? 'checked="checked"' : '';
    echo "<input type='checkbox' $checked name='wcs_detect_classroom_collisions' value='yes' /> Yes";
  }
  
  /* Render detect instructor collisions field */
  public static function detect_instructor_collisions_field()
  {
    $checked = ( get_option( 'wcs_detect_instructor_collisions', 'yes' ) == 'yes' ) ? 'checked="checked"' : '';
    echo "<input type='checkbox' $checked name='wcs_detect_instructor_collisions' value='yes' /> Yes";
  }

  public static function use_timezones_field()
  {
    $checked = ( get_option( 'wcs_use_timezones' ) == 'yes' ) ? 'checked="checked"' : '';
    echo "<input type='checkbox' $checked name='wcs_use_timezones' value='yes' /> Yes";
  }

  public static function short_day_names_field()
  {
    $checked = ( get_option( 'wcs_short_day_names' ) == 'yes' ) ? 'checked="checked"' : '';
    echo "<input type='checkbox' $checked name='wcs_short_day_names' value='yes' /> Yes";
  }

  public static function class_template_field()
  {
    $template = "[class] with [instructor]\n[start hour] to [end hour]\n[notes]";
    $default = get_option( 'wcs_class_template', $template );
    echo "<textarea name='wcs_class_template' cols='40' rows='6'>$default</textarea>";
  }

  /* --------------------------------------------------------------------------------- */

  /* Render Color Settings section */
  public static function appearance_settings_section()
  {
    /* Set default colors */
    add_option( 'wcs_base_class_color', 'ddffdd' );
    add_option( 'wcs_secondary_class_color', 'ddddff' );
    add_option( 'wcs_hover_class_color', 'ffdddd' );
    add_option( 'wcs_border_color', 'dddddd' );
    add_option( 'wcs_headings_color', '666666' );
    add_option( 'wcs_headings_background_color', 'eeeeee' );
    add_option( 'wcs_text_color', '373737' );
    add_option( 'wcs_background_color', 'ffffff' );
    add_option( 'wcs_qtip_background', 'ffffff' );
    add_option( 'wcs_links_color', '1982D1' );
  }

  public static function base_class_color_field()
  {
    $default = get_option( 'wcs_base_class_color', 'ddffdd' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_base_class_color' name='wcs_base_class_color' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_base_class_color'>&nbsp</span>";
    echo '&nbsp; <span class="description">Default: DDFFDD</span>';
  }

  public static function secondary_class_color_field()
  {
    $default = get_option( 'wcs_secondary_class_color', 'ddddff' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_secondary_class_color' name='wcs_secondary_class_color' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_secondary_class_color'>&nbsp</span>";
    echo '&nbsp; <span class="description">Default: DDDDFF</span>';
  }

  public static function hover_class_color_field()
  {
    $default = get_option( 'wcs_hover_class_color', 'ffdddd' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_hover_class_color' name='wcs_hover_class_color' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_hover_class_color'>&nbsp</span>";
    echo '&nbsp; <span class="description">Default: FFDDDD</span>';
  }

  public static function border_color_field()
  {
    $default = get_option( 'wcs_border_color', 'dddddd' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_border_color' name='wcs_border_color' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_border_color'>&nbsp</span>";
    echo '&nbsp; <span class="description">Default: DDDDDD</span>';
  }

  public static function headings_color_field()
  {
    $default = get_option( 'wcs_headings_color', '666666' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_headings_color' name='wcs_headings_color' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_headings_color'>&nbsp</span>";
    echo '&nbsp; <span class="description">Default: 666666</span>';
  }

  public static function headings_background_color_field()
  {
    $default = get_option( 'wcs_headings_background_color', 'eeeeee' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_headings_background_color' name='wcs_headings_background_color' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_headings_background_color'>&nbsp</span>";
    echo '&nbsp; <span class="description">Default: EEEEEE</span>';
  }

  public static function text_color_field()
  {
    $default = get_option( 'wcs_text_color', '373737' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_text_color' name='wcs_text_color' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_text_color'>&nbsp</span>";
        echo '&nbsp; <span class="description">Default: 373737</span>';
  }

  public static function background_color_field()
  {
    $default = get_option( 'wcs_background_color', 'ffffff' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_background_color' name='wcs_background_color' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_background_color'>&nbsp</span>";
    echo '&nbsp; <span class="description">Default: FFFFFF</span>';
  }

  public static function qtip_background_field()
  {
    $default = get_option( 'wcs_qtip_background', 'ffffff' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_qtip_background' name='wcs_qtip_background' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_qtip_background'>&nbsp</span>";
    echo '&nbsp; <span class="description">Default: FFFFFF</span>';
  }
  
  public static function links_color_field()
  {
    $default = get_option( 'wcs_links_color', '1982D1' );
    echo "<input type='text' class='wcs_colorpicker' id='wcs_links_color' name='wcs_links_color' value='$default' size='8'>";
    echo "<span style='background: #$default;' class='colorpicker-preview wcs_links_color'>&nbsp</span>";
    echo '&nbsp; <span class="description">Default: 1982D1</span>';
  }
}