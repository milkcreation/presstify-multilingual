jQuery(document).ready(function($){
	$( '.tiFyMultilingual_LocaleItemLink' ).on( 
		'click',
		function(e)
		{
			e.preventDefault();
			
			var $link 			= $(this), 
				$closest 		= $link.closest( '.tiFyMultilingual_LocaleItem' ),				
				$row_actions	= $( '.tiFyMultilingual_LocaleItemRowActions', $closest ),
				blog_id 		= $link.data( 'blog_id' ),
				translate_id 	= $link.data( 'post_id' );
				
			if( ! $link.attr( 'href' ) ){
				if( ! translate_id )
					return false;
				
				$.ajax({
					url : 		tify_ajaxurl,
					data :		{ action : 'tiFyMultilingualPostColumn', blog_id : blog_id, post_id : translate_id },
					type : 		'post',
					beforeSend:	function()
					{
						$( '.tiFyMultilingual_LocaleItemSpinner', $closest ).show();
					},
					success : 	function( resp )
					{						
						if( ! resp.success ) {
							$( 'strong', $link ).html( resp.data.error ).addClass( 'error' );
						} else {
							$( 'strong', $link ).html( resp.data.title ).removeClass( 'error' );
							$link.attr( 'href', resp.data.edit_post_link );							
							$row_actions.html( resp.data.actions );
						}
					}
				}).done( function(){
					$( '.tiFyMultilingual_LocaleItemSpinner', $closest ).hide();
				});
			} else {
				window.location.href = $link.attr('href');
			}
		}
	);
	$( '.tiFyMultilingual_LocaleItem' ).hover( 
		function()
		{
			$( '.tiFyMultilingual_LocaleItemRowActions', $(this) ).show();
		},
		function()
		{
			$( '.tiFyMultilingual_LocaleItemRowActions', $(this) ).hide();
		}
	);
});