<?php

// Конфигурация уже подключена.

// Сначала выведем статистику по сайту. Сколько изображений всего есть в БД.

// Подключаемся к БД MySQL исходя из настроек.
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);
	
if ($mysqli->connect_error)
	print("* Ошибка при подключении к базе данных!<br />(#".$mysqli->connect_errno.": ".$mysqli->connect_error.")");

$query = sprintf("SELECT COUNT(*) FROM ".DB_DEFAULTTABL);
$result = $mysqli->query($query);

if (!$result)
	print("* Ошибка при выполнении запроса!");

$result = $result->fetch_assoc();
$totalPhotosUploaded = $result["COUNT(*)"];

$query = sprintf("SELECT `email` FROM ".DB_DEFAULTTABL." WHERE 1");
$result = $mysqli->query($query);

if (!$result)
	print("* Ошибка при выполнении запроса!");

while ($row = $result->fetch_row())
	$uploadersArray[] = $row[0];
if ($uploadersArray != null) $uploadersArray = array_count_values($uploadersArray);

// Закрываем соединение с бд.
$mysqli->close();

print("<table><tr><td style=\"border-bottom: 1px solid black\" colspan=2>Статистика</td></tr><tr><td>Фотографий загружено:</td><td><i>$totalPhotosUploaded</i></td></tr></table>");
print("<br />");
print("<table><tr><td style=\"border-bottom: 1px solid black\" colspan=2>Аплоадеры</td></tr>");
if ($uploadersArray != null) foreach($uploadersArray as $uploaderName => $uploadsTotal) {
print("<tr><td><a href=\"index.php?act=admin&photos&section=uploadsby&showu=$uploaderName\">$uploaderName</a></td><td>$uploadsTotal</td></tr>"); }
print("</table>");

?>