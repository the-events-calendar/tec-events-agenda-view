<?php
/**
 * Agenda View Content Template
 * The content template for the agenda view. 
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<div id="tribe-events-content" class="tribe-events-list tribe-events-agenda">

	<!-- Agenda Title -->
	<?php do_action( 'tribe_events_before_the_title' ); ?>
	<h2 class="tribe-events-page-title"><?php echo tribe_get_events_title() ?></h2>
	<?php do_action( 'tribe_events_after_the_title' ); ?>

	<!-- Notices -->
	<?php tribe_events_the_notices() ?>

	<!-- Agenda Header -->
    <?php do_action( 'tribe_events_before_header' ); ?>
	<div id="tribe-events-header" <?php tribe_events_the_header_attributes() ?>>
	</div><!-- #tribe-events-header -->
	<?php do_action( 'tribe_events_after_header' ); ?>

	<!-- Events Loop -->
	<?php if ( have_posts() ) : ?>
		<?php do_action( 'tribe_events_before_loop' ); ?>
		<?php tribe_get_template_part( 'agenda/loop' ) ?>
		<?php do_action( 'tribe_events_after_loop' ); ?>
	<?php endif; ?>

	<!-- Agenda Footer -->
	<?php do_action( 'tribe_events_before_footer' ); ?>
	<div id="tribe-events-footer">

	</div><!-- #tribe-events-footer -->
	<?php do_action( 'tribe_events_after_footer' ) ?>

</div><!-- #tribe-events-content -->
