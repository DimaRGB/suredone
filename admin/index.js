jQuery(function($) {
	$('#gravatar-test')
		.on('submit', function(e) {
			e.preventDefault();
			var form = $(this).get(0);

			$.ajax({
				type: form.method,
				url: form.action,
				data: $(form).serialize(),
				success: function(url) {
					// console.log(url);
					$(form).append('<img src=' + url + ' />');
				},
				error: function(error) {
					console.log(error);
					alert('error');
				},
			});
		})
});
