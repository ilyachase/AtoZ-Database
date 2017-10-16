$( function () {
	function checkPageButtonsState()
	{
		var $currentPage = $( '#current_page' );

		$( '#prev_page' ).attr( 'disabled', parseInt( $currentPage.val() ) === 1 );
		$( '#next_page' ).attr( 'disabled', parseInt( $currentPage.val() ) === parseInt( $( '#total_pages' ).text() ) );
	}

	function changePage( toPage )
	{console.log(toPage);
		var queryKeywords = $.url( '?keywords', window.location.href ),
			finalQuery = {};

		$.each( queryKeywords, function ( k, v ) {
			finalQuery['keywords[' + k + ']'] = v;
		} );

		finalQuery['page'] = toPage;

		window.location.href = '/site/search?' + decodeURIComponent( $.param( finalQuery ) );
	}

	$( '#prev_page' ).click( function () {
		changePage( parseInt( $( '#current_page' ).val() ) - 1 );
	} );

	$( '#next_page' ).click( function () {
		changePage( parseInt( $( '#current_page' ).val() ) + 1 );
	} );

	$( '#page_form' ).submit( function () {
		var toPage = parseInt( $( '#current_page' ).val() );

		if ( toPage > parseInt( $( '#total_pages' ).text() ) || toPage < 1 )
		{
			alert( toPage + ' is not a proper page' );
			return false;
		}

		changePage( toPage );

		return false;
	} );

	checkPageButtonsState();
} );