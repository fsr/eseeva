<?php
//============================================================================
// Name        : keyTable.php
// Author      : Patrick Reipschläger
// Version     : 1.0
// Date        : 08-2013
// Description : For managing all keys and their states.
//============================================================================
	include_once 'libs/keyLib.php';
	include_once 'libs/formLib.php';
	// start a session prevent sending the same post twice
	// if the user refreshes the page, it will default to the
	// access code screen
	session_start();
	// the key file which should be edited, either the default key file or the one that was specified
	$keyFile = KEYFILE;
	if (isset($_GET["keyFile"]))
		$keyFile = $_GET["keyFile"];
	// holds the key data, either be generated from the form or by from the key file
	$keyData;
	
	
	// if the submission ids of the post and the form don't match, the
	// user has refreshed the site and thus its reseted to the default state

	if (isset($_SESSION["submissionId"]) && $_SESSION["submissionId"] == $_POST["submissionId"])
	{
		if (isset($_POST["changesConfirm"]))
		{
			$keyData = array();
			$i = 0;
			while(isset($_POST["keyIndex" . $i]))
			{
				if (!isset($_POST["keyDelete" . $i]) || $_POST["keyDelete" . $i] == false)
					array_push($keyData, array($_POST["keyCode" . $i], $_POST["keyState" . $i]));
				$i++;
			}
			WriteKeyFile($keyFile, $keyData);
		}
		else if (isset($_POST["keyGenNew"]))
		{
			$amount = $_POST["keyAmount"];
			$keyFile = $_POST["keyFile"];
			CreateKeyFile($amount, $keyFile);
			$keyData = ReadKeyFile($keyFile);
		}
		else if (isset($_POST["keyGenAppend"]))
		{
			$amount = (int)$_POST["keyAmount"];
			$keyFile = $_POST["keyFile"];
			$keyData = ReadKeyFile($keyFile);
			while($amount > 0)
			{
				$key = GenerateKey();
				$duplicate = false;
				foreach($keyData as $entry)
					if ($entry[0] == $key)
					{
						$duplicate = true;
						break;
					}
				if ($duplicate)
					continue;
				array_push($keyData, array($key, KEYSTATE_UNISSUED));
				$amount--;
			}
			WriteKeyFile($keyFile, $keyData);
		}
		else
			$keyData = ReadKeyFile($keyFile);
	}
	// if the page was refreshed by the user, just load the key file
	else
		$keyData = ReadKeyFile($keyFile);
	// generate a new submission id that is used within the form to prevent double posts
	$_SESSION["submissionId"] = rand();
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
				<?php
					CreateHeadLine("Key Control Panel");
				?>
				<?php
					echo "<form action=\"keyTable.php?keyFile=" . $keyFile . "\" method=\"post\">\n";
					CreateSectionHeader("Key Overview");
					
					CreateRowHeader();
					echo "  <div class=\"col-1\">\n    <p class=\"lead\">Index</p>\n  </div>\n";
					echo "  <div class=\"col-6\">\n    <p class=\"lead\">Code</p>\n  </div>\n";
					echo "  <div class=\"col-2\">\n    <p class=\"lead\">State</p>\n  </div>\n";
					echo "  <div class=\"col-1\">\n    <p class=\"lead\">Delete</p>\n  </div>\n";
					echo "</div>";
				
					for ($i = 0; $i < count($keyData); $i++)
					{
						CreateRowHeader();
						echo "  <div class=\"col-1\">\n";
						echo "    <p class=\"lead\">" . $i . "</p>\n";
						echo "    <input type=\"hidden\" name=\"keyIndex" . $i . "\" value=\"" . $i . "\"/>";
						echo "  </div>\n";
						
						echo "  <div class=\"col-6\">\n";
						echo "    <input type=\"textbox\" class=\"form-control\" name=\"keyCode" . $i . "\" value=\"" . $keyData[$i][0] . "\" readonly/>\n";
						echo "  </div>\n";
						
						echo "  <div class=\"col-2\">\n";
						echo "  <select class=\"form-control lead\" name=\"keyState" . $i . "\">";
						echo "    <option value=\"" . KEYSTATE_UNISSUED . "\""; if ($keyData[$i][1] == KEYSTATE_UNISSUED) echo " selected"; echo">" . KEYSTATE_UNISSUED . "</option>";
						echo "    <option value=\"" . KEYSTATE_ISSUED . "\""; if ($keyData[$i][1] == KEYSTATE_ISSUED) echo " selected"; echo">" . KEYSTATE_ISSUED . "</option>";
						echo "    <option value=\"" . KEYSTATE_ACTIVATED . "\""; if ($keyData[$i][1] == KEYSTATE_ACTIVATED) echo " selected"; echo">" . KEYSTATE_ACTIVATED . "</option>";
						echo "    <option value=\"" . KEYSTATE_USED . "\""; if ($keyData[$i][1] == KEYSTATE_USED) echo " selected"; echo">" . KEYSTATE_USED . "</option>";
						echo "  </select>";
						echo "  </div>\n";
						
						echo "  <div class=\"col-1\">\n";
						echo "    <input type=\"checkbox\" class=\"form-control\" name=\"keyDelete" . $i . "\"/>\n";
						echo "  </div>\n";
						echo "</div>\n";
					}
					
					CreateRowHeader();
					echo "  <div class=\"col-5\">\n    <input type=\"submit\" class=\"form-control\" name=\"changesConfirm\" value=\"Confirm Changes\"/>\n  </div>\n";
					echo "  <div class=\"col-2\"></div>\n";
					echo "  <div class=\"col-5\">\n    <input type=\"submit\" class=\"form-control\" name=\"changesDiscard\" value=\"Discard Changes\"/>\n  </div>\n";
					echo "</div>\n";
					
					// Hidden input with previously generated id - used for preventing double posts
					echo "<input type=\"hidden\" value=\"" . $_SESSION['submissionId'] . "\" name=\"submissionId\" />\n";
					echo "</form>\n";
				?>
				<?php
					echo "<form action=\"keyTable.php?keyFile=" . $keyFile . "\" method=\"post\">\n";
				    CreateSectionHeader("Key File Manipulation");
					CreateTextBox("Number of keys", "keyAmount", "10");
					CreateTextBox("Key file name", "keyFile", $keyFile);
					
					CreateRowHeader();
					echo "  <div class=\"col-5\">\n    <input type=\"submit\" class=\"form-control\" name=\"keyGenNew\" value=\"Generate New Key File\"/>\n  </div>\n";
					echo "  <div class=\"col-2\"></div>\n";
					echo "  <div class=\"col-5\">\n    <input type=\"submit\" class=\"form-control\" name=\"keyGenAppend\" value=\"Append existing Key File\"/>\n  </div>\n";
					echo "</div>\n";
					
					// Hidden input with previously generated id - used for preventing double posts
					echo "<input type=\"hidden\" value=\"" . $_SESSION['submissionId'] . "\" name=\"submissionId\" />\n";
					echo "</form>\n";
				?>
		</div>
	</body>
</html>
