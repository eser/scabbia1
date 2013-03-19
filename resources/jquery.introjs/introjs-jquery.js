// Generated by CoffeeScript 1.6.1
(function() {

  (function($) {
    var $base, methods, _addOverlay, _buildHelper, _currentStep, _defaultOptions, _exitIntro, _nextStep, _options, _placeTooltip, _previousStep, _showElement, _startIntro, _steps, _updatePosition;
    $base = this;
    _currentStep = null;
    _steps = [];
    _options = {};
    _defaultOptions = {
      skipButtonLabel: 'Skip',
      nextButtonLabel: 'Next &rarr;',
      prevButtonLabel: '&larr; Prev',
      startAt: 0,
      autoStart: true,
      useKeyboard: true,
      autoScroll: true,
      onNextStep: null,
      onPrevStep: null,
      onClose: null,
      onOpen: null,
      onSkip: null,
      onComplete: null
    };
    methods = {
      init: function(options) {
        _options = $.extend(_defaultOptions, options);
        _currentStep = null;
        _steps = [];
        this.each(function(index) {
          return _steps.push($(this));
        });
        _steps.sort(function(a, b) {
          return parseInt(a.attr('data-intro-step') || 0) - parseInt(b.attr('data-intro-step') || 0);
        });
        if (_options.autoStart) {
          return _startIntro();
        }
      },
      start: function() {
        return _startIntro();
      },
      close: function() {
        return _exitIntro();
      }
    };
    _startIntro = function() {
      _addOverlay();
      _currentStep = _options.startAt;
      _showElement();
      if (_options.useKeyboard) {
        $('body').on('keydown', function(event) {
          switch (event.which) {
            case 27:
              if (_options.onSkip != null) {
                _options.onSkip();
              }
              return _exitIntro();
            case 37:
              return _previousStep();
            case 39:
            case 13:
              return _nextStep();
          }
        });
      }
      if (_options.onOpen != null) {
        return _options.onOpen();
      }
    };
    _addOverlay = function() {
      var $overlay;
      $overlay = $('<div class="jq-intro-overlay"/>');
      $overlay.on('click', function(event) {
        event.preventDefault();
        return _exitIntro();
      });
      $overlay.hide();
      $overlay.appendTo($('body'));
      return $overlay.fadeIn(300);
    };
    _exitIntro = function() {
      var $overlay;
      $overlay = $('.jq-intro-overlay');
      $overlay.fadeOut(300, function() {
        return $(this).remove();
      });
      $('.jq-intro-helperLayer').remove();
      $('.jq-intro-showElement').removeClass('jq-intro-showElement');
      if (_options.useKeyboard) {
        $('body').off('keydown');
      }
      if (_options.onClose != null) {
        return _options.onClose();
      }
    };
    _nextStep = function() {
      _currentStep = _currentStep === null ? _options.startAt : _currentStep + 1;
      if (_steps.length <= _currentStep) {
        _exitIntro();
        if (_options.onComplete != null) {
          return _options.onComplete();
        }
      } else {
        _showElement();
        if (_options.onNextStep != null) {
          return _options.onNextStep();
        }
      }
    };
    _previousStep = function() {
      if (_currentStep > 0) {
        _currentStep--;
        _showElement();
        if (_options.onPrevStep != null) {
          return _options.onPrevStep();
        }
      }
    };
    _showElement = function() {
      var $arrow, $el, $helperLayer, $stepNumber, $tooltip, $tooltipContent;
      $el = _steps[_currentStep];
      $helperLayer = $('.jq-intro-helperLayer').length > 0 ? $('.jq-intro-helperLayer') : _buildHelper();
      $helperLayer.width($el.outerWidth() + 10);
      $helperLayer.height($el.outerHeight() + 10);
      $helperLayer.css('top', "" + ($el.offset().top - 5) + "px").css('left', "" + ($el.offset().left - 5) + "px");
      $stepNumber = $helperLayer.find('.jq-intro-helperNumberLayer');
      $tooltipContent = $helperLayer.find('.jq-intro-tooltiptext');
      $arrow = $helperLayer.find('.jq-intro-arrow');
      $tooltip = $helperLayer.find('.jq-intro-tooltip');
      $tooltip.css('height', '').css('width', '');
      if ($el.attr('data-intro-height')) {
        $tooltip.height(parseInt($el.attr('data-intro-height')));
      }
      if ($el.attr('data-intro-width')) {
        $tooltip.width(parseInt($el.attr('data-intro-width')));
      }
      $stepNumber.html($el.attr('data-intro-step') || _currentStep);
      $tooltipContent.html($el.attr('data-intro-content'));
      $('.jq-intro-showElement').removeClass('jq-intro-showElement');
      setTimeout(function() {
        return $el.addClass('jq-intro-showElement');
      }, 300);
      if (!($('.jq-intro-helperLayer').length > 0)) {
        $('body').append($helperLayer);
      }
      _placeTooltip();
      _updatePosition();
      if (_options.autoScroll) {
        return $('body').scrollTop($helperLayer.offset().top);
      }
    };
    _buildHelper = function() {
      var $arrow, $helperLayer, $nextButton, $prevButton, $skipButton, $stepNumber, $tooltip, $tooltipButtons, $tooltipContent;
      $helperLayer = $('<div class="jq-intro-helperLayer"/>');
      $stepNumber = $('<span class="jq-intro-helperNumberLayer"/>');
      $tooltip = $('<div class="jq-intro-tooltip"/>');
      $tooltipContent = $('<div class="jq-intro-tooltiptext"/>');
      $arrow = $('<div class="jq-intro-arrow"/>');
      $tooltipButtons = $('<div class="jq-intro-tooltipbuttons"/>');
      $skipButton = $('<a class="jq-intro-skipbutton"/>');
      $prevButton = $('<a class="jq-intro-prevbutton"/>');
      $nextButton = $('<a class="jq-intro-nextbutton"/>');
      $skipButton.on('click', function(event) {
        if (_options.onSkip != null) {
          _options.onSkip();
        }
        _exitIntro();
        return event.preventDefault();
      });
      $skipButton.html(_options.skipButtonLabel);
      $prevButton.on('click', function(event) {
        _previousStep();
        return event.preventDefault();
      });
      $prevButton.html(_options.prevButtonLabel);
      $nextButton.on('click', function(event) {
        _nextStep();
        return event.preventDefault();
      });
      $nextButton.html(_options.nextButtonLabel);
      $tooltipButtons.append($skipButton);
      $tooltipButtons.append($prevButton);
      $tooltipButtons.append($nextButton);
      $tooltip.append($tooltipContent);
      $tooltip.append($arrow);
      $tooltip.append($tooltipButtons);
      $tooltip.append($stepNumber);
      $helperLayer.append($tooltip);
      return $helperLayer;
    };
    _placeTooltip = function() {
      var $arrow, $el, $tooltip;
      $el = _steps[_currentStep];
      $tooltip = $('.jq-intro-tooltip');
      $arrow = $('.jq-intro-arrow');
      $arrow.removeClass('top right bottom left');
      $tooltip.css('top', '').css('right', '').css('bottom', '').css('left', '');
      switch ($el.attr('data-intro-position')) {
        case 'top':
          $tooltip.css('top', "-" + ($tooltip.outerHeight() + 10) + "px");
          return $arrow.addClass('bottom');
        case 'right':
          $tooltip.css('right', "-" + ($tooltip.outerWidth() + 10) + "px");
          return $arrow.addClass('left');
        case 'left':
          $tooltip.css('left', "-" + ($tooltip.outerWidth() + 10) + "px");
          return $arrow.addClass('right');
        default:
          $tooltip.css('bottom', "-" + ($tooltip.outerHeight() + 10) + "px");
          return $arrow.addClass('top');
      }
    };
    _updatePosition = function() {
      var $el, $helperLayer;
      $el = _steps[_currentStep];
      $helperLayer = $('.jq-intro-helperLayer');
      if ($helperLayer.length > 0) {
        $helperLayer.width($el.outerWidth() + 10);
        $helperLayer.height($el.outerHeight() + 10);
        $helperLayer.css('top', "" + ($el.offset().top - 5) + "px").css('left', "" + ($el.offset().left - 5) + "px");
        _placeTooltip();
        return setTimeout(_updatePosition, 300);
      }
    };
    return $.fn.intro = function(method) {
      if (methods[method]) {
        return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
      } else if (typeof method === "object" || !method) {
        return methods.init.apply(this, arguments);
      } else {
        return $.error("Method " + method + " does not exist on IntroJs-jQuery");
      }
    };
  })(jQuery);

}).call(this);
