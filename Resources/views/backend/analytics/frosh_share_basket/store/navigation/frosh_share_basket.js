//{block name="backend/analytics/frosh_share_basket/store/navigation/frosh_share_basket"}
Ext.define('Shopware.apps.Analytics.froshShareBasket.store.navigation.FroshShareBasket', {
    extend: 'Ext.data.Store',
    alias: 'widget.analytics-store-froshShareBasket',
    remoteSort: true,

    fields: [
        'ordernumber',
        'total_count',
        'total_quantity',
        'name',
        'articleId',
    ],

    proxy: {
        type: 'ajax',

        url: '{url controller=FroshShareBasket action=getStatistics}',

        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    },

    constructor: function(config) {
        var me = this;
        config.fields = me.fields;

        if (config.shopStore) {
            config.shopStore.each(function(shop) {
                config.fields.push('total_count' + shop.data.id);
                config.fields.push('total_quantity' + shop.data.id);
            });
        }

        me.callParent(arguments);
    }
});
// {/block}
