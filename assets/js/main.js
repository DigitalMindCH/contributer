jQuery(document).ready(function($) {
	
	//when form for saving option is submited
	$( "#profile-form" ).submit(function( event ) {
        
        $.ajax({
            type: "POST",
            url: contributer_object.ajaxurl,
            data: $( "#profile-form" ).serialize(),
            success: function(data) {                
                if( data.status ) {
                    alert(data.message)
                }
                else {
					
                }
            }
        });
		
		event.preventDefault();
	});		
	
});