$( function () {
	$( "#input_keyword" ).bind( 'input', function () {
		var keyword = $( this ).val();

		window.clearTimeout( $( this ).data( "timeout" ) );
		$( this ).data( "timeout", setTimeout( function () {
			$.ajax( {
				url       : '/ajax/keywordautocomplete',
				data      : {'keyword': keyword},
				beforeSend: function () {
					$( '#loader' ).show();
				},
				success   : function ( data ) {
					var $suggested_keywords = $( '#suggested_keywords' );
					$suggested_keywords.empty();

					$.each( data, function ( k, v ) {
						$suggested_keywords.append( $( '<li/>', {
							'class'     : 'list-group-item',
							'text'      : v.display,
							'data-value': v.value
						} ) );
					} );
				},
				complete  : function () {
					$( '#loader' ).hide();
				}
			} );
		}, 750 ) );
	} );
} );