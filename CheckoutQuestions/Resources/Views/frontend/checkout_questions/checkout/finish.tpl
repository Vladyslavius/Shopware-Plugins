{block name='frontend_checkout_confirm_information_payment' prepend}
<div class="information--panel-item information--panel-item-questions">
	{block name='frontend_checkout_finish_information_questions_panel'}
		<div class="panel has--border block information--panel questions--panel finish--questions">

			{* Headline *}
			{block name='frontend_checkout_finish_information_questions_panel_title'}
				<div class="panel--title is--underline">
					{s namespace="CheckoutQuestions" name="CheckoutFinishQuestionsText"}Fragebogen{/s}
				</div>
			{/block}

			{* Content *}
			{block name='frontend_checkout_finish_information_questions_panel_body'}
			<div class="panel--body is--wide">
				<span class="questions--status is--bold">{s namespace="CheckoutQuestions" name="CheckoutFinishQuestionsStatusText"}Status:{/s}</span>&nbsp;{s namespace="CheckoutQuestions" name="CheckoutFinishQuestionsStatusDataText"}bearbeitet{/s}
			</div>
			{/block}
		</div>
	{/block}
</div>
{/block}
