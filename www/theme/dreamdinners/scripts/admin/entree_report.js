
$('.session_type_filter').click(function() {
	if($(this).attr("name") == 'session_type_all'){
		//clear others
		$('.session_type_filter').prop( "checked", false );
		$('#session_type_all').prop( "checked", true );
	}else{
		//clear All
		$('#session_type_all').prop( "checked", false );
	}
});
