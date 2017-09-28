<?php
	include_once 'libs/formLib.php';
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
			<?php
				CreateHeadline("ESE Evaluation Index Page");
				CreateLink("Student Evaluation Questionnaire", "student_questionnaire.php");
				CreateLink("Tutor Evaluation Questionnaire", "tutor_questionnaire.php");
				CreateLink("Key Control Panel", "keyControlPanel.php");
				CreateLink("Key Overview", "keyTable.php");
				CreateLink("Evaluation analysis page", "analysis.php");
				CreateLink("Generate tickets for Monday", "patronGen.php");
			?>
		</div>
	</body>
</html>
