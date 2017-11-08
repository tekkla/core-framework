/**
 * Create framework related namespaces
 */
CORE.createNS("FRAMEWORK");
CORE.createNS("FRAMEWORK.STYLE");

// ----------------------------------------------------------------------------
// Function with commands to use on "ready" and after ajax requests
// ----------------------------------------------------------------------------
CORE.FRAMEWORK.ready = function() {
    
    // Bind datepicker
    $('.form-datepicker').coreDatepicker();

    // Bind error popover
    $('.form-control[data-error]').coreErrorPop();
    $('[data-toggle="popover"]').popover();

    // beautifiying xdebug oputput including ajax return values add styling
    // hooks to any XDEBUG output
    $('font>table').addClass('xdebug-error');
    $('font>table *').removeAttr('style').removeAttr('bgcolor');
    $('font>table tr:first-child').addClass('xdebug-error_description');
    $('font>table tr:nth-child(2)').addClass('xdebug-error_callStack');

    // Fade out elements
    var style = CORE.FRAMEWORK.STYLE;
    
    $('.fadeout').delay(style.fadeout_time).slideUp(style.animation_speed, function() {
        $(this).remove();
    });
};

// ----------------------------------------------------------------------------
// Eventhandler "ready"
// ----------------------------------------------------------------------------
$(document).ready(function() {

    // scroll to top button
    $(window).scroll(function() {

        if ($(this).scrollTop() > 100) {
            $('#core-scrolltotop').fadeIn();
        } else {
            $('#core-scrolltotop').fadeOut();
        }
    });
   
    // Register CORE.ready() as a callback after ajax calls
    CORE.AJAX.registerCallback(CORE.FRAMEWORK.ready);

    // Run function with commands to be used on "ready" and "ajaxComplete"
    CORE.FRAMEWORK.ready();
    
});

// ----------------------------------------------------------------------------
//  Add loading class to body tag while doing ajax request
// ----------------------------------------------------------------------------
$(document).ajaxStart(function() {
    $('body').addClass("loading");
});

$(document).ajaxStop(function(event) {
    $('body').removeClass("loading");
});

// ----------------------------------------------------------------------------
// Input|textarea maxlength counter
// ----------------------------------------------------------------------------
$(document).on('keyup input paste', 'textarea[maxlength]', function() {

    if ($(this).data('counter') !== undefined) {

        var limit = parseInt($(this).attr('maxlength'));
        var text = $(this).val();
        var chars = text.length;

        if (chars > limit) {
            $(this).val(text.substr(0, limit));
        }

        var counterid = $(this).data('counter');

        if ($(counterid).length > 0) {
            $(counterid).text(limit - chars);
        }
    }
});

// ----------------------------------------------------------------------------
// Scroll to top click handler
// ----------------------------------------------------------------------------
$(document).on('click', '#core-scrolltotop', function(event) {

    if (navigator.userAgent.match(/(iPod|iPhone|iPad|Android)/)) {
        window.scrollTo(0, 0);
    } else {
        $('html,body').animate({
            scrollTop : 0,
            scrollLeft : 0
        }, 800, function() {
            $('html,body').clearQueue();
        });
    }

    return false;
});

// ----------------------------------------------------------------------------
// Autoclose for collapseable navbar on link click
// ----------------------------------------------------------------------------
$(document).on('click', '.navbar-collapse a', function() {
    $(".navbar-collapse").collapse('hide');
});
