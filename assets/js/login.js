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


jQuery(document).ready(function($) { 
    
    $("#face-button").click(function () {
        
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
        }
    });
}