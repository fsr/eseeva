<?php
//============================================================================
// Name        : analysisTut.php
// Author      : Patrick Reipschläger, Lucas Woltmann
// Version     : 1.0
// Date        : 01-2017
// Description : Analysis a ESE Evaluation tutor log file. The file that is analysed
//               may be passed as parameter with the URL.
//============================================================================
	include_once 'libs/formLib.php';
	include_once 'libs/questionnaireLib.php';
	include_once 'libs/dbLib.php';
    include_once 'libs/chartLib.php';
	
	// variables for the log data
	$questionData;
	$tutorData;
	$commentData;
	// Default log file is the student log file defined in 'dbLib.php'
	$logFile = LOGDB;
	// if a logFile parameter has been passed in the URL, than that value will
	// be used instead of the default value (with the added folder name)
	if (isset($_GET["logFile"]))
		$logFile = "db/" . $_GET["logFile"];
	// read the database	
	ReadLogDatabase($logFile, 0, $questionData, $tutorData, $commentData);
?>
<!DOCTYPE html>
<html>
	<head>
    <meta charset="utf-8">
		<title>ESE-Tutor Evaluation Analyse</title>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
	</head>
	<body>
		<div class="container">
			<?php
				CreateHeadline("ESE Evaluation - Tutor Data Analysis");
				// if there was any question Data in the log file, display it 
            
				if (count($questionData) > 0)
				{
					CreateSectionHeader("Question Evaluation");
					CreateLegend();
					foreach ($questionData as $question)
					{
						CreateRowHeader();
						echo "  <div class=\"col-sm-8\">\n";
						// The question itself
						echo "    <p class=\"lead\"><span>" . $question[0];
						//average
            			echo "</span><span style=\"float:right;\">⌀" . 
            				round(
            					($question[1]+2*$question[2]+3*$question[3]+4*$question[4]+5*$question[5])
            					/(array_sum(array_slice($question,1,5))), 
            					2)
            	 			. "</span>\n";
            	 		echo "</p>\n";
						echo "  </div>\n";
						echo "	<div class=\"col-sm-4\">\n";
						echo "    <div class=\"row\">\n";
            
                        $width = 800;
                        $height = 300;
                        
                        $file = CreateQuestionBar($width, $height, $question);
            
						echo "    </div>\n";
						echo "  </div>\n";
                        echo "  <div class=\"col-sm-3\">\n";
                        echo "  </div>\n";
                        echo "  <div class=\"col-sm-9\">\n";
                        echo "    <img src=\"".$file."\" class=\"lead center\">";
                        echo "  </div>\n";
						echo "</div>\n"; 
					}
				}
				// if there was any tutor Data in the log file, display it
				if (count($tutorData) > 0)
				{
					CreateSectionHeader("Tutor Evaluation");
					CreateLegend();
					foreach ($tutorData as $tutorName => $tutor)
					{
						CreateRowHeader();
						echo "  <div class=\"col-sm-8\">\n";
						// the name of the tutor
						echo "    <p class=\"lead\"><span>" . $tutorName . "</span>\n";
						
						//average
            			echo "<span style=\"float:right;\">⌀" . 
            				round(
            					($tutor[0]+2*$tutor[1]+3*$tutor[2]+4*$tutor[3]+5*$tutor[4])
            					/(array_sum($tutor)-$tutor[5]), 2)
            	 			. "</span>\n";
            	 		echo "</p>\n";						
						
						echo "  </div>\n";
						echo "	<div class=\"col-sm-4\">\n";
						echo "    <div class=\"row\">\n";
            
                        $width = 800;
                        $height = 300;
                        
                        $file = CreateTutorBar($width, $height, $tutorName, $tutor);
            
						echo "    </div>\n";
						echo "  </div>\n";
                        echo "  <div class=\"col-sm-3\">\n";
                        echo "  </div>\n";
                        echo "  <div class=\"col-sm-9\">\n";
                        echo "    <img src=\"".$file."\" class=\"lead center\">";
                        echo "  </div>\n";
						echo "</div>\n";
            
					}
				}
				// if there was any comment Data in the log file, display it
				if (count($commentData) > 0)
				{
					CreateSectionHeader("Comments");
					foreach ($commentData as $comment)
						// replace all new lines with html breaks to properly display multi-line comments
						CreateParagraph(str_replace("\n", "<br/>\n", $comment));
				}
			?>
		</div>
	</body>
</html>
