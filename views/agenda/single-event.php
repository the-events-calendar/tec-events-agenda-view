<?php 
/**
 * Agenda View Single Event
 * This file contains one event in the agenda view
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/agenda/single-event.php
 *
 * @package TribeAgenda
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php 

// Setup an array of venue details for use later in the template
$venue_details = array();

if ($venue_name = tribe_get_meta( 'tribe_event_venue_name' ) ) {
	$venue_details[] = $venue_name;	
}

if ($venue_address = tribe_get_meta( 'tribe_event_venue_address' ) ) {
	$venue_details[] = $venue_address;	
}
// Venue microformats
$has_venue = ( $venue_details ) ? ' vcard': '';
$has_venue_address = ( $venue_address ) ? ' location': '';
?>

<!-- Event Title -->
<div class="agenda-event-heading">
	
	<?php do_action( 'tribe_events_before_the_event_title' ) ?>
	<h2 class="entry-title summary">
		<a class="url" href="<?php echo tribe_get_event_link() ?>" title="<?php the_title() ?>" rel="bookmark"><?php the_title() ?></a>
		<?php if ( $has_venue ) : ?>
			@ <?php echo $venue_name; ?>
		<?php endif; ?>
	</h2>
	<?php do_action( 'tribe_events_after_the_event_title' ) ?>

	<!-- Event Meta -->
	<?php do_action( 'tribe_events_before_the_meta' ) ?>
	<div class="tribe-events-event-meta">
		<h3 class="updated published time-details">
			<?php
			global $post;
			if ( !empty( $post->distance ) ) { ?>
				<strong><?php echo '['. tribe_get_distance_with_unit( $post->distance ) .']'; ?></strong>
			<?php } ?>
		</h3>

		<?php if ( $venue_details ) : ?>
			<!-- Venue Display Info -->
			<div class="tribe-events-venue-details">
				<?php  ?>
			</div> <!-- .tribe-events-venue-details -->
		<?php endif; ?>

	</div><!-- .tribe-events-event-meta -->
	<?php do_action( 'tribe_events_after_the_meta' ) ?>
</div> <!-- .agenda-event-heading -->

<!-- Event Content -->
<?php do_action( 'tribe_events_before_the_content' ) ?>
<div class="tribe-agenda-event-description tribe-content">
	<?php echo tribe_event_featured_image(null, 'large'); ?>
	<?php the_content() ?>
</div><!-- .tribe-agenda-event-description -->
<?php do_action( 'tribe_events_after_the_content' ) ?>
