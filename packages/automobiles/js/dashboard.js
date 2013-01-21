/*** drag-n-drop sorting ***/
$(document).ready(function() {
	$('.sortable-container table tbody').sortable({
		handle: '.sortable-handle',
		axis: 'y',
		containment: '.sortable-container', //contain to a wrapper div (not the <tbody> itself), so there's room for dropping at top and bottom of list
		helper: sortableHelper, //prevent cell widths from collapsing while dragging (see http://www.foliotek.com/devblog/make-table-rows-sortable-using-jquery-ui-sortable/ )
		stop: sortableStop, //save data back to server
		cursor: 'move'
	}).disableSelection();
	
	function sortableHelper(event, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	}
	
	function sortableStop(event, ui) {
		var ids = [];
		ui.item.closest('table').find('tbody tr').each(function() {
			ids.push($(this).attr('data-sortable-id'));
		});
		
		var url = $('.sortable-container').attr('data-sortable-save-url');
		var token = $('.sortable-container').attr('data-sortable-save-token');
		data = {
			'ids': ids.join(),
			'ccm_token': token
		};
		$.post(url, data);
	}
});


/*** Body Types dropdown filter on cars/view page ***/
$(document).ready(function() {
	$('.body-type-filter input[type="submit"]').hide();
	$('.body-type-filter select').on('change', function() {
		var $form = $(this).closest('form');
		$form.find('.loading-indicator').show();
		$form.trigger('submit');
	});
});
