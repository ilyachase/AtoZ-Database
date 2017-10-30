$( function () {
	var enteredKeyword;

	$( '#keyword_form' ).submit( function () {
		return false;
	} );

	$( "#input_keyword" ).bind( 'input', function () {
		var keyword = $( this ).val();

		window.clearTimeout( $( this ).data( "timeout" ) );
		$( this ).data( "timeout", setTimeout( function () {
			enteredKeyword = keyword;

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

	$( document ).on( 'click', '#suggested_keywords .list-group-item', function () {
		var $selected_keywords = $( '#selected_keywords' ),
			already_have = false,
			adding_value = $( this ).data( 'value' );

		if ( !adding_value )
			return false;

		$( '.list-group-item', $selected_keywords ).each( function ( k, v ) {
			if ( $( v ).data( 'value' ) === adding_value )
				already_have = true;
		} );

		if ( already_have )
			return;

		$selected_keywords.append( $( '<li/>', {
			'class'     : 'list-group-item',
			'text'      : $( this ).text(),
			'data-value': adding_value
		} ) );
	} );

	$( document ).on( 'click', '#selected_keywords .list-group-item', function () {
		$( this ).remove();
	} );

	function get_selected_keywords()
	{
		var result = [];

		$( '#selected_keywords .list-group-item' ).each( function ( k, v ) {
			result.push( $( v ).data( 'value' ) );
		} );

		return result;
	}

	$( '#update_count' ).click( function () {
		$.ajax( {
			method    : 'post',
			url       : '/ajax/getcount',
			data      : {'keywords': get_selected_keywords()},
			beforeSend: function () {
				$( '#loader' ).show();
			},
			success   : function ( data ) {
				$( '#count' ).text( data );
			},
			complete  : function () {
				$( '#loader' ).hide();
			}
		} );
	} );

	$( '#clear_search' ).click( function () {
		$( '#selected_keywords' ).empty();
		$( '#count' ).text( '0' );

		return false;
	} );

	$( '#search' ).click( function () {
		var queryString = '', $selected_keywords_items = $( '#selected_keywords .list-group-item' );

		if ( $selected_keywords_items.length === 0 )
		{
			alert( 'Select keywords first' );
			return false;
		}

		$selected_keywords_items.each( function ( k, v ) {
			if ( queryString === '' )
				queryString += '?keywords[' + k + ']=';
			else
				queryString += '&keywords[' + k + ']=';

			queryString += $( v ).data( 'value' );
		} );

		queryString += '&keyword=' + enteredKeyword;

		window.location.href = '/site/search' + queryString;
	} );
} );