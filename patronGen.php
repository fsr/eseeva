<?php
//============================================================================
// Name        : patronGen.php
// Author      : Patrick Reipschläger, Dirk Legler
// Version     : 1.0
// Date        : 10-2013
// Description : For generating the group name tickets.
//============================================================================
	include_once 'libs/keyLib.php';
	include_once 'libs/formLib.php';
	include_once 'libs/questionnaireLib.php';

	// start a session prevent sending the same post twice
	// if the user refreshes the page, it will default to the
	// access code screen
	//session_start();

	// the key file which should be edited, either the default key file or the one that was specified
	$keyFile = KEYFILE;
	if (isset($_GET["keyFile"]))
		$keyFile = $_GET["keyFile"];
	// holds the key data, generated from the key file
	// $keyData = ReadKeyFile($keyFile);
	// the group names and rooms for Monday and their start times for Tuesday, removes "Nicht am Tutorium teilgenommen"
	$patrons = ReadPatronsFile(PATRONS);
	array_splice($patrons, 0, 1);

?>
<!DOCTYPE html>
<html>
	<head>
    <meta charset="utf-8">
		<title>Namenspatronzettel</title>
		<link rel="stylesheet" type="text/css" href="css/print.css" />
	</head>
	<body>
		<div class="container">
<?php
for ($i = 0; $i < 400; $i++)
	{
		$pno = $i%(count(array_keys($patrons)) - 1);
		$patron_name = array_keys($patrons)[$pno];
		?>
			<div class="ticket">
				<div class="patron"><?php echo $patron_name; ?></div>
				<div class="tickettext">
					<div class="ticketimg"><img src="css/ese-logo.png"/></div>
					<div class="room">Tutorium in <br><strong><?php echo $patrons[$patron_name][1]; ?></strong></div>
					<div class="time">
						<?php
							echo "Einschreibestart Mittwoch <br><strong>".$patrons[$patron_name][2]." Uhr</strong>";
						?>
					</div>
				</div>
			</div>
		<?php
	}
for ($i = 0; $i < 50; $i++)
	{
		$pno = 16;//$i%(count(array_keys($patrons)));
		$patron_name = array_keys($patrons)[$pno];
		?>
			<div class="ticket">
				<div class="patron"><?php echo $patron_name; ?></div>
				<div class="tickettext">
					<div class="ticketimg"><img src="css/ese-logo.png"/></div>
					<div class="room">Tutorium in <br><strong><?php echo $patrons[$patron_name][1]; ?></strong></div>
					<div class="time">
						<?php
							// don't display start date if there is none (Master case)
							echo "&nbsp;";
						?>
					</div>
				</div>
			</div>
		<?php
	}
?>
		</div>
	</body>
</html>
