;(function($) {
	$.fn.Steps = function(opts) {
		var _currentStep = 1,
			_steps = null,
			_nextButton = null,
			_prevButton = null,
			_submitButton = null,
			_error = null,
			_options,
			_this;
			
		_options = $.extend({
			tabsDisabled : true,
			buttonsDisabled: false,
			ready : false,
			validateCallbacks : {
				next : function() { return true; },
				prev : function() { return true; },
				submit : function() { return true; }
			},
			stepLoadCallback : function() { return true; },
			prevStepLock : false,
			nextStepLock : false,
			stepOffset : 700,
			steps : '.steps',
			step : '.step',
			buttonText : {
				prev : 'Previous',
				next : 'Continue',
				submit : 'Continue to Inbox'
			}
		}, opts || {});
	
		var triggerError = function(message) {
			_error.html(message);
			if (!_error.is(':visible')) {
				toggleError();
			}
		};

		var toggleError = function() {
			_error.slideToggle();
		};
	
		var nextStep = function(e) {
			e.preventDefault();
			
			if(_options.prevStepLock) {
				return;
			}
			
			_options.prevStepLock = true;
			
			if (_options.validateCallbacks.next.apply(_this, [_currentStep, $(_numSteps + ':eq(' + _currentStep + ')', _this)])) {
				var nextStep = _currentStep + 1;
				goToStep(nextStep);
			}			
		};
		
		var prevStep = function(e) {
			e.preventDefault();
						
			if(_options.nextStepLock) {
				return;
			}

			_options.nextStepLock = true;

			if (_options.validateCallbacks.prev.apply(_this, [_currentStep, $(_numSteps + ':eq(' + _currentStep + ')', _this)])) {
				var nextStep = _currentStep - 1;
				goToStep(nextStep);
			}
		};
		
		var goToStep = function(step) {
			if (step != _currentStep) {
				step = parseInt(step, 10);
				var left = (step * (_options.stepOffset * -1)) + _options.stepOffset;
				_steps.animate({'left': left}, 'normal', 'swing', _setButtons);
				_currentStep = step;
				_options.nextStepLock = _options.prevStepLock = false;
				_options.stepLoadCallback.apply(_this, [_currentStep, $(_numSteps + ':eq(' + _currentStep + ')', _this)]);
			}
		};
	
		var submitSteps = function(e) {
			e.preventDefault();
			return _options.validateCallbacks.submit.apply(_this);
		};
	
		var _setButtons = function() {
			_submitButton.removeClass('loading');
			
			var leftPos = $(_options.steps, _this).css('left').replace('px', '');
						
			if(leftPos > (_options.stepOffset * -1)) {
				_prevButton.prop('disabled', true).hide();
			} else if (!_prevButton.hasClass('hide')) {
				_prevButton.prop('disabled', false).show();
			}
				
			if(leftPos == (_options.stepOffset * ((_numSteps -1) * -1))) {
				_nextButton.prop('disabled', true).hide();
			} else if (!_nextButton.hasClass('hide')){
				_nextButton.prop('disabled', false).show();
			}

			if (_currentStep != _numSteps) {
				_submitButton.hide();
			} else {
				_submitButton.show();
			}

			_nextStepLock = false;
			_prevStepLock = false;
		};
		
		var _disable = function(button, disable, hide) {
			button.prop('disabled', disable);
			if (hide) {
				button.addClass('hide').hide();
			}
			else {
				button.removeClass('hide').show();
			}
		};
	
		return this.each(function() {
			_this = $(this);
			
			_nextButton = $('button.next', _this)
							.html(_options.buttonText.next + ' &raquo;')
							.live('click', nextStep);
			_prevButton = $('button.prev', _this)
							.html('&laquo; ' + _options.buttonText.prev)
							.live('click', prevStep);
			_submitButton = $('button.submit', _this)
							.html(_options.buttonText.submit + ' &raquo;')
							.live('click', submitSteps);

			_error = $('.error', _this);
			_steps = $(_options.steps, _this);
			_numSteps = $(_options.steps + ' ' + _options.step, _this).size();
			
			_setButtons();
			
			_this.disablePrev = function(disable) {
				_disable(_prevButton, (disable == true), (disable == true));
			};

			_this.disableNext = function(disable) {
				_disable(_nextButton, (disable == true), (disable == true));
			};

			_this.lockPrev = function(lock) {
				_disable(_prevButton, (lock == true), false);
			};

			_this.lockNext = function(lock) {
				_disable(_nextButton, (lock == true), false);
			};
			
			if (window.location.hash.match('step-')) {
				var firstStep = parseInt(window.location.hash.replace('#step-', ''));
				goToStep(firstStep);
			}
		});
	};
})(jQuery);