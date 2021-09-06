<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Подключим файл с конфигурацией.
require("config.php");

$act = $_POST['act'] or die("* Не указано действие.");

unset($_REQUEST['act']);
$param = $_REQUEST;

if (empty($param))
	die("* Не указаны параметры запроса.");
	
foreach($param as $paramName => $paramVal)
	switch($paramName) {
		case 'last':
			// Последние жалобы.
			requestLastReports();
			break;
		case 'day':
			if ($act == 'find')
				// Жалобы за определнный день.
				requestReportsForDay($paramVal);
			break;
		case 'photoid':
			$addReport = true;
			break;
		case 'reasonid':
			// Добавляем жалобу.
			requestNewReport($_REQUEST['photoid'], $_REQUEST['reasonid']);
			break;
		case 'id':
			// Удаляем жалобу.
			if (isset($paramVal) && $act == "remove")
				requestReportRemoval($paramVal);
			break;
		default:
			print "* Неизвестный параметр.";
			break;
	}

exit;

function requestReportRemoval($reportId) {
	// Подключаемся к БД MySQL исходя из настроек.
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);

	if ($mysqli->connect_error)
		return print json_encode(Array("error" => $mysqli->connect_error));

	$query = sprintf("DELETE FROM `reports` WHERE `rid` = '" . $reportId . "'");
	$result = $mysqli->query($query);

	if (!$result)
		print json_encode(Array("error" => "Ошибка при выполнении запроса."));
	else
		print json_encode(Array("result" => "true"));
}

function requestNewReport($photoId, $reasonId) {
	// Подключаемся к БД MySQL исходя из настроек.
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);

	if ($mysqli->connect_error)
		return print json_encode(Array("error" => $mysqli->connect_error));

	$reasonsFile = fopen("reasons.txt", 'r');
	$line = 0;
	while (!feof($reasonsFile)) {
		$reason = fgets($reasonsFile);

		$reason = trim($reason);

		if (strcasecmp($reason, $reasonId) == 0)
			$reasonId = $line;
		$line++;
	}
	fclose($reasonsFile);

	$query = sprintf("INSERT INTO `reports` (`rid`, `photoid`, `reason`) VALUES ('".rand()."',  '".$photoId."',  '".$reasonId."');");
	$result = $mysqli->query($query);

	if (!$result)
		return print json_encode(Array("error" => "Ошибка при выполнении запроса."));
}

function requestLastReports() {
}

function requestReportsForDay($date) {
	// Подключаемся к БД MySQL исходя из настроек.
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);

	if ($mysqli->connect_error)
		return print json_encode(Array("error" => $mysqli->connect_error));

	$query = sprintf("SELECT COUNT(*) FROM reports");
	$result = $mysqli->query($query);

	if (!$result)
		return print json_encode(Array("error" => "Ошибка при выполнении запроса."));

	$result = $result->fetch_assoc();
	$totalReportsSend = $result["COUNT(*)"];

	print json_encode($totalReportsSend);
}

?>