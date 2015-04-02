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


jQuery(document).ready( function($) {
    var standard = $('.contributer-editor input[type="radio"]'),
        featimg = $('.contributer-editor #feat-img-field');

    show_hide(['gallery-field','video-field']);
    
    standard.on('change', function(){
        switch($(this).val()) {
            case 'Standard':
                show_hide(['gallery-field','video-field']);
                break;
            case 'Image':
                show_hide(['gallery-field','video-field']);
                // also: make featured image required
                break;
            case 'Video':
                show_hide(['gallery-field']);
                break;
            case 'Gallery':
                show_hide(['video-field']);
                break;
            default:
                show_hide(['gallery-field','video-field']);
        }       
    });
    
});

function show_hide( trigger ){
    jQuery('.field').each(function(){
        if( jQuery.inArray( jQuery(this).attr('id'), trigger ) !== -1 ){
            jQuery(this).slideUp();
        }
        else{
            jQuery(this).slideDown();
        }
    });
}