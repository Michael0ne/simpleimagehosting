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
		case 'uploadsby':
			//	Покажем загрузки пользователя.
			$showUser = (isset($params["showu"])) ? include_once("testp-admin-photos-photosby.html") : include_once("testp-admin-photos-photosbyn.html");
			break;
		case 'importp':
			//	Импорт фотографии(-й).
			$showImport = true;
			include_once("testp-admin-photos-imp.html");
			break;
		default:
			print("<p>Данной секции не существует.</p>");
			break;
	}
}else{
	// Секция не выбрана. Покажем выбор секции и возможности управления фотографиями.
	foreach ($photosPageLinks as $actionLink => $actionDescription)
		print "<a href=\"index.php?act=admin&{$adminPage}&section={$actionLink}\">{$actionDescription}</a><br />";
	print("<a href=\"index.php?act=add&from=admin\">Добавить фотографию</a><br />");
}

?>