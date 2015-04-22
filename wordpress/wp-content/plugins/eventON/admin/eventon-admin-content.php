<?php
/**
 * Functions used for the showing help/links to eventon resources in admin
 *
 * @author 		EventON
 * @category 	Admin
 * @package 	Eventon/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Help Tab Content
 *
 * Shows some text about WooCommerce and links to docs.
 *
 * @access public
 * @return void
 */
function eventon_admin_help_tab_content() {
	$screen = get_current_screen();

	$screen->add_help_tab( array(
	    'id'	=> 'eventon_overview_tab',
	    'title'	=> __( 'Overview', 'eventon' ),
	    'content'	=>

	    	'<p>' . __( 'Thank you for using EventON WordPress Event Calendar plugin. ', 'eventon' ). '</p>'

	) );

	$screen->add_help_tab( array(
	    'id'	=> 'eventon_settings_tab',
	    'title'	=> __( 'Settings', 'eventon' ),
	    'content'	=>
	    	'<p>' . __( 'In here you can change variety of settings for the calendar that will allow you further customization to your need:', 'eventon' ) . '</p>' .
	    	'<p><strong>' . __( 'General', 'eventon' ) . '</strong> - ' . __( 'General settings include basic show hide elements for overall calendar, Event type taxonomies naming and enabling them for frontend.', 'eventon' ) . '</p>' .
	    	'<p><strong>' . __( 'Appearance', 'eventon' ) . '</strong> - ' . __( 'This is where you can customize the look of the overall calendar aesthetically.', 'eventon' ) . '</p>' .
	    	'<p><strong>' . __( 'Icons', 'eventon' ) . '</strong> - ' . __( 'In here you can upload your own icons to replace those are default in the calendar event card.', 'eventon' ) . '</p>' .
	    	'<p><strong>' . __( 'Third Party API', 'eventon' ) . '</strong> - ' . __( 'In here you can find the third party APIs supported in this plugin that you can enable with API keys', 'eventon' ) . '</p>'
	) );

	$screen->add_help_tab( array(
	    'id'	=> 'eventon_overview_tab_2',
	    'title'	=> __( 'How to use shortcode', 'eventon' ),
	    'content'	=>
				'<p><b>Basic Use</b> [add_eventon]<br/>
				<i>NOTE [add_ajde_evcal] shortcode is also supported - which was used in versions older than 2.1. However please note this shortcode is deprecating in the future.</i></p>' .
				
				
				'<p><b>Supported variables for shortcode</b><br/>
					<b>cal_id</b> Whenever you are going to add more than one calendar on a page, you MUST specify a unique calendar ID.
				</p>'.
				'<p><b>event_type & event_type_2</b> You can add calendars to show only certain event types. This plugin support 2 taxonomies which are "event_type" and "event_type_2" (slug names). In order to do this, copy the event_type tag ID separated by commas.
				</p>'
				
				.'<p><b>event_count</b> This can be used to show only a specific number of events in a calendar for a given month. It will be first event till the count.
				</p>'
				
				.'<p><b>month_incre</b> You can create calendars with different months as starting point with this. eg. [add_ajde_evcal month_incre="+4"] this value should be plus or minus integer from the current month.
				</p>'
	) );

	$screen->add_help_tab( array(
	     'id'	=> 'eventon_overview_tab_3',
	     'title'	=> __( 'How to use Template Tags', 'eventon' ),
	     'content'	=>
				"<p>Use this template tag in a theme file, such as a page template.</p>
				<p><i>NOTE: ajde_evcal_calendar() php function is also supported - which was used in versions older than 2.1. However please note this is deprecating in the future.</i></p>
				
				<ol>
					<li>
						<p><b>Usage:</b><br/>
<pre>
&lt;?php
if( function_exists('add_eventon')) {
	add_eventon(&#36;args); 
}
?&gt;
</pre>						
						</p>
					</li>
					<li>
						<p><b>Default Usage:</b><br/>
<pre>&lt;?php &#36;args = array(
	'cal_id'		=> 1,
	'event_type'		=> '3,4,1',
	'event_type_2'		=> '4,7',
	'month_incre'		=> +2,
	'event_count'		=> 3,
	'show_upcoming'		=> 0,
	'number_of_months'	=> 2
); ?&gt;
</pre>"


	) );

	$screen->add_help_tab( array(
	     'id'	=> 'eventon_overview_tab_4',
	     'title'	=> __( 'Parameters', 'eventon' ),
	     'content'	=>
				"<b><u>Parameters:</u></b><br/>
<b><i>cal_id</i></b><br/>
(string) A unique ID to be used for this calendar. This is recommended when you are adding multiple calendars in one page.<br/><br/>

<b><i>event_type/ event_type_2</i></b><br/>
(integer) Tag_ID of the event type that you only want to show in the calendar. The IDs must be separated by commas.<br/><br/>

<b><i>month_incre</i></b><br/>
(integer) Number of months (integer) that should be added or substracted from the current month for the month that will show in the calendar. <br/>
Default: 0<br/><br/>

<b><i>event_count</i></b><br/>
(integer) Limit the number of events that get displayed in a month. Set this to number of events (int) to show.<br/>
Default: 0<br/><br/>

<b><i>show_upcoming</i></b><br/>
(integer) Limit the number of events that get displayed in a month. Set this to <b>\"1\"</b> for the calendar to show upcoming events.<br/>
Default: 0<br/><br/>

<b><i>number_of_months</i></b><br/>
(integer) This represents number of months to show in the upcoming events list, separated by month. \"show_upcoming\" must be set to <b>\"1\"</b> for this to work.<br/>
Default: 0<br/><br/>"
	) );

	$screen->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'eventon' ) . '</strong></p>' .
		'<p><a href="http://www.myeventon.com/" target="_blank">' . __( 'EventON', 'eventon' ) . '</a></p>' .
		'<p><a href="http://www.myeventon.com/faq/" target="_blank">' . __( 'FAQ Section', 'eventon' ) . '</a></p>' .
		'<p><a href="http://www.myeventon.com/changelog/" target="_blank">' . __( 'Changelog', 'eventon' ) . '</a></p>'.
		'<p><a href="http://www.myeventon.com/documentation/" target="_blank">' . __( 'Documentation', 'eventon' ) . '</a></p>'.
		'<p><a href="http://www.myeventon.com/addons/" target="_blank">' . __( 'Addons', 'eventon' ) . '</a></p>'
	);
}
?>