{extends file="frontend/index/index.tpl"}

{* Back to the shop button *}
{block name='frontend_index_logo_trusted_shops'}
    {$smarty.block.parent}
    {if $theme.checkoutHeader}
        <a href="{url controller='index'}"
           class="btn is--small btn--back-top-shop is--icon-left"
           title="{"{s name='FinishButtonBackToShop' namespace='frontend/checkout/finish'}{/s}"|escape}"
           xmlns="http://www.w3.org/1999/html">
            <i class="icon--arrow-left"></i>
            {s name="FinishButtonBackToShop" namespace="frontend/checkout/finish"}{/s}
        </a>
    {/if}
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}{/block}

{* Hide shop navigation *}
{block name='frontend_index_shop_navigation'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide global menu *}
{block name='frontend_index_menu_container'}{/block}

{* Hide checkout_actions *}
{block name='frontend_index_checkout_actions'}{/block}

{* Step box *}
{block name='frontend_index_navigation_categories_top'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
    {include file="frontend/register/steps.tpl" sStepActive="questions"}
{/block}

{* Hide top bar *}
{block name='frontend_index_top_bar_container'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Footer *}
{block name="frontend_index_footer"}
    {if !$theme.checkoutFooter}
        {$smarty.block.parent}
    {else}
        {block name='frontend_index_checkout_confirm_footer'}
            {include file="frontend/index/footer_minimal.tpl"}
        {/block}
    {/if}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="content questions--content">

    {* Error messages *}
    {block name='frontend_checkout_questions_error_messages'}
        {include file="frontend/checkout/error_messages.tpl"}
    {/block}

    {block name='frontend_checkout_questions_form'}
		<div class="questions--content-wrapper">
			<div class="questions--form-button-box">
				<button type="submit" class="questions--form-button btn is--primary is--large right" form="questions--form" data-preloader-button="true">
					{s name='CheckoutQuestionsLabelButtonNext' namespace='CheckoutQuestions'}Weiter{/s}<i class="icon--arrow-right"></i>
				</button>
			</div>
			<h2>{s name='CheckoutQuestionsTitle' namespace='CheckoutQuestions'}Bitte zutreffendes ankreuzen{/s}</h2>
			<div class="questions--content-form">
				<form id="questions--form" method="post" action="{url action='interview'}">
					{* Gender *}
					<div class="questions--form-field questions--form-row-1{if $checkoutQuestionGenderError} error-in-field{/if}">
						<div class="questions--form-field-label">
							<span class="label--title">{s name='CheckoutQuestionsLabelGender' namespace='CheckoutQuestions'}Geschlecht{/s}</span>
							<div class="tooltip-box"><b>{s name='CheckoutQuestionsLabelTooltip' namespace='CheckoutQuestions'}{/s}</b>
								<span class="tooltiptext">{s name='CheckoutQuestionsLabelTooltipGender' namespace='CheckoutQuestions'}Lorem ipsum{/s}</span>
							</div>
						</div>
						<div class="questions--form-field-input">
							<div class="questions--checkbox-box"><input type="radio" id="gender-f" name="gender" value="f"{if $checkoutQuestionGender == "f"} checked="checked"{/if}><label for="gender-f" required>{s name='CheckoutQuestionsLabelGenderFemale' namespace='CheckoutQuestions'}weiblich{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="gender-m" name="gender" value="m"{if $checkoutQuestionGender == "m"} checked="checked"{/if}><label for="gender-m" required>{s name='CheckoutQuestionsLabelGenderMale' namespace='CheckoutQuestions'}männlich{/s}</label></div>
						</div>
					</div>
					{*  Date of birth *}
					<div class="questions--form-field questions--form-row-2{if $checkoutQuestionDateOfBirthError} error-in-field{/if}">
						<div class="questions--form-field-label">
							<span class="label--title">{s name='CheckoutQuestionsLabelDateOfBirth' namespace='CheckoutQuestions'}Geburtsdatum{/s}</span>
						</div>
						<div class="questions--form-field-input">
							<div class="questions--input-box"><input type="text" pattern="{literal}[0-9]{2}\.[0-9]{2}\.[0-9]{4}{/literal}" id="dob" name="dateofbirth" value="{if $checkoutQuestionDateOfBirth}{$checkoutQuestionDateOfBirth}{/if}" placeholder="{s name='CheckoutQuestionsPlaceholderDateOfBirth' namespace='CheckoutQuestions'}TT.MM.JJJJ{/s}" required></div>
						</div>
					</div>
					{*  Size *}
					<div class="questions--form-field questions--form-row-3{if $checkoutQuestionSizeError} error-in-field{/if}">
						<div class="questions--form-field-label">
							<span class="label--title">{s name='CheckoutQuestionsLabelSize' namespace='CheckoutQuestions'}Größe{/s}</span>
						</div>
						<div class="questions--form-field-input">
							<div class="questions--input-box"><input type="number" min="0" id="size" name="size" value="{if $checkoutQuestionSize}{$checkoutQuestionSize}{/if}" placeholder="{s name='CheckoutQuestionsPlaceholderSize' namespace='CheckoutQuestions'}cm{/s}" required></div>
						</div>
					</div>
					{* Weight *}
					<div class="questions--form-field questions--form-row-4{if $checkoutQuestionWeightError} error-in-field{/if}">
						<div class="questions--form-field-label">
							<span class="label--title">{s name='CheckoutQuestionsLabelWeight' namespace='CheckoutQuestions'}Gewicht{/s}</span>
						</div>
						<div class="questions--form-field-input">
							<div class="questions--input-box"><input type="number" min="0" id="weight" name="weight" value="{if $checkoutQuestionWeight}{$checkoutQuestionWeight}{/if}" placeholder="{s name='CheckoutQuestionsPlaceholderWeight' namespace='CheckoutQuestions'}kg{/s}" required></div>
						</div>
					</div>
					{* Ethnicity *}
					<div class="questions--form-field questions--form-row-5{if $checkoutQuestionEthnicityError} error-in-field{/if}">
						<div class="questions--form-field-label">
							<span class="label--title">{s name='CheckoutQuestionsLabelEthnicity' namespace='CheckoutQuestions'}Ethnische Zugehörigkeit{/s}</span>
						</div>
						<div class="questions--form-field-input">
							<div class="questions--checkbox-box"><input type="radio" id="ethnicity-eu" name="ethnicity" value="eu"{if $checkoutQuestionEthnicity == "eu"} checked="checked"{/if} required><label for="ethnicity-eu">{s name='CheckoutQuestionsLabelEthnicityEu' namespace='CheckoutQuestions'}europäisch{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="ethnicity-af" name="ethnicity" value="af"{if $checkoutQuestionEthnicity == "af"} checked="checked"{/if} required><label for="ethnicity-af">{s name='CheckoutQuestionsLabelEthnicityAf' namespace='CheckoutQuestions'}afrikanisch{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="ethnicity-ar" name="ethnicity" value="ar"{if $checkoutQuestionEthnicity == "ar"} checked="checked"{/if} required><label for="ethnicity-ar">{s name='CheckoutQuestionsLabelEthnicityAr' namespace='CheckoutQuestions'}arabisch{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="ethnicity-as" name="ethnicity" value="as"{if $checkoutQuestionEthnicity == "as"} checked="checked"{/if} required><label for="ethnicity-as">{s name='CheckoutQuestionsLabelEthnicityAs' namespace='CheckoutQuestions'}asiatisch{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="ethnicity-hi" name="ethnicity" value="hi"{if $checkoutQuestionEthnicity == "hi"} checked="checked"{/if} required><label for="ethnicity-hi">{s name='CheckoutQuestionsLabelEthnicityHi' namespace='CheckoutQuestions'}hispanisch{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="ethnicity-mi" name="ethnicity" value="mi"{if $checkoutQuestionEthnicity == "mi"} checked="checked"{/if} required><label for="ethnicity-mi">{s name='CheckoutQuestionsLabelEthnicityMi' namespace='CheckoutQuestions'}Mischung{/s}</label></div>
						</div>
					</div>
					{* Desired weight *}
					<div class="questions--form-field questions--form-row-6{if $checkoutQuestionDesiredWeightError} error-in-field{/if}">
						<div class="questions--form-field-label">
							<span class="label--title">{s name='CheckoutQuestionsLabelDesiredWeight' namespace='CheckoutQuestions'}Wunschgewicht{/s}</span>
						</div>
						<div class="questions--form-field-input">
							<div class="questions--input-box"><input type="number" min="0" id="desired-weight" name="desired-weight" value="{if $checkoutQuestionDesiredWeight}{$checkoutQuestionDesiredWeight}{/if}" placeholder="{s name='CheckoutQuestionsPlaceholderDesiredWeight' namespace='CheckoutQuestions'}kg{/s}"></div>
						</div>
					</div>
					{* Basal metabolic rate without physical activity *}
					<div class="questions--form-field questions--form-row-7{if $checkoutQuestionBasalMetabolicRateWithoutPhysicalActivityError} error-in-field{/if}">
						<div class="questions--form-field-label">
							<span class="label--title">{s name='CheckoutQuestionsLabelBasalMetabolicRateWithoutPhysicalActivity' namespace='CheckoutQuestions'}Grundumsatz ohne körperliche Aktivität{/s}</span>
							<div class="tooltip-box"><b>{s name='CheckoutQuestionsLabelTooltip' namespace='CheckoutQuestions'}{/s}</b>
								<span class="tooltiptext">{s name='CheckoutQuestionsLabelTooltipMetabolicRateWithoutPhysicalActivity' namespace='CheckoutQuestions'}Lorem ipsum{/s}</span>
							</div>
						</div>
						<div class="questions--form-field-input">
							<div class="questions--input-box"><input type="number" min="0" id="bmrwpa" name="bmrwpa" value="{if $checkoutQuestionBasalMetabolicRateWithoutPhysicalActivity}{$checkoutQuestionBasalMetabolicRateWithoutPhysicalActivity}{/if}" placeholder="{s name='CheckoutQuestionsPlaceholderBasalMetabolicRateWithoutPhysicalActivity' namespace='CheckoutQuestions'}kcal pro Tag{/s}"></div>
						</div>
					</div>
					{* Basic metabolism including physical activity *}
					<div class="questions--form-field questions--form-row-8{if $checkoutQuestionBasicMetabolismIncludingPhysicalActivityError} error-in-field{/if}">
						<div class="questions--form-field-label">
							<span class="label--title">{s name='CheckoutQuestionsLabelBasicMetabolismIncludingPhysicalActivity' namespace='CheckoutQuestions'}Grundumsatz inkl. körperliche Aktivität{/s}</span>
						</div>
						<div class="questions--form-field-input">
							<div class="questions--input-box"><input type="number" min="0" id="bmipa" name="bmipa" value="{if $checkoutQuestionBasicMetabolismIncludingPhysicalActivity}{$checkoutQuestionBasicMetabolismIncludingPhysicalActivity}{/if}" placeholder="{s name='CheckoutQuestionsPlaceholderBasicMetabolismIncludingPhysicalActivity' namespace='CheckoutQuestions'}kcal pro Tag{/s}"></div>
						</div>
					</div>
					{* Physical Activity Occupation / Leisure *}
					<div class="questions--form-field questions--form-row-9{if $checkoutQuestionPhysicalActivityOccupationOrLeisureError} error-in-field{/if}">
						<div class="questions--form-field-label">
							<span class="label--title">{s name='CheckoutQuestionsLabelPhysicalActivityOccupationOrLeisure' namespace='CheckoutQuestions'}Körperliche Aktivität Beruf/Freizeit{/s}</span>
						</div>
						<div class="questions--form-field-input">
							<div class="questions--checkbox-box"><input type="radio" id="paol-ve" name="paol" value="ve"{if $checkoutQuestionPhysicalActivityOccupationOrLeisure == "ve"} checked="checked"{/if}><label for="paol-ve">{s name='CheckoutQuestionsLabelPaolVeryEasy' namespace='CheckoutQuestions'}sehr leicht{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="paol-no" name="paol" value="no"{if $checkoutQuestionPhysicalActivityOccupationOrLeisure == "no"} checked="checked"{/if}><label for="paol-no">{s name='CheckoutQuestionsLabelPaolNormal' namespace='CheckoutQuestions'}normal{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="paol-mo" name="paol" value="mo"{if $checkoutQuestionPhysicalActivityOccupationOrLeisure == "mo"} checked="checked"{/if}><label for="paol-mo">{s name='CheckoutQuestionsLabelPaolModerate' namespace='CheckoutQuestions'}mäßig{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="paol-ac" name="paol" value="ac"{if $checkoutQuestionPhysicalActivityOccupationOrLeisure == "ac"} checked="checked"{/if}><label for="paol-ac">{s name='CheckoutQuestionsLabelPaolActive' namespace='CheckoutQuestions'}aktiv{/s}</label></div>
							<div class="questions--checkbox-box"><input type="radio" id="paol-va" name="paol" value="va"{if $checkoutQuestionPhysicalActivityOccupationOrLeisure == "va"} checked="checked"{/if}><label for="paol-va">{s name='CheckoutQuestionsLabelPaolVeryActive' namespace='CheckoutQuestions'}sehr aktiv{/s}</label></div>
						</div>
					</div>
				</form>
			</div>
			<div class="questions--form-button-box">
				<button type="submit" class="questions--form-button btn is--primary is--large right" form="questions--form" data-preloader-button="true">
					{s name='CheckoutQuestionsLabelButtonNext' namespace='CheckoutQuestions'}Weiter{/s}<i class="icon--arrow-right"></i>
				</button>
			</div>
		</div>
    {/block}
</div>
{/block}
