/**
 * Thanks to Kenneth Truyers for his idea of how to implement namespaces in javascript
 *
 * @author https://www.kenneth-truyers.net/about-kenneth-truyers/
 * @see https://www.kenneth-truyers.net/2013/04/27/javascript-namespaces-and-modules/
 */

// create the root namespace and making sure we're not overwriting it
var CORE = CORE || {};

// create a general purpose namespace method
// this will allow us to create namespace a bit easier
CORE.createNS = function(namespace) {

    var nsparts = namespace.split(".");
    var parent = CORE;

    // we want to be able to include or exclude the root namespace
    // So we strip it if it's in the namespace
    if (nsparts[0] === "CORE") {
        nsparts = nsparts.slice(1);
    }

    // loop through the parts and create
    // a nested namespace if necessary
    for (var i = 0; i < nsparts.length; i++) {
        var partname = nsparts[i];
        // check if the current parent already has
        // the namespace declared, if not create it
        if (typeof parent[partname] === "undefined") {
            parent[partname] = {};
        }
        // get a reference to the deepest element
        // in the hierarchy so far
        parent = parent[partname];
    }
    // the parent is now completely constructed
    // with empty namespaces and can be used.
    return parent;
};

// ----------------------------------------------------------------------------
// Function with commands to use on "ready" and after ajax requests
// ----------------------------------------------------------------------------
CORE.readyAndAjax = function() {

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
    $('.fadeout').delay(fadeout_time).slideUp(800, function() {
        $(this).remove();
    });

    $(".sortable tbody").sortable({
        axis : 'y',
        update : function(event, ui) {
            if ($(this).data('url') !== undefined) {

                var ajaxOptions = {
                    data : $(this).sortable('serialize'),
                    type : 'POST',
                    url : $(this).data('url')
                };

                if (CORE.AJAX.handler !== undefined) {
                    var ajax = new CORE.AJAX.handler();
                    ajax.process(ajaxOptions);
                }
            }
        }
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

    // Run function with commands to be used on "ready" and "ajaxComplete"
    CORE.readyAndAjax();

});

// ----------------------------------------------------------------------------
// Ajax eventhandler
// ----------------------------------------------------------------------------
$(document).ajaxStart(function() {
    // Show loading circle on ajax loads
    // $('body').addClass("loading");
});

$(document).ajaxStop(function(event) {
    // Hide loading circle
    // $('body').removeClass("loading");
});

$(document).ajaxComplete(function() {
    CORE.readyAndAjax();
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
// ClickHandler for back button
// ----------------------------------------------------------------------------
$(document).on('click', '.btn-back', function(event) {
    document.history.go(-1);
});

// ----------------------------------------------------------------------------
// WIP: Backbutton on ajax requests
// ----------------------------------------------------------------------------
$(window).on("popstate", function(e) {
    if (e.originalEvent.state !== null) {
        location.href = location.href;
    }
});

// ----------------------------------------------------------------------------
// Autoclose for collapseable navbar on link click
// ----------------------------------------------------------------------------
$(document).on('click', '.navbar-collapse a', function() {
    $(".navbar-collapse").collapse('hide');
});
