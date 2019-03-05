$(document).on('change', '.checkall', function(){
    var table = $(this).closest('table');
    table.find("input[type=checkbox]").prop('checked', $(this).prop('checked'));
});
$(document).ready(function(){
	$("[data-toggle=tooltip]").tooltip();
	$('.selectpicker').selectpicker();
});
