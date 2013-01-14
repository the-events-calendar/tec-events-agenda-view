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

      // settings tab
      add_action( 'tribe_settings_do_tabs', array( $this, 'settings_tab' ) );

      // inject agenda view into events bar & (display) settings
      add_filter( 'tribe-events-bar-views', array( $this, 'setup_agenda_in_bar' ), 40, 1 );

      // add appropriate body_classes if we're on the right view
      add_filter( 'body_class', array( $this, 'body_class') );

      // setup permalink routes
      add_filter( 'generate_rewrite_rules', array( $this, 'add_routes' ) );

      // make sure everything is ready in the query for agenda
      add_filter( 'tribe_events_pre_get_posts', array( $this, 'pre_get_posts'));

      // load the proper template hooks (agenda) for the permalink
      add_filter( 'tribe_current_events_page_template', array( $this, 'select_page_template' ) );

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
  					'label' => __( 'Limit Amount Per Day', 'tribe-event-agenda-view' ),
            'tooltip' => __( 'If more events occur on a given day we will link to day view if \'The Events Calendar PRO\' is available.', 'tribe-event-agenda-view' ),
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
      new TribeSettingsTab( 'agenda', __( 'Agenda View', 'tribe-event-agenda-view' ), $settings );
    }

    function setup_agenda_in_bar( $views ) {
      $views[] = array( 'displaying' => 'agenda',
                        'anchor'     => __( 'Agenda', 'tribe-event-agenda-view' ),
                        'url'        => tribe_get_week_permalink() );
      return $views;
    }

    function body_class( $classes ){
      if( TribeEvents::instance()->displaying == 'agenda' ) {
          $classes[] = ' tribe-events-agenda';
          // remove the default gridview class from core
          $classes = array_diff($classes, array('events-gridview'));
      }
      return $classes;
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
          $event_date = $query->get('eventDate') != '' ? $query->get('eventDate') : Date('Y-m-d');
          $query->set( 'start_date', tribe_event_beginning_of_day( $event_date ) );
          $query->set( 'end_date', tribe_event_end_of_day( $event_date ) );
          $query->set( 'eventDate', $event_date );
          $query->set( 'orderby', 'event_date' );
          $query->set( 'order', 'ASC' );
          $query->set( 'posts_per_page', -1 ); // show ALL day posts
          $query->set( 'hide_upcoming', false );
          $query->tribe_is_agenda = true;
        }
      }
      $query->tribe_is_event_agenda_query = $agenda_query;
      return $query->tribe_is_event_agenda_query ? apply_filters('tribe_events_agenda_pre_get_posts', $query) : $query;
    }

    function select_page_template( $template ){
      // agenda view
      if( tribe_is_agenda() ) {
        $template = TribeEventsTemplates::getTemplateHierarchy('agenda','','agenda', $this->pluginPath);
        $template = TribeEventsTemplates::getTemplateHierarchy('list');
      }
      return $template;
    }

    /**
     * Check the minimum WP version and if TribeEvents exists
     *
     * @static
     * @param string  $wp_version
     * @return bool Whether the test passed
     */
    public static function prerequisites( $wp_version = null ) {;
      $pass = TRUE;
      $pass = $pass && class_exists( 'TribeEvents' );
      $pass = $pass && version_compare( is_null( $wp_version ) ? get_bloginfo( 'version' ) : $wp_version, self::MIN_WP_VERSION, '>=' );
      return $pass;
    }

    public function fail_notices() {
      printf( '<div class="error"><p>%s</p></div>', sprintf( self::__( '%1$s requires WordPress v%2$s or higher and The Events Calendar v%3$s or higher.' ), self::PLUGIN_NAME, self::MIN_WP_VERSION, self::REQUIRED_TEC_VERSION ) );
    }

    /* Static Singleton Factory Method */
    public static function instance() {
      if ( !isset( self::$instance ) ) {
        $className = __CLASS__;
        self::$instance = new $className;
      }
      return self::$instance;
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
