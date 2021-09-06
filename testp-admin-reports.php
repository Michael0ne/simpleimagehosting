<?php


// Конфигурация уже подключена.
// $params содержит все доп. параметры запроса к странице., где ключ = название параметра, значение = значение параметра.

if (!isset($params["section"]))
	$section = false;
else
	$section = $params["section"];

// Обработаем частные случаи.
if ($section != false) {
	// Покажем опр. секцию.
	switch ($section) {
		case 'last':
			// Секция с жалобами за последнее время (от 1 часа до 24 часов).
			include_once("testp-admin-reports-last.html");
			break;
		case 'search':
			// Поиск по жалобам с возможностью настройки параметров.
			include_once("testp-admin-reports-search.html");
			break;
		default:
			print("<p>Данной секции не существует.</p>");
			break;
	}
}else{
	// Секция не выбрана. Предложим выбрать самостоятельно.
	print("<a href=\"index.php?act=admin&{$adminPage}&section=last\">Жалобы за последнее время</a><br />");
	print("<a href=\"index.php?act=admin&{$adminPage}&section=search\">Поиск по жалобам</a>");
}

?>