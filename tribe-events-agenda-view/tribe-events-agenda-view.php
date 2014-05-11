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

if ( ! class_exists( 'TribeEvents' ) )
	return;

/* Include our other files */
require_once( 'template-tags.php' );
require_once( 'tribe-agenda-view-template-class.php' );

/* Add Hooks */

// hook in to add the rewrite rules
add_action( 'generate_rewrite_rules', 'tribe_events_agenda_add_routes' );

// specify the template class
add_filter( 'tribe_events_current_template_class', 'tribe_events_agenda_setup_template_class' );

// load the proper template for agenda view
add_filter( 'tribe_events_current_view_template', 'tribe_events_agenda_setup_view_template' );

// inject agenda view into events bar & (display) settings
add_filter( 'tribe-events-bar-views', 'tribe_events_agenda_setup_in_bar', 40, 1 );

/**
 * Add the agenda view rewrite rule
 *
 * @param $wp_rewrite the WordPress rewrite rules object
 * @return void
 **/
function tribe_events_agenda_add_routes( $wp_rewrite ) {

	// Get the instance of the TribeEvents plugin, and the rewriteSlug that the plugin uses
	$tec = TribeEvents::instance();
	$tec_rewrite_slug = trailingslashit( $tec->rewriteSlug );

	// create new rule for the agenda view
	$newRules = array(
		$tec_rewrite_slug . 'agenda/?$' => 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=agenda',
	);

     // Add the new rule to the global rewrite rules array
	$wp_rewrite->rules = $newRules + $wp_rewrite->rules;
}

/**
 * Specify the template class for agenda view
 *
 * @param $class string containing the current template classname
 * @return string
 **/
function tribe_events_agenda_setup_template_class( $class ) {
	if ( tribe_is_agenda() ) {
		$class = 'Tribe_Events_Agenda_Template';
	}
	return $class;
}

/**
 * Specify the template for agenda view
 *
 * @param $template string containing the current template file
 * @return string
 **/
function tribe_events_agenda_setup_view_template( $template ){
	// agenda view
	if( tribe_is_agenda() ) {
		$template = TribeEventsTemplates::getTemplateHierarchy('agenda');
	}
	return $template;
}


/**
 * Register the Agenda view alongside the other views
 *
 * @param $views array of registered views
 * @return array
 **/
function tribe_events_agenda_setup_in_bar( $views ) {
     $views[] = array( 
          'displaying' => 'agenda',
          'anchor'     => 'Agenda',
          'url'        => tribe_get_agenda_permalink() 
     );
     return $views;
}