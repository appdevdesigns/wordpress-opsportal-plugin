(function (window, jQuery) {
    'use strict';

    jQuery(function ($) {
        var $parent = $('.tablenav.top').find('div.alignleft').last();
        var $button = $('<button id="op-bulk-sync" type="submit" title="Sync Now" class="button button-primary wp-hide-pw" value="sync" name="op_bulk_sync"><i class="dashicons dashicons-update"></i> <span>Bulk User Sync</span></button>');
        var html = $('<div class="alignleft actions">').append($button);
        $(html).insertAfter($parent);

        $button.on('click', function (e) {
            $(this).find('span').text('Please wait...');
        })
    });
})(window, jQuery);