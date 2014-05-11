<?php

	/**
	 * Agenda view conditional tag
	 *
	 * @return bool
	 */
	function tribe_is_agenda()  {
		global $wp_query;
		return $wp_query->get( 'eventDisplay' ) == 'agenda' ? true : false;
	}

	/**
	 * Get agenda view permalink
	 * 
	 * @param string $set_date
	 * @return string $permalink
	 */
	function tribe_get_agenda_permalink( $set_date = null ){
          $tec = TribeEvents::instance();
          $permalink = get_site_url() . '/' . $tec->rewriteSlug . '/agenda/';
          return $permalink;
	}