//{block name="backend/article/controller/detail" append}
Ext.define('Shopware.apps.Article.controller.DetailUpdater', {
    override: 'Shopware.apps.Article.controller.Detail',

    hasArticlePrice: function(priceStore) {
        var me = this,
            priceExist = false;

        var setMetallUse = me.subApplication.article.data.setMetallUse;

        var firstCustomerGroup = me.subApplication.firstCustomerGroup;

        priceStore.each(function(price) {
            if (setMetallUse) {
                if (price.get('customerGroupKey') == firstCustomerGroup.get('key') && price.get('price') >= 0) {
                    priceExist = true;
                    return true;
                }
            } else {
                if (price.get('customerGroupKey') == firstCustomerGroup.get('key') && price.get('price') > 0) {
                    priceExist = true;
                    return true;
                }
            }
        });

        return priceExist;
    },

    onSaveArticle: function(win, article, options) {
        var me = this;

        me.callParent(arguments);
    },

    prepareArticleProperties: function(article, callback) {
        var me = this;

        me.callParent(arguments);

        Ext.Ajax.request({
            params: {
                mainDetailId:       article.get('mainDetailId'),
                setMetallType:      article.get('setMetallType'),
                setMetallUse:       article.get('setMetallUse'),
                setMetallWeight:    article.get('setMetallWeight'),
                setMetallAppendix:  article.get('setMetallAppendix'),
            },
            dataType: 'json',
            method: 'POST',
            url: '{url controller="AttributeData" action="saveProductMetallData"}',
            success: function (result) {
            }
        });
    }
});
//{/block}


