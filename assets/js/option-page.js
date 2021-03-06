(function (window, document, Math, jQuery) {
    'use strict';
    //Note: All events are namespaced

    //Get requested tab from url
    var requestedTab = window.location.hash.replace('#top#', '');

    jQuery(function ($) {
        //Cache DOM elements for later use
        var $gaTabs = $('h2#op-tabs'),
            $input = $("form#op-form").find('input:hidden[name="_wp_http_referer"]'),
            $sections = $('section.tab-content');

        //If there no active tab found, set first tab as active
        if (requestedTab == '' || $('#' + requestedTab).length == 0) requestedTab = $sections.attr('id');
        //Set that tab active,
        //Notice: we are not using cached DOM in next line
        $('#' + requestedTab).addClass('active');
        //Set related tab content active
        $('#' + requestedTab + '-tab').addClass('nav-tab-active');
        //Set return tab on page load
        setRedirectURL(requestedTab);

        //Bind a click event to all tabs
        $gaTabs.find('a.nav-tab').on('click.op', (function (e) {
            e.stopPropagation();
            //Hide all tabs
            $gaTabs.find('a.nav-tab').removeClass('nav-tab-active');
            $sections.removeClass('active');
            //Activate only clicked tab
            var id = $(this).attr('id').replace('-tab', '');
            $('#' + id).addClass('active');
            $(this).addClass('nav-tab-active');
            //Set return tab url
            setRedirectURL(id);
        }));

        /**
         * Set redirect url into form's input:hidden
         * Note: Using hardcoded plugin option page slug
         * @param url String
         */
        function setRedirectURL(url) {
            var split = $input.val().split('?', 1);
            //Update the tab id in last while keeping base url same
            $input.val(split[0] + '?page=ops_portal#top#' + url);
        }

        /**
         * Auth key show/hide
         */
        var $opInputKey = $('#op-input-key'),
            $opBtnShowKey = $('#op-btn-show-key'),
            $opBtnGenKey = $('#op-btn-gen-key'),
            $icon = $opBtnShowKey.find('i');

        $opBtnShowKey.on('click.op', function (e) {
            e.preventDefault();
            if ($icon.hasClass('dashicons-visibility')) {
                $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                $opInputKey.prop('type', 'text');
            } else {
                $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                $opInputKey.prop('type', 'password');
            }

        });

        //Set random string In input field
        $opBtnGenKey.on('click.op', function (e) {
            e.preventDefault();
            $opInputKey.val(getRandomString());

        });

        /**
         * Generate a new random string
         * @returns {string}
         */
        function getRandomString() {
            return (Math.random() * 1e64).toString(36).slice(2);
        }
    });
})(window, document, Math, jQuery);