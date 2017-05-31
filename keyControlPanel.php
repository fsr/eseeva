<?php
//============================================================================
// Name        : keyControlPanel.php
// Author      : Patrick Reipschläger
// Version     : 1.0
// Date        : 08-2013
// Description : Control panel for managing key codes. The not so quick
//               but dirty approach. At some point this should probably
//               be rewritten using a cleaner approach.
//============================================================================

	include_once 'libs/keyLib.php';
	include_once 'libs/formLib.php';
	
	// the super secret password that must be entered to access the control center
	define ("ACCESS_CODE", "blub");
	// all the states that the control center can have
	define ("STATE_NONE", 0);
	define ("STATE_ACCESS_ENTER", 1);
	define ("STATE_ACCESS_FAILED", 2);
	define ("STATE_ACCESS_SUCCESSFULL", 3);
	define ("STATE_KEY_NONEXISTENT", 10);
	define ("STATE_KEY_UNISSUED", 11);
	define ("STATE_KEY_ISSUED", 12);
	define ("STATE_KEY_ACTIVATED", 13);
	define ("STATE_KEY_USED", 14);
	define ("STATE_ACTION_UNISSUED", 20);
	define ("STATE_ACTION_ISSUED", 21);
	define ("STATE_ACTION_ACTIVATED", 22);
	define ("STATE_ACTION_USED", 23);
	define ("STATE_ACTION_NEWCODE", 24);
	define ("STATE_ACTION_FAILED", 25);
	
	// start a session prevent sending the same post twice
	// if the user refreshes the page, it will default to the
	// access code screen
	session_start();
	$formState = STATE_ACTION_NEWCODE;
	$keyCode = "";
	$keyData = ReadKeys(KEYFILE);
	
	// if the variable is set, the form has been posted to itself
	// if the submission ids of the post and the form don't match, the
	// user has refreshed the site and thus its reseted to the default state
	if (isset($_POST["submit"]) && isset($_SESSION["submissionId"]) && ($_SESSION["submissionId"] == $_POST["submissionId"]))
	{
		// check if the access code was correct
		/*if ($_POST["accessCode"] != ACCESS_CODE)
			$formState = STATE_ACCESS_FAILED;
		// if the access code was correct, check if a key code has been entered
		else*/ 
		if (isset($_POST["keyCode"]))
		{
			// if a key code has been entered, get the state of that key
			$keyCode = $_POST["keyCode"];
			//$keyData = ReadKeyFile(KEYFILE);
			// if no action was performed on the entered key, simply display its state
			if (!isset($_POST["action"])){
				$formState = KeyStateToFormState(GetKeyState(KEYFILE, $_POST["keyCode"]));
			}
			else
			{
				// otherwise set the state of the form to the action that should be performed
				$formState = $_POST["action"];
				// if the state is different from the new code action, perform said action
				// on the key and if the action was successful save the key file
				if ($formState != STATE_ACTION_NEWCODE)
				{
					if (SetKeyState(KEYFILE, $keyCode, FormStateToKeyState($formState)))
						SetKeyState(KEYFILE, $keyCode, FormStateToKeyState($formState));
					else
						$formState($STATE_ACTION_FAILED);
					$_POST["action"] = STATE_ACTION_NEWCODE;
				}
			}
		}
		// if the access code was correct and no code was entered
		else
			$formState = STATE_ACTION_NEWCODE;
	}
	// generate a new submission id that is used within the form to prevent double posts
	$_SESSION["submissionId"] = rand();
?>

<!DOCTYPE html>
<html>
	<head>
    <meta charset="utf-8">
		<title>ESE Evaluation - Key Control Center</title>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
	</head>
	<body>
		<div class="container">
			<form action="" method="post">
				<?php
					CreateHeadline("Key Control Panel");
					CreateInfoBox($formState);
					//CreateAccessBox($formState);
					
					switch ($formState)
					{
						// if the access was successful or a action was performed successfully,
						// just display the empty key code box
						case STATE_ACCESS_SUCCESSFULL:
						case STATE_ACTION_ISSUED:
						case STATE_ACTION_UNISSUED:
						case STATE_ACTION_ACTIVATED:
						case STATE_ACTION_USED:
						case STATE_ACTION_NEWCODE:
							CreateKeyDropDownBox($keyData, "");
							break;
						// if previously entered key was not found or the action that should
						// be performed has been failed, just display the key code box with
						// previously entered value
						case STATE_KEY_NONEXISTENT:
						case STATE_ACTION_FAILED:
							CreateKeyDropDownBox($keyData, $keyCode);
							break;
						// if an existing key has been entered, display the readonly key code box
						// and all options that can be performed on the key
						case STATE_KEY_UNISSUED:
						case STATE_KEY_ISSUED:
						case STATE_KEY_ACTIVATED:
						case STATE_KEY_USED:
							CreateKeyCodeBox($keyCode, false);
							CreateRowHeader();
							echo "  <div class=\"col-12\">\n";
							echo "    <br/><p><strong>Bitte wähle die gewünschte Aktion aus:</strong></p>\n";
							echo "  </div>\n";
							echo "</div>\n";
							
							CreateOption("ESE Code Status auf 'Nicht Ausgegeben' setzen", STATE_ACTION_UNISSUED, true);
							CreateOption("ESE Code Status auf 'Ausgegeben' setzen", STATE_ACTION_ISSUED, true);
							CreateOption("ESE Code Status auf 'Fragebogen ausgefüllt' setzen", STATE_ACTION_ACTIVATED, true);
							CreateOption("ESE Code Status auf 'eingelöst' setzen", STATE_ACTION_USED, true);
							CreateOption("Neuen ESE-Code eingeben", STATE_ACTION_NEWCODE, true, true);
							break;
					}
				?>
				<div class="row">
					<input class="form-control btn-success" type="submit" name="submit" value="Absenden"/>
				</div>
				<input type="hidden" value="<?php /*Hidden input with previously generated id - used for preventing double posts*/ echo $_SESSION['submissionId'];?>" name="submissionId"> 
			</form>
		</div>
	</body>
</html>

<?php
	/**
	 * Echos a new option row with the specified parameters.
	 *
	 * @param string $label The text that is displayed for that option.
	 * @param integer $id The unique id of that option
	 * @param boolean $enabled Indicates if that option should be enabled or not. Default is enabled.
	 * @param boolean $checked Indicates if that option should be checked or not. Default is not checked.
	 */
	function CreateOption($label, $id, $enabled = true, $checked = false)
	{
		CreateRowHeader();
		echo "	<div class=\"col-12\">\n";
		echo "    <label>\n";
		echo "      <input class=\"\" type=\"radio\" id=\"action\" name=\"action\" value=\"" . $id . "\" required";
		if ($checked)
			echo " checked";
		if (!$enabled)
			echo " disabled";
		echo "/>\n";
		echo "      " . $label . "\n";
		echo "    </label>\n";
		echo "  </div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos the key code text box.
	 *
	 * @param string $keyCode The key that should be displayed in the box.
	 * @param boolean $enabled Indicates if the key code box should be enabled or not.
	 */
	function CreateKeyCodeBox($keyCode, $enabled)
	{
		CreateRowHeader();
		echo "	<div class=\"col-6\">\n";
		echo "		<p class=\"lead\">ESE Code:</p>\n";
		echo "	</div>\n";
		echo "	<div class=\"col-6\">\n";
		echo "		<input class=\"form-control\" type=\"text\" id=\"keyCode\" name=\"keyCode\" value=\"" . $keyCode . "\" required";
		if (!$enabled)
			echo " readonly";
		echo "/>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos a drop down box with all keys.
	 *
	 * @param string $keyCode The key that should be displayed in the box.
	 */
	function CreateKeyDropDownBox($keyData, $keyCode)
	{
		CreateRowHeader();
		echo "	<div class=\"col-6\">\n";
		echo "		<p class=\"lead\">ESE Code:</p>\n";
		echo "	</div>\n";
		echo "	<div class=\"col-6\">\n";
		echo "		<select class=\"form-control\" id=\"keyCode\" name=\"keyCode\" required>\n";
		foreach ($keyData as $key => $value) {
			echo "			<option value=\"". $value['KeyId'] ."\" ";
			if ($keyCode != "" && $keyCode == $value['KeyId']) {
				echo "selected=\"selected\"";
			}
			echo ">".$value['KeyId']."</option>\n";
		}
		echo "      </select>\n";
		//echo "		<input class=\"form-control\" type=\"text\" id=\"keyCode\" name=\"keyCode\" value=\"" . $keyCode . "\" required";
		echo "	</div>\n";
		echo "</div>\n";
	}
	/**
	 * Echos the access code box according the current state of the form.
	 *
	 * @param integer $formState The current state of the form.
	 */
	function CreateAccessBox($formState)
	{
		if ($formState < STATE_ACCESS_SUCCESSFULL)
			echo "<div class=\"row equalrow\">\n";
		else
			echo "<div class=\"row equalrow\" hidden>";
			
		echo "	<div class=\"col-6\">\n";
		echo "		<p class=\"lead\">Access Code:</p>\n";
		echo "	</div>\n";
		echo "	<div class=\"col-6\">\n";
		echo "		<input class=\"form-control\" type=\"text\" id=\"accessCode\" name=\"accessCode\" value=\"";
		if ($formState != STATE_ACCESS_ENTER)
			echo $_POST["accessCode"];
		echo "\"/>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
	/**
	 * Creates a information message box depending on the current state of the form.
	 * 
	 * @param integer $formState The current state of the form.
	 */
	function CreateInfoBox($formState)
	{
		switch ($formState)
		{
			case STATE_ACCESS_ENTER: CreateMessageBox(MSG_INFO, "Zugang:", "Bitte gib den korrekten Zugangscode ein, um das Key Control Panel nutzen zu können"); break;
			case STATE_ACCESS_FAILED: CreateMessageBox(MSG_DANGER, "Zugang:", "Der eingegebene Zugangscode war falsch! Bitte überprüfe deine Eingabe."); break;
			case STATE_ACCESS_SUCCESSFULL: CreateMessageBox(MSG_SUCCESS, "Zugang:", "Der eingegebene Zugangscode war korrekt! Bitte gib nun den ESE Code ein, welchen du überprüfen oder verändern möchtest."); break;
			case STATE_KEY_NONEXISTENT: CreateMessageBox(MSG_DANGER, "Achtung:", "Der eingegebene ESE Code wurde nicht gefunden! Bitte überprüfe deine Eingabe."); break;
			case STATE_KEY_UNISSUED: CreateMessageBox(MSG_WARNING, "ESE Code gefunden:", "Der eingegebene ESE Code wurde gefunden. <strong>Der Schlüssel wurde nicht an einen Studenten ausgegeben!</strong>."); break;
			case STATE_KEY_ISSUED: CreateMessageBox(MSG_INFO, "ESE Code gefunden:", "Der Schlüssel wurde an einen Studenten ausgegeben, der Fragebogen wurde noch <strong>nicht ausgefüllt</strong>."); break;
			case STATE_KEY_ACTIVATED: CreateMessageBox(MSG_INFO, "ESE Code gefunden:", "Der Schlüssel wurde an einen Studenten ausgegeben, der Fragebogen wurde <strong>ausgefüllt</strong>."); break;
			case STATE_KEY_USED: CreateMessageBox(MSG_WARNING, "ESE Code gefunden:", "Der Schlüssel wurde an einen Studenten ausgegeben, der Fragebogen wurde ausgefüllt und der Student hat bereits eine <strong>ESE Tasse erhalten</strong>."); break;
			case STATE_ACTION_UNISSUED: CreateMessageBox(MSG_SUCCESS, "ESE Code Status geändert:", "Der Schlüssel wurde erfolgreich auf den Status <strong>Nicht Ausgegeben</strong> gesetzt. Bitte gib einen ESE Code ein, welchen du überprüfen oder verändern möchtest."); break;
			case STATE_ACTION_ISSUED: CreateMessageBox(MSG_SUCCESS, "ESE Code Status geändert:", "Der Schlüssel wurde erfolgreich auf den Status <strong>Ausgegeben</strong> gesetzt. Bitte gib einen ESE Code ein, welchen du überprüfen oder verändern möchtest."); break;
			case STATE_ACTION_ACTIVATED: CreateMessageBox(MSG_SUCCESS, "ESE Code Status geändert:", "Der Schlüssel wurde erfolgreich auf den Status <strong>Fragebogen ausgefüllt</strong> gesetzt. Bitte gib einen ESE Code ein, welchen du überprüfen oder verändern möchtest."); break;
			case STATE_ACTION_USED: CreateMessageBox(MSG_SUCCESS, "ESE Code Status geändert:", "Der Schlüssel wurde erfolgreich auf den Status <strong>Eingelöst</strong> gesetzt. Bitte gib einen ESE Code ein, welchen du überprüfen oder verändern möchtest."); break;
			case STATE_ACTION_NEWCODE: CreateMessageBox(MSG_INFO, "ESE Code auswählen:", "Bitte wähle den ESE Code aus, welchen du überprüfen oder verändern möchtest."); break;
			case STATE_ACTION_FAILED: CreateMessageBox(MSG_DANGER, "Achtung:", "Der Status des angegebenen Schlüssels konnte <strong>nicht geändert</strong> werden! Bitte überprüfe deine Eingabe."); break;
		}
	}
	/**
	 * Converts the specified key state constant to a valid form state constant
	 *
	 * @param string $keySate The key state that should be converted.
	 * @return integer
	 */
	function KeyStateToFormState($keyState)
	{
		switch($keyState)
		{
			case KEYSTATE_NONEXISTENT: return STATE_KEY_NONEXISTENT;
			case KEYSTATE_UNISSUED: return STATE_KEY_UNISSUED;
			case KEYSTATE_ISSUED: return STATE_KEY_ISSUED;
			case KEYSTATE_ACTIVATED: return STATE_KEY_ACTIVATED;
			case KEYSTATE_USED: return STATE_KEY_USED;
		}
		return STATE_NONE;
	}
	/**
	 * Converts the specified form state constant to a valid key state constant
	 *
	 * @param integer The form state that should be converted.
	 * @return integer
	 */
	function FormStateToKeyState($keyState)
	{
		switch($keyState)
		{
			case STATE_KEY_NONEXISTENT:
				return KEYSTATE_NONEXISTENT;
			case STATE_KEY_UNISSUED:
			case STATE_ACTION_UNISSUED:
				return KEYSTATE_UNISSUED;
			case STATE_KEY_ISSUED:
			case STATE_ACTION_ISSUED:
				return KEYSTATE_ISSUED;
			case STATE_KEY_ACTIVATED:
			case STATE_ACTION_ACTIVATED:
				return KEYSTATE_ACTIVATED;
			case STATE_KEY_USED:
			case STATE_ACTION_USED:
				return KEYSTATE_USED;
		}
		return KEYSTATE_NONEXISTENT;
	}
?>
