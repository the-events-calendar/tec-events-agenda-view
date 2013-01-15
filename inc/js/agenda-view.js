jQuery(document).ready(function( $ ) { 
	var agendaHeading = $('.tribe-events-agenda .agenda-event-heading');
			agendaHeading.click(function(){
				eventContent = $(this).parent('div').find('.tribe-agenda-event-description');
				eventContent.slideToggle('fast');
			});	
});	
