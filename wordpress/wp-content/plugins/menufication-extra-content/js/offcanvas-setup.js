
(function(root, $) {

    $(document).ready(function() {

        if(wp_off_canvas.is_user_logged_in == 1 && $('#wpadminbar').length ) {
            $('body').addClass('menufication-is-logged-in');
            wp_off_canvas.addToFixedHolder += ',#wpadminbar';
        }

        $('random-name').menufication({

            // Basic settings
            onlyMobile:             wp_off_canvas.onlyMobile == 'on' ? true : false,
            theme:                  wp_off_canvas.theme || 'dark',
            direction:              wp_off_canvas.direction == 'left' ? 'right' : 'left',
            enableMultiple:         true,
            multipleContentElement: '#wp_off_canvas-multiple-content',
            multipleToggleElement:  '#wp_off_canvas-multiple-toggle',

            // Advanced settings
            triggerWidth:           wp_off_canvas.triggerWidth || null,
            addToFixedHolder:       wp_off_canvas.addToFixedHolder,
        });

    });

})(window, jQuery);
