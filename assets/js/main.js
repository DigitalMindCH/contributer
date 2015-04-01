jQuery(document).ready(function($) {
	
	//when form for saving option is submited
	$( "#profile-form" ).submit(function( event ) {
        
        $.ajax({
            type: "POST",
            url: contributer_object.ajaxurl,
            data: $( "#profile-form" ).serialize(),
            success: function(data) {                
                if( data.status ) {
                    alert( data.message );
                }
                else {
                    alert( data.message );
                }
            }
        });
		
		event.preventDefault();
	});		
	
});



jQuery(document).ready(function($){
    
    $("#profile-image-upload").on("change", function() {
        $("#file_form").submit();
    });
    
    var form_data = {};
    $('#file_form').find('input').each(function(){
        form_data[this.name] = $(this).val();
    });
    
    $('#file_form').ajaxForm({
        url: contributer_object.ajaxurl,
        data: form_data,
        type: 'POST',
        contentType: 'json',
        success: function( data ){
            if ( data.status ) {
                $("#profile-image").attr("src", data.image_url )
            }
            else {
                alert( data.message );
            }
        }
    });
});