(function ($, window) {
    $.plugin('FroshShareBasket', {

        defaults: {
            formSelector: '.frosh-share-basket--form',
            responseContainerSelector: '.frosh-share-basket--response',
            webShareButtonSelector: '.share-webshare',
        },

        init: function () {
            var me = this;

            new ClipboardJS('[data-clipboard-target]');
            me._on(me.opts.formSelector, 'submit', $.proxy(me.onSubmitForm, me));
            me.initWebShare();
        },

        initWebShare: function () {
            var me = this;

            if ($(me.opts.webShareButtonSelector).length && navigator.share !== undefined) {
                $(me.opts.webShareButtonSelector).css('display', 'inline-block');
                me._on(me.opts.webShareButtonSelector, 'click', $.proxy(me.onClickWebShare, me));
            }
        },

        onClickWebShare: function (event) {
            var $target = $(event.currentTarget);

            event.preventDefault();

            navigator.share({
                title: $target.data('share-title'),
                text: $target.data('share-text'),
                url: $target.data('share-url'),
            });
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
                    me.initWebShare();
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
