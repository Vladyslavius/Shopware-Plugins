//{block name="backend/article/view/detail/base" append}
Ext.define('Shopware.apps.Article.view.detail.PriceUpdaterFieldset', {
    override: 'Shopware.apps.Article.view.detail.Window',
    createBaseTab: function() {
        var me = this;
        me.detailContainer = me.callParent(arguments);

        me.priceUpdaterFieldSet = me.createPriceUpdaterFieldSet();

        me.detailForm.add(1, me.priceUpdaterFieldSet);

        return me.detailContainer;
    },

    createPriceUpdaterFieldSet: function () {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            layout: {
                type: 'hbox'
            },
            cls: Ext.baseCSSPrefix + 'article-price-updater-field-set',
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            title: '{s name=price_updater/title}Set metall data{/s}',
            items: [
                Ext.create('Ext.container.Container', {
                    flex: 1,
                    defaults: {
                        labelWidth: 155,
                        anchor: '100%'
                    },
                    padding: '0 20 0 0',
                    layout: 'anchor',
                    border: false,
                    items: [
                        {
                            xtype: 'checkbox',
                            name: 'setMetallUse',
                            fieldLabel: '{s name=price_updater/use}Sell by metall price{/s}',
                            inputValue: true,
                            uncheckedValue: false
                        },{
                            xtype: 'numberfield',
                            name: 'setMetallWeight',
                            fieldLabel: '{s name=price_updater/weight}Product weight{/s}',
                            supportText: '{s name=price_updater/weight_support}Set product weight in gramm{/s}',
                            minValue: 0,
                            step: 0.01
                        }
                    ]
                }),
                Ext.create('Ext.container.Container', {
                    flex: 1,
                    defaults: {
                        labelWidth: 155,
                        anchor: '100%'
                    },
                    padding: '0 20 0 0',
                    layout: 'anchor',
                    border: false,
                    items: [
                        {
                            xtype: 'combobox',
                            store: me.getMetallStore(),
                            displayField: 'description',
                            valueField: 'value',
                            fieldLabel: '{s name=price_updater/setType}Metall type{/s}',
                            name: 'setMetallType',
                            editable: false,
                            emptyText: '{s name=price_updater/standart}Standart product{/s}'
                        },{
                            xtype: 'textfield',
                            name: 'setMetallAppendix',
                            fieldLabel: '{s name=price_updater/appendix}Product markup{/s}',
                        }
                    ]
                })
            ]
        });
    },

    getMetallStore: function () {
        return Ext.create('Ext.data.Store', {
            fields: [{
                name: 'value',
                useNull: true
            }, {
                name: 'description'
            }],
            data: [{
                value: 'silver',
                description: '{s name=price_updater/silver}Silver{/s}'
            }, {
                value: 'gold',
                description: '{s name=price_updater/gold}Gold{/s}'
            }, {
                value: 'platinum',
                description: '{s name=price_updater/platinum}Platinum{/s}'
            }, {
                value: 'palladium',
                description: '{s name=price_updater/Palladium}Palladium{/s}'
            }]
        });
    }
});
//{/block}
