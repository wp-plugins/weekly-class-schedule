<?php
/**
 * @file
 * Defines the WcsStyle class
 */
class WcsStyle
{
  /**
   * Generates the WCS dynamic CSS
   */
  public static function getDynamicCss()
  {
    $td_width = self::generateTdWidth();
    $base_class_color = get_option( 'wcs_base_class_color', 'ddffdd' );
    $secondary_class_color = get_option( 'wcs_secondary_class_color', 'ddddff' );
    $hover_class_color = get_option( 'wcs_hover_class_color', 'ffdddd' );
    $border_color = get_option( 'wcs_border_color', 'dddddd' );
    $headings_color = get_option( 'wcs_headings_color', '666666' );
    $heading_background_color = get_option( 'wcs_headings_background_color', 'eeeeee' );
    $text_color = get_option( 'wcs_text_color', '373737' );
    $background_color = get_option( 'wcs_background_color', 'ffffff' );
    $qtip_background_color = get_option( 'wcs_qtip_background', 'ffffff' );
    $links_color = get_option( 'wcs_links_color', '1982D1' );
    
    /* ------------- CSS ------------ */
    $dynamic_css =
    
<<<CSS
<style>
	table.wcs-schedule td {
		width: $td_width%;
	}
	table.wcs-schedule th,
	table.wcs-schedule td {
		border-color: #$border_color;
	}
	table.wcs-schedule th {
		color: #$headings_color;
		background-color: #$heading_background_color;
	}
	table.wcs-schedule td {
	  color: #$text_color;
		background-color: #$background_color;
	}
	td.wcs-schedule-cell.active,
	.wcs-active-div {
		background: #$base_class_color;
	}
	.wcs-active-div.even {
		background: #$secondary_class_color;
	}
	.wcs-class-details {
		background: #$hover_class_color;
		border-color: #$border_color;
	}
	.qtip-content {
		background: #$qtip_background_color !important;
	}
	.wcs-active-div a {
		color: #$links_color;
	}
</style>
CSS;
    
    /* ------------- END ------------ */
    
    return $dynamic_css;
  }
  
  /**
   * Generates the td width value based on the number of days setting.
   */
  private static function generateTdWidth()
  {
    $number_of_days = get_option( 'wcs_number_of_days', 7 );
    return floor( 90 / $number_of_days );
  }
}