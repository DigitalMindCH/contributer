window.fbAsyncInit = function() {
    FB.init({
      appId      : '677795652326648',
      xfbml      : true,
      version    : 'v2.3'
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
        
        /*FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
                var uid = response.authResponse.userID;
                var accessToken = response.authResponse.accessToken;
                alert(accessToken);
            }
        });*/
        
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
                
            }
            else {
                alert( data.message )
            }
        }
    });
}