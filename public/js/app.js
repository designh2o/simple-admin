$(document).ready(function(){
	$("#mytable #checkall").click(function () {
		if ($("#mytable #checkall").is(':checked')) {
			$("#mytable input[type=checkbox]").each(function () {
				$(this).prop("checked", true);
			});

		} else {
			$("#mytable input[type=checkbox]").each(function () {
				$(this).prop("checked", false);
			});
		}
	});

	$("[data-toggle=tooltip]").tooltip();
});


$(document).on('submit', '#create_form', function(e){
	e.preventDefault();
	$.ajax({
		url: $(this).attr('action'),
		method: $(this).attr('method'),
		data: $(this).serialize(),
	}).done(function(response){
		alert(response);
	})
});
$(document).on('submit', '#update_form', function(e){
	e.preventDefault();
	$.ajax({
		url: $(this).attr('action'),
		method: $(this).attr('method'),
		data: $(this).serialize(),
	}).done(function(response){
		alert(response);
	})
});
$(document).on('submit', '#delete_form', function(e){
	e.preventDefault();
	$.ajax({
		url: $(this).attr('action'),
		method: $(this).attr('method'),
		data: $(this).serialize(),
	}).done(function(response){
		alert(response);
	})
});