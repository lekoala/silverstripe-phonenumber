

(function ($) {

	function onblur() {
		var $this = $(this);
		var data = {
			number: $(this).val()
		};
		if ($this.data('country')) {
			data.country = $this.data('country');
		}
		if ($this.data('format')) {
			data.format = $this.data('format');
		}
		if ($this.data('countryfield')) {
			var field = $this.parents('form').find('[name=' + $this.data('countryfield') + ']');
			if (field.length && field.val()) {
				data.country = field.val();
			}
		}
		$.get($this.data('remote'), data).success(function (res) {
			if (res.length) {
				$this.val(res);
			}
		});
		return false;
	}

	if ($.entwine) {
		//cms
		$.entwine('ss', function ($) {
			$('.libphonenumber').entwine({
				onfocusout: onblur
			});
		});
	}
	else {
		//if entwine is not loaded
		$('.libphonenumber').on('blur', onblur);
	}

}(jQuery));