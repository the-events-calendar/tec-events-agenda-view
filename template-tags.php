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