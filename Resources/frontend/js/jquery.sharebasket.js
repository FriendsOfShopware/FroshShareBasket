(function ($, window) {
    $.plugin('FroshShareBasket', {

        init: function () {
            var me = this;
            me.clipboard = new ClipboardJS('[data-clipboard-target]');
        },

        destroy: function () {
            var me = this;
            me._destroy();
        }
    });

    window.StateManager.addPlugin(
        '.main--actions--sharebasket',
        'FroshShareBasket'
    );
})(jQuery, window);
