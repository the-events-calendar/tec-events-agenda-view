<?php

	/**
	 * Is Agenda View
	 *
	 * @return bool
	 * @since 3.0
	 */
	function tribe_is_agenda()  {
		$is_agenda = (TribeEvents::instance()->displaying == 'agenda') ? true : false;
		return apply_filters('tribe_is_agenda', $is_agenda);
	}

	/**
	 * Get week permalink
	 * 
	 * @param string $week
	 * @return string $permalink
	 * @since 3.0
	 */
	function tribe_get_agenda_permalink( $set_date = null ){
		$tec = TribeEvents::instance();
		$set_date = is_null($set_date) ? '' : date('Y-m-d', strtotime( $set_date ) );
		$permalink = get_site_url() . '/' . $tec->rewriteSlug . '/' . trailingslashit( TribeAgenda::instance()->agendaSlug . '/' . $set_date );
		return apply_filters('tribe_get_agenda_permalink', $permalink);
	}