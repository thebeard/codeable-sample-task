( function( $ ) {

	$( document ).ready( function () {

		if ( $( '#cdbl-test').length ) testTimer = null;
		$( '#cdbl-test [name="id"]' ).keyup( fetchTitle );
		$( '#cdbl-test [name="title"]' ).keyup( updateTitle );

	});

	function updateTitle() {
		var title = $( '#cdbl-test [name="title"]' ).val();
		$( '#cdbl-title' ).html( 
			( title ) ? title : '%title%'
		);
		$( '#cdbl-test [name="title"]' ).focus();
	}

	function updateID() {
		var id = $( '#cdbl-test [name="id"]' ).val();
		$( '#cdbl-id' ).html( 
			( id ) ? id : '%id%'
		);
	}

	function fetchTitle() {
		updateID();
		window.clearTimeout( testTimer );
		testTimer = window.setTimeout( function() {
			$( '#cdbl-test [name="title"]' ).attr( 'disabled', true );
			$.post( uris.ajaxurl, {
				action: "fetch_title",
				id: $( '#cdbl-test [name="id"]' ).val(),
				update_post_title: $( '#update_post_title' ).val(),
			}).done(function( data ) {
				$( '#cdbl-test [name="title"]' ).val( data );
				$( '#cdbl-test [name="title"]' ).removeAttr( 'disabled' );
				updateTitle();
			});
		}, 500 );
	}

} )( jQuery );