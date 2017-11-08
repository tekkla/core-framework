/**
 * Core specific plugins
 * 
 * Uses other plugins, so put this always at the end of this file!
 */
(function( $ ) {

    /**
     * Datepicker plugin bindings
     */
    $.fn.coreDatepicker = function() {

        return this.each(function() {

            var options = $(this).data("datepicker-options") != undefined ? $(this).data("datepicker-options") : {};
            var maskformat = $(this).data("form-mask") != undefined ? $(this).data("form-mask") : "9999-99-99";

            $(this).datetimepicker(options).mask(maskformat).attr('size', maskformat.length);
        });
    };

    /**
     * Bootstrap popover bindings
     */
    $.fn.coreErrorPop = function() {

        return this.each(function(){
            $(this).popover({
                content      : '<span class="text-danger">' + $(this).data('error') + '</span>',
                trigger      : 'hover',
                placement    : 'top',
                html         : true,
                container    : 'body'
            });
        });

    };

}( jQuery ));
