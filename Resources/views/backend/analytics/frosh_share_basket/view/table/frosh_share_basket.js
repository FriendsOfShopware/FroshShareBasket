//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/frosh_share_basket/view/table/frosh_share_basket"}
Ext.define('Shopware.apps.Analytics.froshShareBasket.view.table.FroshShareBasket', {
    extend: 'Shopware.apps.Analytics.view.main.Table',
    alias: 'widget.analytics-table-froshShareBasket',

    initComponent: function() {
        var me = this;

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex: 1,
                sortable: true
            }
        };
        
        me.initShopColumns([
            {
                dataIndex: 'total_count',
                text: '{s name=frosh_share_basket/savecount}Saved{/s}: [0]',
                renderer: function (val) {
                    return val + 'x';
                }
            },
            {
                dataIndex: 'total_quantity',
                text: '{s name=frosh_share_basket/quantity}Quantity{/s}: [0]'
            },
        ]);

        me.callParent(arguments);
    },

    getColumns: function() {
        return [
            {
                xtype: 'actioncolumn',
                dataIndex: 'ordernumber',
                text: '{s name=frosh_share_basket/ordernumber}Ordernumber{/s}',
                renderer: function (val) {
                    return val;
                },
                items: [
                    {
                        iconCls: 'sprite-pencil',
                        cls: 'editBtn',
                        tooltip: '{s name=table/article_impression/action_column/edit}Edit this Article{/s}',
                        handler: function (view, rowIndex, colIndex, item, event, record) {
                            openNewModule('Shopware.apps.Article', {
                                action: 'detail',
                                params: {
                                    articleId: record.get('articleId')
                                }
                            });
                        }
                    }
                ]
            },
            {
                dataIndex: 'name',
                text: '{s name=frosh_share_basket/article}Article{/s}'
            },
            {
                dataIndex: 'total_count',
                text: '{s name=frosh_share_basket/savecount}Saved{/s}',
                renderer: function (val) {
                    return val + 'x';
                },
            },
            {
                dataIndex: 'total_quantity',
                text: '{s name=frosh_share_basket/quantity}Quantity{/s}'
            }
        ];
    }
});
//{/block}
