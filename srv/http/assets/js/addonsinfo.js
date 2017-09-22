function info( option ) {
	// reset to default
	$( '#infoIcon' ).html( '<i class="fa fa-question-circle fa-lg">' );
	$( '#infoTitle' ).html( 'Information' );
	$( '#infoTextLabel, #infoPasswordLabel, #infoSelectLabel' ).empty();
	$( '#infoRadio, #infoCheckbox, #infoSelectbox' ).empty();
	$( '.infoBox' ).width( 200 );
	$( '.info, #infoCancel' ).hide();
	$( '#infoOk' ).html( 'Ok' );
	$( '#infoCancel' ).html( 'Cancel' )

	// simple use as info('message')
	if ( typeof option != 'object' ) {
		$( '#infoOk' ).off( 'click' ).on( 'click', function () {
			$( '#infoOverlay' ).hide();
		});
		$( '#infoMessage' ).html( option ).show();
	} else {
		// option use as info({x: 'x', y: 'y'})
		var icon = option[ 'icon' ];
		var title = option[ 'title' ];
		var message = option[ 'message' ];
		var textlabel = option[ 'textlabel' ];
		var passwordlabel = option[ 'passwordlabel' ];
		var radiohtml = option[ 'radiohtml' ];
		var checkboxhtml = option[ 'checkboxhtml' ];
		var selectlabel = option[ 'selectlabel' ];
		var selecthtml = option[ 'selecthtml' ];
		var selectvalue = option[ 'selectvalue' ];
		var boxwidth = option[ 'boxwidth' ];
		var ok = option[ 'ok' ];
		var oklabel = option[ 'oklabel' ];
		var okcolor = option[ 'okcolor' ];
		var cancel = option[ 'cancel' ];
		var cancellabel = option[ 'cancellabel' ];
		
		if ( icon ) $( '#infoIcon' ).html( icon );
		if ( title ) $( '#infoTitle' ).html( title );
		if ( message ) {
			$( '#infoMessage' ).html( message ).show();
			var infofocus = $( '#infoOk' );
		}
		if ( textlabel ) {
			$( '#infoTextLabel' ).html( textlabel +' ' );
			$( '#infoText' ).show();
			var infofocus = $( '#infoTextbox' );
		}
		if ( passwordlabel ) {
			$( '#infoPasswordLabel' ).html( passwordlabel +' ' );
			$( '#infoPassword' ).show();
			var infofocus = $( '#infoPasswordbox' );
		}
		if ( radiohtml ) setboxwidth( $( '#infoRadio' ), radiohtml );
		if ( checkboxhtml ) setboxwidth( $( '#infoCheckbox' ), checkboxhtml );
		if ( selecthtml ) {
			$( '#infoSelectLabel' ).html( selectlabel +' ' );
			$( '#infoSelectbox' ).html( selecthtml );
			$( '#infoSelect' ).show();
		}
		if ( boxwidth ) $( '.infoBox' ).width( boxwidth );
		
		if ( ok ) {
			$( '#infoOk' ).off( 'click' ).on( 'click', function() {
				$('#infoOverlay').hide();
				(typeof ok === 'function') && ok();
			} );
		} else {
			$( '#infoOk' ).off( 'click' ).on( 'click', function() {
				$( '#infoOverlay' ).hide();
			} );
		}
		if ( oklabel ) $( '#infoOk' ).html( oklabel );
		if ( okcolor ) $( '#infoOk' ).css( 'background', okcolor );
		if ( cancel ) {
			$( '#infoCancel' ).show();
			$( '#infoCancel' ).off( 'click' ).on( 'click', function() {
				$( '#infoOverlay' ).hide();
				( typeof cancel === 'function' ) && cancel();
			});
		}
		if ( cancellabel ) $( '#infoCancel' ).html( cancellabel );
	}
	
	$( '#infoOverlay' ).show();
	if ( infofocus ) infofocus.focus();
	
	$( '#infoOverlay' ).keypress( function(e) {
		if ( e.which == 13 ) {
//			$('#infoOverlay').hide();
		}
	} );
	$( '#infoX' ).click( function() {
		$( '#infoOverlay' ).hide();
		$( '#infoTextbox, #infoPasswordbox' ).val( '' );
	} );
	
}

function setboxwidth( box, html ) {
	var contentW = $( '#infoBox' ).width();
	var maxW = 0;
	var spanW = 0;
	$( '#infoBox' ).css('left', '-100%' );                  // move out of screen
	box.html( html ).show();             // show to get width
	setTimeout( function() {                                // wait for radiohtml ready
		box.find( 'span' ).each( function() { // get max width
				spanW = $( this ).width();
				maxW = ( spanW > maxW ) ? spanW : maxW;
		} );
		var pad = ( contentW - 15 - maxW ) / 2;            // 15 = button width
		box.css('padding-left', pad +'px');  // set padding-left
		$( '#infoBox' ).css('left', '50%' );               // move back
	}, 100);
}

function verifypassword( msg, pwd, fn ) {
	$( '#infoPasswordbox' ).val( '' );
	info( {
		message:     msg,
		passwordlabel: 'Retype password',
		ok:          function() {
			if ( $( '#infoPasswordbox' ).val() !== pwd ) {
				info( {
					message: 'Passwords not matched. Please try again.',
					ok:      function() {
						verifypassword( msg, pwd, fn )
					}
				} );
			} else {
				fn()
			}
		}
	} );
}