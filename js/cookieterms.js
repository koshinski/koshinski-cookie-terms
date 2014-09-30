jQuery(document).ready(function($){
	if( 'accept' !== $.cookie('cookie-terms') ){
		$('body').prepend(
			'<div class="cookie-terms">' + cookie_terms.msg +
				'<div class="btn-group">' +
					'<button class="btn btn-primary btn-xs" id="cookie-terms-accept">' + cookie_terms.label_button_ok + '</button>' + ( ( cookie_terms.label_button_more != 0 ) ? '<a class="btn btn-default btn-xs" href="' + cookie_terms.more_url + '">' + cookie_terms.label_button_more + '</a>' : '' ) +
				'</div>' +
			'</div>'
		);
		
		$(document).on('click', '#cookie-terms-accept', function(e){
			e.preventDefault();
			$.cookie( 'cookie-terms', 'accept', { expires: 14 } );
			$('.cookie-terms').fadeOut(250, function(){
				$(this).remove();
			});
		});
	}
	
});
