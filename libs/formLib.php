<?php
//============================================================================
// Name        : formLib.php
// Authors     : Patrick Reipschläger, Lucas Woltmann
// Version     : 1.1
// Date        : 07-2014
// Description : Provides several functions for displaying form elements
//               for the ESE evaluation for students and tutors.
//============================================================================

	include ('questionnaireLib.php');
	// constants for the different types of message boxes
	define ("MSG_ALERT", 0);
	define ("MSG_WARNING", 1);
	define ("MSG_DANGER", 2);
	define ("MSG_SUCCESS", 3);
	define ("MSG_INFO", 4);
	// global variable that get toggled if a new row is created and that 
	// indicates if the next created row is of equal or unequal style
	$isEqualRow = true;
	
	/**
	 * Echos a new row division element of equal or unequal style depending
	 * on the last row that has been created.
	 *
	 * @param boolean $toggle Indicates if the style should be switched or if the
	 *                         new row should have same style as the previous row
	 */
	function CreateRowHeader($toggle = true)
	{
		global $isEqualRow;
		
		if ($toggle)
			$isEqualRow = !$isEqualRow;
		
		if ($isEqualRow == true)
			echo "<div class=\"row equalrow\">\n";
		else
			echo "<div class=\"row unequalrow\">\n";
	}
	/**
	 * Echos a new headline division with the specified text with switching
	 * row styles.
	 * 
	 * @param string $text The text that should be displayed in the headline.
	 */
	function CreateHeadline($text)
	{
		CreateRowHeader();
		echo "	<div class=\"col-sm-12\">\n";
		echo "		<h1>" . $text . "</h1>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos a new section headline with the specified text and switching
	 * row styles.
	 *
	 * @param string $text The text that should be displayed in the section header.
	 */
	function CreateSectionHeader($text)
	{
		CreateRowHeader();
		echo "	<div class=\"col-sm-12\">\n";
		echo "		<h2>" . $text . "</h2>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos a new paragraph with the specified text and switching
	 * row styles.
	 *
	 * @param string $text The text that should be displayed in the paragraph.
	 */
	function CreateParagraph($text)
	{
		CreateRowHeader();
		echo "	<div class=\"col-sm-12\">\n";
		echo "    <p class=\"lead\">" . $text . "</p>\n";
		echo "  </div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos a new paragraph with the specified link and switching
	 * row styles.
	 *
	 * @param string $text The text that should be displayed for the link.
	 * @param string $link The target to which should be linked.
	 */
	function CreateLink($text, $link)
	{
		CreateRowHeader();
		echo "	<div class=\"col-sm-12\">\n";
		echo "    <p class=\"lead\"><a href=\"" . $link . "\">" . $text . "</a></p>\n";
		echo "  </div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos a new division with a labelled text box and switching row styles.
	 *
	 * @param string $label The label that should be displayed for the text box.
	 * @param string $id The unique id that is used to identify the text box.
	 * @param string $value The text that should be displayed within the text box.
	 *                      The default value is no text.
	 */
	function CreateTextBox($label, $id, $value = "")
	{
		CreateRowHeader();
		echo "	<div class=\"col-sm-8\">\n";
		echo "		<p class=\"lead\">" . $label . "</p>\n";
		echo "	</div>\n";
		echo "	<div class=\"col-sm-4\">\n";
		echo "		<input class=\"form-control\" type=\"text\" id=\"". $id . "\" name=\"". $id . "\" value=\"" . $value . "\" required/>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos a new division with the questionnaire answer legend and switching row styles.
	 * Should only be used once at the top of the list of questions.
	 */
	function CreateLegend()
	{
		CreateRowHeader();
		echo "	<div class=\"col-sm-2 col-offset-6\"><p class=\"lead\">Bewertung:</p></div>\n";
		echo "	<div class=\"col-sm-4\">\n";
		echo "		<div class=\"row\">\n";
		echo "			<div class=\"col-2\"><p class=\"lead center\">++</p></div>\n";
		echo "			<div class=\"col-2\"><p class=\"lead center\">+</p></div>\n";
		echo "			<div class=\"col-2\"><p class=\"lead center\">o</p></div>\n";
		echo "			<div class=\"col-2\"><p class=\"lead center\">-</p></div>\n";
		echo "			<div class=\"col-2\"><p class=\"lead center\">--</p></div>\n";
		echo "			<div class=\"col-2\"><p class=\"lead center\">N/A</p></div>\n";
		echo "		</div>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos a new division with a question and six radio buttons for the possible answer.
	 *
	 * @param string $id The unique id that is used to identify the question.
	 * @param integer $value Indicates which answer should initially be selected.
	 *                       The default value is -1, which means no answer is selected.
	 *                       The other possible values range from 1 to 6.
	 */
	function CreateQuestion($question, $id, $value = -1)
	{
		CreateRowHeader();
		echo "	<div class=\"col-sm-8\">\n";
		echo "		<p class=\"lead\">" . $question . "</p>\n";
		echo "	</div>\n";
		echo "	<div class=\"col-sm-4\">\n";
		echo "		<div class=\"row\">\n";
		for ($i = 1; $i < 7; $i++)
		{
			echo "			<div class=\"col-2\"><input class=\"form-control\" type=\"radio\" id=\"" . $id . $i . "\" name=\"" . $id . "\" value=\"" . $i . "\" required";
			if ($value == $i)
				echo " checked";
			if ($i == 6)
				echo " checked";
			echo "/></div>\n";
		}
		echo "		</div>\n";
		echo " 	</div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos a new division with a labelled comment box and switching row styles.
	 *
	 * @param string $label The label that should be displayed for the comment box.
	 * @param string $id The unique id that is used to identify the comment box.
	 * @param string $value The comment that should be displayed within the comment box.
	 *                      The default value is no text.
	 */
	function CreateCommentBox($label, $id, $value)
	{
		CreateRowHeader();
		echo "	<div class=\"col-sm-8\">\n";
		echo "		<p class=\"lead\">" . $label . "</p>\n";
		echo "	</div>\n";
		echo "	<div class=\"col-sm-4\">\n";
		echo "		<textarea class=\"form-control\" id=\"" . $id . "\" name=\"" . $id . "\">" . $value . "</textarea>\n";
		echo " 	</div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos a new division for the questionnaire element with the specified id.
	 * What kind of division is created id determined by the type of the element.
	 * 
	 * @param string $id The unique id of the element that should be created.
	 * @param array $questionnaire The questionnaire data that is used to build the form.
	 *                             Passed by reference.
	 * @param array $formData The list of all form elements and their values. This should
	 *                        simply be the $_POST array that was submitted.
	 *                        Passed by reference.
	 */
	function CreateQuestionnaireElement($id, &$questionnaire, &$formData)
	{
		if (!isset($questionnaire[$id]))
			return;
		$entry = $questionnaire[$id];
		$type = $entry[0];
		$value = "";
		if (isset($formData[$id]))
			$value = $formData[$id];
			
		if ($type == Q_HEADLINE)
			CreateHeadline($entry[1]);
		if ($type == Q_LEGEND)
			CreateLegend();
		else if ($type == Q_TEXTBOX)
			CreateTextBox($entry[1], $id, $value);
		else if ($type == Q_QUESTION)
			CreateQuestion($entry[1], $id, $value);
		else if ($type == Q_COMMENT)
			CreateCommentBox($entry[1], $id, $value);
		else if ($type == Q_DROPDOWN)
		{
			$value = $formData;
			CreateDropDownMenu($entry[1], $id, $value);
		}
	}
	/**
	 * Echos a range of new divisions with switching row styles for all questions that
	 * the specified questionnaire data contains. Only the questions are created, nothing else.
	 *
	 * @param array $questionnaire The questionnaire data that is used to build the form.
	 *                             Passed by reference.
	 * @param array $formData The list of all form elements and their values. This should
	 *                        simply be the $_POST array that was submitted.
	 *                        Passed by reference.
	 */
	function CreateAllQuestionElements(&$questionnaire, &$formData)
	{
		foreach($questionnaire as $id => $entry)
		{
			$type = $entry[0];
			if ($type != Q_QUESTION)
				continue;
			$value = "";
			if (isset($formData[$id]))
				$value = $formData[$id];
			CreateQuestion($entry[1], $id, $value);
		}
	}
	/**
	 * Echos a range of new divisions with switching row styles for all questions that
	 * the specified questionnaire data contains. Only the questions are created, nothing else.
	 *
	 * @param array $questionnaire The questionnaire data that is used to build the form.
	 *                             Passed by reference.
	 * @param array $formData The list of all form elements and their values. This should
	 *                        simply be the $_POST array that was submitted.
	 *                        Passed by reference.
	 */
	function CreateAllCommentElements(&$questionnaire, &$formData)
	{	
		foreach($questionnaire as $id => $entry)
		{
			$type = $entry[0];
			if ($type != Q_COMMENT)
				continue;
			$value = "";
			if (isset($formData[$id]))
				$value = $formData[$id];
			CreateCommentBox($entry[1], $id, $value);
		}
	}
	/**
	 * Echos a range of new divisions with switching row styles for all questions that
	 * the specified questionnaire data contains. Only the questions are created, nothing else.
	 *
	 * @param array $questionnaire The questionnaire data that is used to build the form.
	 *                             Passed by reference.
	 * @param array $formData The list of all form elements and their values. This should
	 *                        simply be the $_POST array that was submitted.
	 *                        Passed by reference.
	 */
	function CreateEvaForm(&$questionnaire, &$formData)
	{	
		foreach($questionnaire as $id => $entry)
		{
			$type = $entry[0];
			$value = "";
			if (isset($formData[$id]))
				$value = $formData[$id];
			if ($type == Q_HEADLINE)
				CreateHeadline($entry[1]);
			if ($type == Q_LEGEND)
				CreateLegend();
			else if ($type == Q_TEXTBOX)
				CreateTextBox($entry[1], $id, $value);
			else if ($type == Q_QUESTION)
				CreateQuestion($entry[1], $id, $value);
			else if ($type == Q_COMMENT)
				CreateCommentBox($entry[1], $id, $value);
			else if ($type == Q_DROPDOWN)
			{
				$value = $formData;
				CreateDropDownMenu($entry[1], $id, $value);
			}
		}
	}
	/**
	 * Echos a new division with switching row styles that represents a message box.
	 *
	 * @param integer $msgType The type of message box that should be created. The MSG constants
	 *                         defined at the beginning of this file should be used for this parameter.
	 * @param string $header The text that should be displayed in the header of the message box.
	 * @param string $text The text that is displayed within the message box itself.
	 */
	function CreateMessageBox($msgType, $header, $text)
	{
		CreateRowHeader(false);
		echo "	<div class=\"col-sm-12 col-offset-0\">\n";
		echo "<div class=\"panel";
		if ($msgType == MSG_WARNING)
			echo " panel-warning";
		else if ($msgType == MSG_DANGER)
			echo " panel-danger";
		else if ($msgType == MSG_SUCCESS)
			echo " panel-success";
		else if ($msgType == MSG_INFO)
			echo " panel-info";
		echo "\"><div class=\"panel-heading\"><h3 class=\"panel-title\">" . $header . "</h3></div>" . $text . "</div>";
		echo "	</div>\n";
		echo "</div>\n";
	}

	/**
	 * Echos a new division with a dropdown menu.
	 *
	 * @param string $label The label that should be displayed for the dropdown menu.
	 * @param string $id The unique id that is used to identify the dropdown menu.
	 * @param string $value The options that should be displayed within the dropdown menu.
	 * 
	 */
	function CreateDropDownMenu($label, $id, $value)
	{
		CreateRowHeader();
		echo "	<div class=\"col-sm-8\">\n";
		echo "		<p class=\"lead\">" . $label . "</p>\n";
		echo "	</div>\n";
		echo "	<div class=\"col-sm-4\">\n";
		echo "		<select class=\"form-control\" id=\"". $id . "\" name=\"". $id ."\" required>\n";
		foreach ($value as $key => $entry) {
			echo "			<option value=\"".$entry."\">".$entry."</option>\n";
		}
		echo "		</select>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
?>
