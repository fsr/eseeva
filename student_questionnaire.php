<?php
//============================================================================
// Name        : student_questionnaire.php
// Author      : Patrick Reipschläger, Lucas Woltmann
// Version     : 2.0
// Date        : 01-2017
// Description : The form that students have to fill in
//               for the ESE evaluation.
//============================================================================

	include_once 'libs/formLib.php';
	include_once 'libs/questionnaireLib.php';
	include_once 'libs/keyLib.php';
	include_once 'libs/dbLib.php';
	
	// indicates if an error occurred and what error
	$error = 0;
	// Determines if a message box is shown, and what type of message box is shown
	$keyState = "";
	// load the questionnaire and patrons data
	$questionnaire = ReadQuestionnaireFile(STUDENT_QUESTIONNAIRE);
	$patrons = array_keys(ReadPatronsFile(PATRONS));
	// if the variable is set, the form has been posted to itself and can be validated
	if (isset($_POST["submit"]))
	{
		// read the key state
		$keyState = GetKeyState(KEYFILE, $_POST["code"]);
		if ($keyState == KEYSTATE_ISSUED)
		{
			// variables for the log data
			$questionData = array();
			$tutorData = array();
			$commentData;
			
			// add the data of the form to the legacy data structure
			AddQuestionData($_POST, $questionData, $questionnaire);
			AddTutorData($_POST, $tutorData);
			AddCommentData($_POST, $commentData);

			// quick ~~fix~~ hack
			// mail("eseeva@ifsr.de", "ESEEVA2016POST", serialize($_POST));
			
			// write the new data back to the database, only change the state of the key,
			// if that action was successful
			// Uses $student=1 to imply that a student answered the questionnaire.
			if (WriteLogDatabase(LOGDB, 1, $questionData, $tutorData, $commentData, $_POST["code"]))
			{
				if (!SetKeyState(KEYFILE, $_POST["code"], KEYSTATE_ACTIVATED))
				{
					$error = 1;
				}
			}
			// otherwise set the error flag
			else
				$error = 1;
		}
	}
?>

<!DOCTYPE html>
<html>
	<head>
    <meta charset="utf-8">
		<title>ESE Evaluation für Studenten</title>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
	</head>
	<body>
		<div class="container">
			<form action="" method="post">
				<?php
					// CreateQuestionnaireElement("headline", $questionnaire, $_POST);
					// if ($error)
					// 	CreateMessageBox(MSG_DANGER, "Achtung:", "Deine Evaluation konnte aufgrund eines internen Fehlers leider nicht erfolgreich bearbeitet werden.<br/>Bitte versuch es später noch einmal oder wende dich an einen der Verantwortlichen.");
					// else
					// 	CreateKeyMessageBox($keyState);
					// CreateQuestionnaireElement("code", $questionnaire, $_POST);
					// CreateQuestionnaireElement("tutorName", $questionnaire, $patrons);
					// CreateQuestionnaireElement("legend", $questionnaire, $_POST);
					// CreateAllQuestionElements($questionnaire, $_POST);
					// CreateAllCommentElements($questionnaire, $_POST);
					CreateEvaForm($questionnaire, $_POST);
				?>
				<div class="row">
					<input class="form-control btn-success" type="submit" name="submit" value="Absenden"/>
				</div>
			</form>
		</div>
                <p></p>
	</body>
</html>

<?php

	function CreateKeyMessageBox($keyState)
	{
		switch($keyState)
		{
			case KEYSTATE_NONEXISTENT: CreateMessageBox(MSG_DANGER, "Achtung!", "Der angegebene Code konnte nicht verifiziert werden. Bitte überprüfe deine Eingabe."); break;
			case KEYSTATE_UNISSUED: CreateMessageBox(MSG_DANGER, "Achtung!", "Der angegebene Code ist ungültig."); break;
			case KEYSTATE_ISSUED: CreateMessageBox(MSG_SUCCESS, "Danke!", "Der eingebene Code ist korrekt. Dein Fragebogen wurde erfolgreich übermittelt und du bist nun zum Empfang einer ESE-Tasse berechtigt."); break;
			case KEYSTATE_ACTIVATED: CreateMessageBox(MSG_DANGER, "Achtung!", "Der angegebene Code ist wurde bereits zum Ausfüllen eines Fragebogens verwendet. Es darf pro Student nur ein Fragebogen ausgefüllt werden."); break;
			case KEYSTATE_USED: CreateMessageBox(MSG_DANGER, "Achtung!", "Der angegebene Code ist wurde bereits eingelöst. Es darf pro Student nur eine ESE-Tasse ausgegeben werden."); break;
		}
	}
?>
