;(function($) {
	$.fn.Steps = function(opts) {
		_options = $.extend({
			tabsDisabled : true,
			buttonsDisabled: false,
			ready : false,
			validateCallbacks : {
				next : function(stepId, step) { return true; },
				prev : function(stepId, step) { return true; },
				submit : function(stepId, step) { return true; }
			},
			stepLoadCallback : function() { return true; },
			prevStepLock : false,
			nextStepLock : false,
			stepOffset : 700,
			steps : '.steps',
			step : '.step'
		}, opts || {});
	
		return this.each(function() {
			_this = $(this);
			
			_nextButton = $('button.next', _this)
							.live('click', nextStep);
			_prevButton = $('button.prev', _this)
							.live('click', prevStep);
			_submitButton = $('button.submit', _this)
							.live('click', submitSteps);

			_error = $('.error', _this);

			_steps = $(_options.steps, _this);
			_numSteps = $(_options.steps + ' ' + _options.step, _this).size();
			
			setButtons();
			setTabBehavior();
					
			if (window.location.hash.match('step-')) {
				var firstStep = parseInt(window.location.hash.replace('#step-', ''), 10);
				goToStep(firstStep);
			}
		});		
	};
	
	var _currentStep = 1,
		_steps = null,
		_nextButton = null,
		_prevButton = null,
		_submitButton = null,
		_error = null,
		_options,
		_this;

	var toggleError = function(state) {
		if (state === true && !_error.is(':visible')) {
			_error.slideDown();
		}
		else if (state === false && _error.is(':visible')) {
			_error.slideUp();
		}
	};

	var nextStep = function(e) {
		e.preventDefault();
		
		if(_options.prevStepLock) {
			return;
		}
		
		_options.prevStepLock = true;

		if (_options.validateCallbacks.next.apply(_this, [_currentStep, $('.step:eq(' + (_currentStep - 1) + ')', _this)])) {
			var nextStep = _currentStep + 1;
			goToStep(nextStep);
		}
		
		_options.prevStepLock = false;	
	};
	
	var prevStep = function(e) {
		e.preventDefault();
					
		if(_options.nextStepLock) {
			return;
		}

		_options.nextStepLock = true;

		if (_options.validateCallbacks.prev.apply(_this, [_currentStep, $('.step:eq(' + (_currentStep - 1) + ')', _this)])) {
			var nextStep = _currentStep - 1;
			goToStep(nextStep);
		}
		
		_options.nextStepLock = false;
	};
	
	var goToStep = function(step) {
		if (step != _currentStep) {
			step = parseInt(step, 10);
			var left = (step * (_options.stepOffset * -1)) + _options.stepOffset;
			_currentStep = step;
			_options.nextStepLock = _options.prevStepLock = false;
			_steps.animate({'left': left}, 'normal', 'swing', setButtons);
			_options.stepLoadCallback.apply(_this, [_currentStep, $('.step:eq(' + (_currentStep - 1) + ')', _this)]);
		}
	};

	var submitSteps = function(e) {
		e.preventDefault();
		var success = _options.validateCallbacks.submit.apply(_this, [_currentStep, $('.step:eq(' + (_currentStep - 1) + ')', _this)]);
		if (success && _currentStep < _numSteps) {
			var nextStep = _currentStep + 1;
			goToStep(nextStep);
		}
	};

	var setButtons = function() {
		_submitButton.removeClass('loading');
		var thisStep = $('.step:eq(' + (_currentStep - 1) + ')', _this);

		$(':input:visible:first', thisStep).focus();

		if (thisStep.hasClass('next')) {
			_nextButton.prop('disabled', false).show();
		}
		else {
			_nextButton.prop('disabled', true).hide();
		}
		
		if (thisStep.hasClass('prev')) {
			_prevButton.prop('disabled', false).show();
		}
		else {
			_prevButton.prop('disabled', true).hide();
		}
		
		if (thisStep.hasClass('submit')) {
			_submitButton.prop('disabled', false).show();
		}
		else {
			_submitButton.prop('disabled', true).hide();
		}

		_nextStepLock = false;
		_prevStepLock = false;
	};
	
	var disable = function(button, disable, hide) {
		button.prop('disabled', disable);
		if (hide) {
			button.addClass('hide').hide();
		}
		else {
			button.removeClass('hide').show();
		}
	};
	
	var setTabBehavior = function() {
		// 1. prevent a user from tabbing to next field if next
		//    field is located in the next step
		// 2. dynamically assign return key to "next" or "submit"
		//    based on button visibility
		$(window).bind('keydown', function(e) {
			var target = $(e.target),
				key = e.keyPress || e.which;
			
			if (target.parents(_this).size() > 0) {
				switch (key) {
				 	case 9: // tab key
						var stop = false,
							parent = target.closest('.step');
					
						var selector = (e.shiftKey ? ':input:visible:first' : ':input:visible:last');
						if (target.attr('name') == target.closest('.step').find(selector).attr('name')) {
							e.preventDefault();
						}						
						break;
					case 13:
						if (_submitButton.is(':visible')) {
							_submitButton.trigger('click');
							e.preventDefault();
						}
						else if (_nextButton.is(':visible')) {
							_nextButton.trigger('click');
							e.preventDefault();
						}
						break;
				}			
			}
		});
	};
	
	$.fn.Steps.isLastStep = function() {
		return _currentStep == _numSteps;
	};
	
	$.fn.Steps.disablePrev = function(disabled) {
		disable(_prevButton, (disabled === true), (disabled === true));
	};

	$.fn.Steps.disableNext = function(disabled) {
		disable(_nextButton, (disabled === true), (disabled === true));
	};

	$.fn.Steps.lockPrev = function(lock) {
		disable(_prevButton, (lock === true), false);
	};

	$.fn.Steps.lockNext = function(lock) {
		disable(_nextButton, (lock === true), false);
	};
	
	$.fn.Steps.clearError = function() {
		_error.html('');
		if (_error.is(':visible')) {
			toggleError(false);
		}
	};
	
	$.fn.Steps.triggerError = function(message) {
		_error.html(message);
		if (!_error.is(':visible')) {
			toggleError(true);
		}
	};
	
	$.fn.Steps.setButtonLoading = function(button, status) {
		var _button;
		switch (button) {
			case 'next':
				_button = _nextButton;
				break;
			case 'prev':
				_button = _prevButton;
				break;
			case 'submit':
				_button = _submitButton;
				break;
		}
		
		if (status) {
			_button.addClass('loading');
		}
		else {
			_button.removeClass('loading');
		}
	};
})(jQuery);