// File location: CheckoutQuestions/Resources/Views/backend/checkout_questions/order/view/detail/window.js
//{block name="backend/order/view/detail/window" append}

Ext.define('Shopware.apps.CheckoutQuestionsExtendOrder.view.detail.Window', {

    override: 'Shopware.apps.Order.view.detail.Window',

    createTabPanel: function() {
        var me = this,
			tabs = me.callParent(arguments); 

		var interview_tab = Ext.create('Ext.form.Panel', {
			title: '{s name="CheckoutQuestionsDataTabTitle" namespace="CheckoutQuestions"}Interviews{/s}',
			record: me.record,
			padding: 5,
			defaults: {
				styleHtmlContent: true
			},
			items: [
				{
					title: '{s name="CheckoutQuestionsDataTitle" namespace="CheckoutQuestions"}Datensatz{/s}',
					id: 'interview-' + me.record.get('id'),
					iconCls: 'home',
					html: Ext.Ajax.request({
						url: '{url controller=CheckoutQuestions action=load}', 
						params: {
							orderID: me.record.get('id')
						},
						success: function(response, opts) {
							var htmlText = response.responseText;
							Ext.getCmp('interview-' + me.record.get('id')).update(htmlText);
						}
					})
				}
			]
		});

		tabs.add(interview_tab);

        return tabs;
    }
});
//{/block}
