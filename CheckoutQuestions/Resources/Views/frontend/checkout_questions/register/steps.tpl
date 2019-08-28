{extends file="frontend/register/steps.tpl"}

{block name='frontend_register_steps'}
	<ul class="steps--list">
		{* First Step - Address *}
		{block name='frontend_register_steps_basket'}
			<li class="steps--entry step--basket{if $sStepActive=='address' || $sStepActive=='paymentShipping' || $sStepActive=='questions' || $sStepActive=='finished'} is--active{/if}">
				<span class="icon">{s namespace="CheckoutQuestions" name="CheckoutStepAddressNumber"}1{/s}</span>
				<span class="text"><span class="text--inner">{s namespace="CheckoutQuestions" name="CheckoutStepAddressText"}Ihre Adresse{/s}</span></span>
			</li>
		{/block}

		{* Spacer *}
		{block name='frontend_register_steps_spacer1'}
			<li class="steps--entry steps--spacer{if $sStepActive=='address' || $sStepActive=='paymentShipping' || $sStepActive=='questions' || $sStepActive=='finished'} is--active{/if}">
				<i class="icon--arrow-right"></i>
			</li>
		{/block}

		{* Second Step - Payment *}
		{block name='frontend_register_steps_register'}
			<li class="steps--entry step--register{if $sStepActive=='paymentShipping' || $sStepActive=='questions' || $sStepActive=='finished'} is--active{/if}">
				<span class="icon">{s namespace="CheckoutQuestions" name="CheckoutStepPaymentShippingNumber"}2{/s}</span>
				<span class="text"><span class="text--inner">{s namespace="CheckoutQuestions" name="CheckoutStepPaymentShippingText"}Zahlungsart & Versandart{/s}</span></span>
			</li>
		{/block}

		{* Spacer *}
		{block name='frontend_register_steps_spacer2'}
			<li class="steps--entry steps--spacer{if $sStepActive=='paymentShipping' || $sStepActive=='questions' || $sStepActive=='finished'} is--active{/if}">
				<i class="icon--arrow-right"></i>
			</li>
		{/block}

		{* Third Step - Questions *}
		{block name='frontend_register_steps_questions'}
			<li class="steps--entry step--questions{if $sStepActive=='questions' || $sStepActive=='finished'} is--active{/if}">
				<span class="icon">{s namespace="CheckoutQuestions" name="CheckoutStepQuestionsNumber"}3{/s}</span>
				<span class="text"><span class="text--inner">{s namespace="CheckoutQuestions" name="CheckoutStepQuestionsText"}Fragebogen{/s}</span></span>
			</li>
		{/block}

		{* Spacer *}
		{block name='frontend_register_steps_spacer2'}
			<li class="steps--entry steps--spacer{if $sStepActive=='questions' || $sStepActive=='finished'} is--active{/if}">
				<i class="icon--arrow-right"></i>
			</li>
		{/block}

		{* Fourth Step - Confirmation *}
		{block name='frontend_register_steps_confirm'}
			<li class="steps--entry step--confirm{if $sStepActive=='finished'} is--active{/if}">
				<span class="icon">{s namespace="CheckoutQuestions" name="CheckoutStepConfirmNumber"}4{/s}</span>
				<span class="text"><span class="text--inner">{s namespace="CheckoutQuestions" name="CheckoutStepConfirmText"}Prüfen und Bestellen{/s}</span></span>
			</li>
		{/block}
	</ul>
{/block}
