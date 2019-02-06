(function ($, window) {
    $.plugin('FroshShareBasket', {

        defaults: {
            formSelector: '.frosh-share-basket--form',
            responseContainerSelector: '.frosh-share-basket--response',
        },

        init: function () {
            var me = this;
            new ClipboardJS('[data-clipboard-target]');
            me._on(me.opts.formSelector, 'submit', $.proxy(me.onSubmitForm, me));
        },

        onSubmitForm: function (event) {
            var me = this,
                form = me.$el.find(me.opts.formSelector),
                responseContainer = me.$el.find(me.opts.responseContainerSelector);

            event.preventDefault();

            $.loadingIndicator.open({
                'openOverlay': true,
                animationSpeed: 200
            });

            $.ajax({
                url: form.attr('action'),
                dataType: 'html',
                method: 'POST',
                success: function (response) {
                    form.remove();
                    responseContainer.empty().append(response).hide().fadeIn();
                    $.loadingIndicator.close();
                }
            });
        },

        destroy: function () {
            var me = this;
            me._destroy();
        }
    });

    window.StateManager.addPlugin(
        '.frosh-share-basket--wrapper',
        'FroshShareBasket'
    );
})(jQuery, window);
