window.fbAsyncInit = function() {
    FB.init({
      appId      : contributer_object.facebook_app_id,
      xfbml      : true,
      version    : 'v2.3',
      cookie:true,    
      oauth : true
    });
};

(function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));


(function() {
    var po = document.createElement('script');
    po.type = 'text/javascript'; po.async = true;
    po.src = 'https://plus.google.com/js/client:plusone.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(po, s);
})();


jQuery(document).ready(function($) { 
    
    $("#face-button").click(function () {
        $("#login-loader").removeClass('hidden_loader');
        //login
        FB.login(function ( response ) {
            if ( response.status === 'connected' ) {
                
                FB.api('/me', function( data ) {
                    console.log( data );
                    if ( data.email == null ) {
                        alert( "You must allow us to access your email. Please edit your app settings and try again." ); 
                    }
                    else {
                          facebook_login( response.authResponse.accessToken );
                    }
                });
            } 
        }, {scope: 'public_profile,email'});
    });
    
    $( "#email-sign-in" ).submit(function( event ) {
        $("#login-loader").removeClass('hidden_loader');
        $.ajax({
            type: "POST",
            url: contributer_object.ajaxurl,
            data: $( "#email-sign-in" ).serialize(),
            success: function(data) {                
                if( data.status ) {
                    window.location.replace( contributer_object.redirect_login_url );
                }
                else {
                    alert( data.message );
                    $("#login-loader").addClass('hidden_loader');
                }
            }
        });

        event.preventDefault();
    });
    
    $( "#email-sign-up" ).submit(function( event ) {
        $("#login-loader").removeClass('hidden_loader');
        $.ajax({
            type: "POST",
            url: contributer_object.ajaxurl,
            data: $( "#email-sign-up" ).serialize(),
            success: function(data) {               
                if( data.status ) {
                    window.location.replace( contributer_object.redirect_login_url );
                }
                else {
                    alert( data.message );
                    $("#login-loader").addClass('hidden_loader');
                }
            }
        });

        event.preventDefault();
    });

    $('.signup-container').hide();
    $('.signlink').click(function(){
        $('.signup-container').slideToggle('swing');
        $('.login-container').slideToggle('swing');
    });
});


function facebook_login( accessToken  ) {
    jQuery.ajax({
        type: 'post',
        cache:  false,
        url: contributer_object.ajaxurl, 
        data:{
            action: "facebook_login",
            access_token: accessToken
        }, 
        success: function (data) {
            if ( data.status ) {
                window.location.replace( contributer_object.redirect_login_url );
            }
            else {
                alert( data.message )
            }
            $("#login-loader").addClass('hidden_loader');
        }
    });
}
