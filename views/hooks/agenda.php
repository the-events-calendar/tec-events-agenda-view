<?php
/**
 * @for Day Template
 * This file contains the hook logic required to create an effective day grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }


if( !class_exists('Tribe_Events_Agenda_Template')){
	class Tribe_Events_Agenda_Template extends Tribe_Template_Factory {

		static $timeslots = array();

		public static function init(){
	
			// clear out list hooks
			add_filter( 'tribe_events_list_show_separators', '__return_false' );
			add_filter( 'tribe_events_list_before_the_content', '__return_false' );
			add_filter( 'tribe_events_list_the_event_image', '__return_false' );
			add_filter( 'tribe_events_list_the_content', '__return_false' );
			add_filter( 'tribe_events_list_after_the_content', '__return_false' );
			add_filter( 'tribe_events_list_header_nav', '__return_false' );
			add_filter( 'tribe_events_list_footer_nav', '__return_false' );
			add_filter( 'tribe_events_list_before_footer_nav', '__return_false' );
			add_filter( 'tribe_events_list_before_header_nav', '__return_false' );

			// Override list hooks
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template' ), 20, 2 );
			add_filter( 'tribe_events_list_the_event_title', array( __CLASS__, 'the_event_title' ), 20, 1 );
			add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_title' ), 10, 1 );
			// Event meta
			add_filter( 'tribe_events_list_before_the_meta', array( __CLASS__, 'before_the_meta' ), 20, 2 );
			add_filter( 'tribe_events_list_the_meta', array( __CLASS__, 'the_meta' ), 20, 2 );
			add_filter( 'tribe_events_list_after_the_meta', array( __CLASS__, 'after_the_meta' ), 20, 2 );

			add_filter( 'tribe_events_list_before_header', array( __CLASS__, 'before_header' ), 20, 1 );
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop'), 20, 1);
						// Event content
			add_filter( 'tribe_events_list_before_the_content', array( __CLASS__, 'before_the_content' ), 20, 2 );
			add_filter( 'tribe_events_list_the_content', array( __CLASS__, 'the_content' ), 20, 2 );
			add_filter( 'tribe_events_list_after_the_content', array( __CLASS__, 'after_the_content' ), 20, 2 );
			add_filter( 'tribe_events_list_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 20, 1 );
			add_filter( 'tribe_events_list_before_footer', array( __CLASS__, 'before_footer' ), 20, 1 );

			wp_enqueue_style( 'tribe-agenda-view', TribeAgenda::instance()->pluginUrl . "inc/css/agenda-view.css", array(), '0.1', 'screen' );
			wp_enqueue_script( 'tribe-agenda-view-scripts', TribeAgenda::instance()->pluginUrl . "inc/js/agenda-view.js", true );			
		}

		public static function before_template( $content, $post_id ){
			$html = '<div id="tribe-events-content" class="tribe-events-list tribe-events-agenda">';
			return apply_filters('tribe_template_factory_debug', $html, 'Tribe_Events_Agenda_before_template');
		}
	
		function the_title( $html ){
			global $wp_query;
			$html = sprintf('<h2 class="tribe-events-page-title">%s %s</h2>',
				__('Agenda starting ', 'tribe-event-agenda-view'),
				Date("l, F jS Y", strtotime($wp_query->get('start_date')))
				);
			return $html;
		}
		// Event Title
		public static function the_event_title( $html ){
			$event_id = get_the_ID();
			$venue_name = tribe_get_meta( 'tribe_event_venue_name' );
			$venue_address = tribe_get_meta('tribe_event_venue_address');

			$html = sprintf('<div class="agenda-event-heading"><h2 class="entry-title summary"><a class="url" href="%s" title="%s" rel="bookmark">%s</a>%s%s</h2>',
				tribe_get_event_link(),
				get_the_title( $event_id ),
				get_the_title( $event_id ),
				( !empty( $venue_name ) || !empty( $venue_address ) ) ? ' @ ' : '',
				$venue_name
				);
			return $html;
		}
// Event Meta
		public static function before_the_meta( $content, $post_id ){
			$html = '';

			return apply_filters('tribe_template_factory_debug', $html, 'Tribe_Events_Agenda_before_the_meta');
		}
		public static function the_meta( $content, $post_id ){
			ob_start();
		?>
			<div class="tribe-events-event-meta">
				<h3 class="updated published time-details">
					<?php
					global $post;
					if ( !empty( $post->distance ) ) { ?>
						<strong><?php echo '['. tribe_get_distance_with_unit( $post->distance ) .']'; ?></strong>
					<?php } ?>
				</h3>
				<?php // venue display info

				$venue_name = tribe_get_meta( 'tribe_event_venue_name' );
				$venue_address = tribe_get_meta('tribe_event_venue_address');
				
				printf('<h3 class="tribe-venue-details">%s%s</h3>',
					( !empty( $venue_name ) && !empty( $venue_address ) ) ? '' : '',
					( !empty( $venue_address ) ) ? $venue_address : ''
				);

				?>
			</div><!-- .tribe-events-event-meta -->
		</div><!-- .agenda-event-heading -->
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'Tribe_Events_Agenda_the_meta');
		}
		public static function after_the_meta( $content, $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'Tribe_Events_Agenda_after_the_meta');
		}		
		public static function ical_link( $link ){
			global $wp_query;
			$day = $wp_query->get('start_date');
			return trailingslashit( esc_url(trailingslashit( tribe_get_day_permalink( $day ) ) . 'ical') );
		}

		// Day Header
		public static function before_header( $html ){
			global $wp_query;
			$current_day = $wp_query->get('start_date');
			
			$html = '<div id="tribe-events-header" data-date="'. Date('Y-m-d', strtotime($current_day) ) .'" data-title="'. wp_title( '&raquo;', false ) .'" data-header="'. Date("l, F jS Y", strtotime($wp_query->get('start_date'))) .'">';
			return $html;
		}
		// Day Before Loop
		public static function inside_before_loop( $pass_through ){
			global $post;

			$html = '';

			// setup the "start time" for the event header
			$start_time = !empty( $post->tribe_is_allday ) && $post->tribe_is_allday ? 
				__( 'All Day', 'tribe-events-calendar' ) :
				tribe_get_start_date( null, false, 'l, F jS Y g:i A' );

			// determine if we want to open up a new time block
			if( ! in_array( $start_time, self::$timeslots ) ) {

				self::$timeslots[] = $start_time;	

				// close out any prior opened time blocks
				$html .= ( Tribe_Events_List_Template::$loop_increment > 0 ) ? '</div>' : '';

				// open new time block & time vs all day header
				$html .= sprintf( '<div class="tribe-events-day-time-slot"><h5>%s</h5>', $start_time );

			}
			return apply_filters('tribe_template_factory_debug', $html . $pass_through, 'Tribe_Events_Agenda_inside_before_loop');
		}

		// Event Content
		public static function before_the_content( $content, $post_id ){
			$html = '<div class="tribe-agenda-event-description tribe-content">';
						if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image(null, 'large');
			}
			return apply_filters('tribe_template_factory_debug', $html, 'Tribe_Events_Agenda_before_the_content');
		}

		public static function the_content( $content, $post_id ){
			$html = '';

				$html .= the_content();	
			return apply_filters('tribe_template_factory_debug', $html, 'Tribe_Events_Agenda_the_content');
		}

		public static function after_the_content( $content, $post_id ){
			$html = '</div><!-- .tribe-list-event-description -->';
			return apply_filters('tribe_template_factory_debug', $html, 'Tribe_Events_Agenda_after_the_content');
		}	

		// Day Inside After Loop
		public static function inside_after_loop( $pass_through ){
			global $wp_query;

			// close out the last time block
			$html = ( Tribe_Events_List_Template::$loop_increment == count($wp_query->posts) ) ? '</div>' : '';

			return apply_filters('tribe_template_factory_debug', $pass_through . $html, 'Tribe_Events_Agenda_inside_after_loop');
		}
		// Day Footer
		public static function before_footer( $html ){			
			$html = '<div id="tribe-events-footer">';
			return $html;
		}
		// Day Navigation

	}
	Tribe_Events_Agenda_Template::init();
}
