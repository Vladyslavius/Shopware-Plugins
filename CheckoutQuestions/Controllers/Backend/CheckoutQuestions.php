<?php
class Shopware_Controllers_Backend_CheckoutQuestions extends \Enlight_Controller_Action
{
	/*
	 * Add custom questions form data to backend order data view
	 */
	public function loadAction()
	{
		$orderID = $this->Request()->get("orderID");

		$data = "<h3>No data</h3>";

		if($orderID)
		{
			$parts = [];
			$parts[] = "<div style='padding: 10px;'>";
			$sql = "SELECT `cq_questions_form_data` FROM `s_order_attributes` WHERE `orderID` = {$orderID}";
			$interviewRawData = Shopware()->Db()->fetchOne($sql);
			$interviewData = json_decode($interviewRawData, true);

			if(isset($interviewData["Gender"]))
			{
				switch($interviewData["Gender"])
				{
					case "f":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelGender") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelGenderFemale");
						break;
					case "m":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelGender") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelGenderMale");
						break;
					default:
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelGender") . "</span>&nbsp;keine Daten";
						break;
				}
			} else {
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelGender") . "</span>&nbsp;keine Daten";
			}

			if(isset($interviewData["DateOfBirth"]))
			{
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelDateOfBirth") . "</span>&nbsp;{$interviewData["DateOfBirth"]}";
			} else {
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelDateOfBirth") . "</span>&nbsp;keine Daten";
			}

			if(isset($interviewData["Size"]))
			{
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelSize") . "</span>&nbsp;{$interviewData["Size"]}" . " " . $this->getSnippet("CheckoutQuestionsPlaceholderSize");
			} else {
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelSize") . "</span>&nbsp;keine Daten";
			}

			if(isset($interviewData["Weight"]))
			{
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelWeight") . "</span>&nbsp;{$interviewData["Weight"]}" . " " . $this->getSnippet("CheckoutQuestionsPlaceholderWeight");
			} else {
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelWeight") . "</span>&nbsp;keine Daten";
			}

			if(isset($interviewData["Ethnicity"]))
			{
				switch($interviewData["Ethnicity"])
				{
					case "eu":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelEthnicity") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelEthnicityEu");
						break;
					case "af":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelEthnicity") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelEthnicityAf");
						break;
					case "ar":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelEthnicity") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelEthnicityAr");
						break;
					case "as":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelEthnicity") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelEthnicityAs");
						break;
					case "hi":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelEthnicity") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelEthnicityHi");
						break;
					case "mi":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelEthnicity") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelEthnicityMi");
						break;
					default:
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelEthnicity") . "</span>&nbsp;keine Daten";
						break;
				}
			} else {
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelEthnicity") . "</span>&nbsp;keine Daten";
			}

			if(isset($interviewData["DesiredWeight"]))
			{
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelDesiredWeight") . "</span>&nbsp;{$interviewData["DesiredWeight"]}" . " " . $this->getSnippet("CheckoutQuestionsPlaceholderDesiredWeight");
			} else {
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelDesiredWeight") . "</span>&nbsp;keine Daten";
			}

			if(isset($interviewData["BasalMetabolicRateWithoutPhysicalActivity"]))
			{
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelBasalMetabolicRateWithoutPhysicalActivity") . "</span>&nbsp;{$interviewData["BasalMetabolicRateWithoutPhysicalActivity"]}" . " " . $this->getSnippet("CheckoutQuestionsPlaceholderBasalMetabolicRateWithoutPhysicalActivity");
			} else {
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelBasalMetabolicRateWithoutPhysicalActivity") . "</span>&nbsp;keine Daten";
			}

			if(isset($interviewData["BasicMetabolismIncludingPhysicalActivity"]))
			{
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelBasicMetabolismIncludingPhysicalActivity") . "</span>&nbsp;{$interviewData["BasicMetabolismIncludingPhysicalActivity"]}" . " " . $this->getSnippet("CheckoutQuestionsPlaceholderBasicMetabolismIncludingPhysicalActivity");
			} else {
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelBasicMetabolismIncludingPhysicalActivity") . "</span>&nbsp;keine Daten";
			}

			if(isset($interviewData["PhysicalActivityOccupationOrLeisure"]))
			{
				switch($interviewData["PhysicalActivityOccupationOrLeisure"])
				{
					case "ve":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelPhysicalActivityOccupationOrLeisure") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelPaolVeryEasy");
						break;
					case "no":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelPhysicalActivityOccupationOrLeisure") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelPaolNormal");
						break;
					case "mo":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelPhysicalActivityOccupationOrLeisure") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelPaolModerate");
						break;
					case "ac":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelPhysicalActivityOccupationOrLeisure") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelPaolActive");
						break;
					case "va":
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelPhysicalActivityOccupationOrLeisure") . "</span>&nbsp;" . $this->getSnippet("CheckoutQuestionsLabelPaolVeryActive");
						break;
					default:
						$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelPhysicalActivityOccupationOrLeisure") . "</span>&nbsp;keine Daten";
						break;
				}
			} else {
				$parts[] = "<span style='font-weight: bold; width: 320px; display: inline-block;'>" . $this->getSnippet("CheckoutQuestionsLabelPhysicalActivityOccupationOrLeisure") . "</span>&nbsp;keine Daten";
			}
			$parts[] = "</div>";

			$data = implode("<br />", $parts);
		}

		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();


		die($data);
	}

	/*
	 * Load custom questions snippets
	 */
	public function getSnippet($name)
	{
		return Shopware()->Snippets()->getNamespace("CheckoutQuestions")->get($name);
	}
}
?>
