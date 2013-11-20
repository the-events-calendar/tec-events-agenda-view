<?php
/*
 Plugin Name: The Events Calendar: Agenda View
 Description: This plugin adds an agenda view to your Tribe The Events Calendar suite.
 Version: 1.0
 Author: Modern Tribe, Inc.
 Author URI: http://www.tri.be
 Text Domain: 'tribe-event-agenda-view'
 License: GPLv2 or later

Copyright 2009-2013 by Modern Tribe Inc and the contributors

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( !defined( 'ABSPATH' ) )
	die( '-1' );

if ( ! class_exists( 'TribeAgenda' ) ) {
	class TribeAgenda {

		protected static $instance;

		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;

		public $agendaSlug = 'agenda';

		const PLUGIN_NAME = 'The Events Calendar: Agenda View';
		const DOMAIN = 'tribe-event-agenda-view';
		const MIN_WP_VERSION = '3.5';
		const REQUIRED_TEC_VERSION = '3.0';

		function __construct() {

			$this->pluginPath = trailingslashit( dirname( __FILE__ ) );
			$this->pluginDir = trailingslashit( basename( $this->pluginPath ) );
			$this->pluginUrl = trailingslashit( plugins_url().'/'.$this->pluginDir );
			$this->agendaSlug = sanitize_title(__('agenda', 'tribe-event-agenda-view'));

			require_once( 'template-tags.php' );
			require_once( 'tribe-agenda-view-template-class.php' );

			// settings tab
			add_action( 'tribe_settings_do_tabs', array( $this, 'settings_tab' ) );

			// inject agenda view into events bar & (display) settings
			add_filter( 'tribe-events-bar-views', array( $this, 'setup_agenda_in_bar' ), 40, 1 );

			// setup permalink routes
			add_filter( 'generate_rewrite_rules', array( $this, 'add_routes' ) );

			// make sure everything is ready in the query for agenda
			add_filter( 'tribe_events_pre_get_posts', array( $this, 'pre_get_posts'));

			// instantiate the template class
			add_action( 'template_redirect', array( $this, 'setup_template_class') );

			// load the proper template hooks (agenda) for the permalink
			add_filter( 'tribe_current_events_page_template', array( $this, 'select_page_template' ) );

			// add this plugin path to the views path
			add_filter( 'tribe_events_template_paths', array( $this, 'template_paths' ) );

		}

		public function settings_tab() {

			$settings = array(
				'priority' => 20,
				'fields' => array(
					'info-start' => array(
						'type' => 'html',
						'html' => '<div id="modern-tribe-info">'
					),
					'info-box-title' => array(
						'type' => 'html',
						'html' => '<h2>' . __('Agenda View', 'tribe-event-agenda-view') . '</h2>',
					),
					'info-box-description' => array(
						'type' => 'html',
						'html' => '<p>' . __('Customize your agenda view layout.', 'tribe-event-agenda-view') . '</p>',
					),
					'info-end' => array(
						'type' => 'html',
						'html' => '</div>',
					),
					'form-content-start' => array(
						'type' => 'html',
						'html' => '<div class="tribe-settings-form-wrap">',
					),
					'agendaViewLimit' => array(
						'type' => 'text',
						'label' => __( 'Limit Events', 'tribe-event-agenda-view' ),
						'tooltip' => __( 'Limit the amount of events that show on the Agenda View.', 'tribe-event-agenda-view' ),
						'size' => 'small',
						'default' => get_option( 'posts_per_page' ),
						'validation_type' => 'positive_int'
					),
					'form-content-end' => array(
						'type' => 'html',
						'html' => '</div>',
					),
				)
			);

			// instantiate the tab (positioned order before core help tab)
			new TribeSettingsTab( 'agenda', __( 'Agenda', 'tribe-event-agenda-view' ), $settings );
		}

		function setup_agenda_in_bar( $views ) {
			$views[] = array( 'displaying' => 'agenda',
												'anchor'		 => __( 'Agenda', 'tribe-event-agenda-view' ),
												'url'				=> tribe_get_agenda_permalink() );
			return $views;
		}

		function add_routes( $wp_rewrite ){

			// create new rules for the agenda permalinks
			$newRules = array();
			$newRules[trailingslashit( TribeEvents::instance()->rewriteSlug ) . trailingslashit($this->agendaSlug) . '?$'] = 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=agenda';

			$wp_rewrite->rules = $newRules + $wp_rewrite->rules;

		}

		function pre_get_posts( $query ){
			$agenda_query = false;
			$query->tribe_is_agenda = false;
			if(!empty( $query->query_vars['eventDisplay'] )) {
				$agenda_query = true;
				if ( $query->query_vars['eventDisplay'] == 'agenda' ) {
					$event_date = $query->get('eventDate') != '' ? $query->get('eventDate') : tribe_event_beginning_of_day( Date('Y-m-d') );
					$query->set( 'start_date', $event_date );
					$query->set( 'eventDate', $event_date );
					$query->set( 'orderby', 'event_date' );
					$query->set( 'order', 'ASC' );
					$query->set( 'posts_per_page', tribe_get_option( 'agendaViewLimit', '10' ) ); // show ALL day posts
					$query->set( 'hide_upcoming', false );
					$query->tribe_is_agenda = true;
				}
			}
			$query->tribe_is_event_agenda_query = $agenda_query;
			return $query->tribe_is_event_agenda_query ? apply_filters('tribe_events_agenda_pre_get_posts', $query) : $query;
		}

		function setup_template_class () {
			if (tribe_is_agenda()) {
				tribe_initialize_view('Tribe_Events_Agenda_Template');
			}
		}

		function select_page_template( $template ){
			// agenda view
			if( tribe_is_agenda() ) {
				$template = TribeEventsTemplates::getTemplateHierarchy('agenda');
			}
			return $template;
		}

		/**
		 * Check the minimum WP version and if TribeEvents exists
		 *
		 * @static
		 * @param string $wp_version
		 * @return bool Whether the test passed
		 */
		public static function prerequisites( $wp_version = null ) {;
			$pass = TRUE;
			$pass = $pass && class_exists( 'TribeEvents' );
			$pass = $pass && version_compare( is_null( $wp_version ) ? get_bloginfo( 'version' ) : $wp_version, self::MIN_WP_VERSION, '>=' );
			return $pass;
		}

    /**
     * Display a failure notice in the WordPress admin if the versions are not compatible
     *
     * @return void
     */

		public function fail_notices() {
			printf( '<div class="error"><p>%s</p></div>', sprintf( __( '%1$s requires WordPress v%2$s or higher and The Events Calendar v%3$s or higher.' ), self::PLUGIN_NAME, self::MIN_WP_VERSION, self::REQUIRED_TEC_VERSION ) );
		}

    /**
     * Static Singleton Factory Method
     *
     * @return TribeAgenda
     **/
    public static function instance() {
         if ( !isset( self::$instance ) ) {
              $className = __CLASS__;
              self::$instance = new $className;
         }
         return self::$instance;
    }

		/**
		 * Add agenda plugin path to the templates array
		 *
		 * @param $template_paths array
		 * @return array
		 * @since 1.0
		 **/
		function template_paths( $template_paths = array() ) {

			array_unshift($template_paths, $this->pluginPath);
			return $template_paths;

		}

	}

	/**
	 * Instantiate class and set up WordPress actions.
	 *
	 * @return void
	 */
	function Load_TribeAgenda() {
		if ( apply_filters( 'tec_rating_pre_check', class_exists( 'TribeAgenda' ) && TribeAgenda::prerequisites() ) ) {
			add_action( 'init', array( 'TribeAgenda', 'instance' ), -100, 0 );
		} else {
			// let the user know prerequisites weren't met
			add_action( 'admin_head', array( 'TribeAgenda', 'fail_notices' ), 0, 0 );
		}
	}
	add_action( 'plugins_loaded', 'Load_TribeAgenda', 1 ); // high priority so that it's not too late for addon overrides

}
