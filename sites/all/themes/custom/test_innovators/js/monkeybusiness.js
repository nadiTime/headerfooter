(function($,main,lower,middle,upper,diagnostic) {
	window.onload = function() { 
		if( main && document.URL.match( main ) )
			document.getElementById( 'MB_catalog' ).className = 'MB_show';
		else if( lower && document.URL.match( lower ) )
			document.getElementById( 'MB_lower' ).className = 'MB_show';
		else if( middle && document.URL.match( middle ) )
			document.getElementById( 'MB_middle' ).className = 'MB_show';
		else if( upper && document.URL.match( upper ) )
			document.getElementById( 'MB_upper' ).className = 'MB_show';
		else if( diagnostic && document.URL.match( diagnostic ) ) {
			var fields = document.getElementsByName('MB_show');
			var buttons = document.getElementsByName('MB_button');
			for( var i=0; i<fields.length; i++ ) (function( button, field ) {
				if( document.addEventListener ) {
					field.className = 'MB_hide';
					button.addEventListener( 'click', function() {
						$(field).slideToggle();
					},
					false );
					field.addEventListener( 'click', function() {
						$(field).slideToggle();
					},
					false );
				}
			}( buttons[i], fields[i] ));
		}
	};
	
})(jQuery,'/catalog$','/lower_level_isee_test$','/middle_level_isee_test$','/upper_level_isee_test$','/isee-diagnostic-tool');
