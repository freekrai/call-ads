function isEmpty( el ){
	return !$.trim(el.html())
}
$( document ).ready(function() {
	$('a.modalButton').on('click', function(e) {
		var src = $(this).data('src');
		var height = $(this).data('height') || 450;
		var width = $(this).data('width') || 550;
		var title = $(this).data("title") || "Test";
		$("#modalbox h4").text( title );
		$("#modalbox iframe").attr({'src':src});
	});
});