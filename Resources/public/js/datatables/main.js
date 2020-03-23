$(document).ready(function() {
	$.extend(true, $.fn.dataTable.defaults, {
		"aaSorting": []
	});

	$('[data-dtc-grid-datatables]').dtc_grid_datatables();
});
