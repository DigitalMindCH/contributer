jQuery(document).ready(function($) {
	
	
    //when form for saving option is submited
    $( "#profile-form" ).submit(function( event ) {
        $(".message-handler").hide();
        $.ajax({
            type: "POST",
            url: contributer_object.ajaxurl,
            data: $( "#profile-form" ).serialize(),
            success: function(data) {                
                if( data.status ) {
                    $("#contributer-success").html( data.message );
                    $("#contributer-success").show();
                }
                else {
                    $("#contributer-failure").html( data.message );
                    $("#contributer-failure").show();
                }
                $("html, body").animate( { scrollTop: 0 }, "slow" );
            }
        });

        event.preventDefault();
    });
    
    
    
    //when form for saving option is submited
    $( "#contributer-editor" ).submit(function( event ) {
        $(".message-handler").hide();
        
        var ce_data = new FormData();
                
        $('#contributer-editor').find('#action, #title, #cat, #post-content, #vid-url, #tags').each(function(){
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
                    $("#contributer-success").html( data.message );
                    $("#contributer-success").show();
                    post_fields_cleanup();
                }
                else {
                    $("#contributer-failure").html( data.message );
                    $("#contributer-failure").show();
                }
                $("html, body").animate( { scrollTop: 0 }, "slow" );
            }
        });

        event.preventDefault();
    });

    
	
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
    jQuery("#title, #post-content, #featured-image, #tags, #vid-url, #gallery-images").val('');
    jQuery("#standard").attr( 'checked', 'checked' );
    jQuery('#cat').val( '-1' );
    set_tinymce_content()
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

function get_tinymce_content() {
    if ( jQuery("#wp-content-wrap").hasClass("tmce-active")) {
        return tinyMCE.activeEditor.getContent();
    } 
    else {
        return jQuery('#post-content').val();
    }
}

function set_tinymce_content() {
    if ( jQuery("#wp-content-wrap").hasClass("tmce-active")) {
        tinyMCE.activeEditor.setContent('');
    } 
    else {
        jQuery('#post-content').val("");
    } 
}