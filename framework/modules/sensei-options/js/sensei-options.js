jQuery(document).ready(function($) {
	
	//tab click (switching tabs)
	$( ".sensei-tab" ).click(function() {
		$( ".sensei-tab" ).removeClass( "tab-selected" );
		$( this ).addClass( "tab-selected" );
		$( ".tab-content" ).hide();
		$( "#tab-content-" + $(this).attr( "id" ) ).show();
	});
	
	$( ".sensei-reset-tab" ).click(function() {
		if( ! confirm('Are you sure? This will reset all options for this tab.') ) {
			return;
		}
		
		var tab_id = $( this ).data('tab');
		
		$.ajax({
            type: 'post',
            cache: false,
            url: ajaxurl,
			data: {
				action: 'reset_options_tab',
				tab_id: tab_id
			},
            success: function(data) {
				if( data.status ) {
					location.reload();
				}
			}
		});
		
	});
	
	$( ".sensei-reset-all" ).click(function() {
		if( ! confirm('Are you sure? This will reset all options.') ) {
			return;
		}
		
		$.ajax({
            type: 'post',
            cache: false,
            url: ajaxurl,
			data: {
				action: 'reset_options_all'
			},
            success: function(data) {
				if( data.status ) {
					location.reload();
				}
			}
		});
	});
	
	//when form for saving option is submited
	$( ".sensei-options-form" ).submit(function( event ) {
		
		//we need to populate manually from visual to textarea (because we are submiting via ajax)
		//ugly hack to be able to use wysiwyg via ajax
		$( ".field-wysiwyg-container" ).each(function( index ) {
			var is_tmce_tab = $( this ).find( ".tmce-active" );
			if( is_tmce_tab.length ) {
				var sensei_wysiwyg_iframe = $(this).find('iframe');
				$(this).find('textarea').val(sensei_wysiwyg_iframe.contents().find("#tinymce").html());
			}
		});
		
		var form_object = $( this );
		form_object.find('.spinner').show();
		form_object.find('.sensei-submit').hide();
		
		$.ajax({
            type: 'post',
            cache: false,
            url: ajaxurl,
			data: $( this ).serialize(),
            success: function(data) {
				form_object.find('.spinner').hide();
				form_object.find('.sensei-submit').show();
				if(data.status) {
					if( data.updated_conditions.length !== 0 ) {
						$.each(data.updated_conditions, function( index, value ) {
							rstate_dependent_options(value);
						});
					}
				}
			}
		});
		
		event.preventDefault();
	});		
	
});

//revert state of fields which depends from specific field
function rstate_dependent_options( field_name ) {
	var sensei_field_container = jQuery( ".dependence-" + field_name );
	
	jQuery( sensei_field_container ).each(function( index ) {
		if( jQuery( this ).hasClass('sensei-option-disabled-mark') ) {
			disable_enable_options( jQuery( this ) );
		}
		else if( jQuery( this ).hasClass('sensei-option-hidden-mark') ) {
			hide_show_options( jQuery( this ) );
		}
	});
}


function disable_enable_options( sensei_field_container ) {
	var sensei_field_blocker = sensei_field_container.find( ".sensei-option-blocker" );
	if(sensei_field_blocker.length) {	
		if( sensei_field_container.hasClass( 'sensei-option-disabled' ) ) {
			sensei_field_blocker.hide( "slow", function() {
				sensei_field_container.removeClass( 'sensei-option-disabled' );
			});
		}
		else {
			sensei_field_blocker.show( "slow", function() {
				sensei_field_container.addClass( 'sensei-option-disabled' );
			});
		}
	}
}


function hide_show_options( sensei_field_container ) {
	if( sensei_field_container.hasClass('sensei-option-hidden') ) {
		sensei_field_container.slideDown( "slow", function() {
			sensei_field_container.removeClass('sensei-option-hidden');
		});
	}
	else {
		sensei_field_container.slideUp( "slow", function() {
			sensei_field_container.addClass('sensei-option-hidden');
		});
	}
}
