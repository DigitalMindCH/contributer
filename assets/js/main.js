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
    
    
    
    //when form for saving option is submited
    $( "#contributer-editor" ).submit(function( event ) {

        var ce_data = new FormData();
                
        $('#contributer-editor').find('#action, #title, #cat, #content, #vid-url, #tags').each(function(){
            ce_data.append( this.name, $(this).val() );
        });
        
        ce_data.append( 'post-format', $("input[name=post-format]:checked").val() );
        
        $.each($('#featured-image')[0].files, function(i, file) {
            ce_data.append('featured-image', file);
        });

        $.each($('#gallery-images')[0].files, function(i, file) {
            ce_data.append('gallery-image-'+i, file);
        });

        $.ajax({
            url: contributer_object.ajaxurl,
            data: ce_data,
            type: 'POST',
            cache: false,
            contentType: false,
            processData: false,
            //clearForm: true,
            success: function( data ){
                if( data.status ) {
                    alert( data.message );
                    post_fields_cleanup();
                }
                else {
                    alert( data.message );
                }
            }
        });

        event.preventDefault();
    });
 
        
    //when form for saving post is submited
    //handling upload of profile image (nr)

    
	
    //submit on profile image change
    $("#profile-image-upload").on("change", function() {
        $("#file_form").submit();
    });	
    //handling upload of profile image (nr)
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


function post_fields_cleanup() {
	
}


jQuery(document).ready( function($) {
    var standard = $('.contributer-editor input[type="radio"]'),
	featimg = $('.contributer-editor #feat-img-field');

    show_hide(['gallery-field','video-field']);
    
    standard.on('change', function(){
        switch($(this).val()) {
            case 'standard':
                show_hide(['gallery-field','video-field']);
                break;
            case 'image':
                show_hide(['gallery-field','video-field']);
                // also: make featured image required
                break;
            case 'video':
                show_hide(['gallery-field']);
                break;
            case 'gallery':
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