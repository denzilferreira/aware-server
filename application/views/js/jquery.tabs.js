$(document).ready(function(){   	
	$('.tabs').each(function(){
		var $active, $content, $links = $(this).find('a');
		$active = $($links.filter('[href="'+location.hash+'"]')[0] || $links[0]);
		$active.addClass('active');
		$active.parent().addClass('selected')
		$content = $($active.attr('href'));

		$links.not($active).each(function () {
			$($(this).attr('href')).hide();
		});

		// Bind the click event handler
			$(this).on('click', 'a', function(e){
			// Make the old tab inactive.
			$active.removeClass('active');
			$active.parent().removeClass('selected')
			$content.hide();

			// Update the variables with the new link and content
			$active = $(this);
			$content = $($(this).attr('href'));

			// Make the tab active.
			$active.addClass('active');
			$active.parent().addClass('selected')
			$content.show();

			// Prevent the anchor's default click action
			e.preventDefault();
		});
	});
});