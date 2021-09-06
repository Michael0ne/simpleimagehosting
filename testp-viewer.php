<?php

require("photoinfo.php");

// Переменные.
$page = $_GET['page'];
$photoId = $_GET['photoid'];
// Константа. Лучше не трогать, пока не будет переделана сетка.
define("PHOTO_PER_PAGE", 9);

// Выведем фотографию, если таковая указана.
if (isset($photoId)) {
	// Существует-ли данная фотография?
	
	// Подключаемся к БД MySQL исходя из настроек.
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);
	
	if ($mysqli->connect_error)
		die("* Ошибка при подключении к базе данных!<br />(#".$mysqli->connect_errno.": ".$mysqli->connect_error.")");

	$query = sprintf("SELECT * FROM ".DB_DEFAULTTABL." WHERE uid='%s'", $photoId);
	$result = $mysqli->query($query);

	if (!$result)
		die("* Ошибка при выполнении запроса!");
	
	if (empty($result) || $result->num_rows == 0) {
		print("<h2>Просмотр загруженной фотографии</h2>");
		print("<br />");
		print("<p>Фотографии не существует!</p>");
		print("<br />");
		print("<p>Вы можете вернуться <a href=\"index.php?act=view&page={$_GET['page']}&from=nophoto\">обратно</a>.</p>");
		
		$mysqli->close();
		
		include("testp-foot.html");
		die();
	}
	
	// Если метода fetch_all не существует.
	if (method_exists('mysqli_result', 'fetch_all') == false)
		for ($res = array(); $tmp = $result->fetch_array(MYSQLI_NUM);) $res[] = $tmp;
	else
		$res = $result->fetch_all();
	
	$data = $res[0];
	
	include("testp-photo.html");
	
	// Закрываем соединение с бд.
	$mysqli->close();
	
	// Подкючим шаблон footer.
	include("testp-foot.html");
	die();
}

print("\n<h2>Просмотр загруженных фотографий.</h2>\n");
print("<br />\n");
print("<p>Фотографии, которые были загружены другими пользователями отображены ниже.</p>\n");
print("<br />\n");

// Подключаемся к БД MySQL исходя из настроек.
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);

if ($mysqli->connect_error)
	die("* Ошибка при подключении к базе данных!<br />(#".$mysqli->connect_errno.": ".$mysqli->connect_error);

// Узнаем, сколько фотографий загружено всего.
$query = sprintf("SELECT COUNT(*) FROM ".DB_DEFAULTTABL);
$result = $mysqli->query($query);

if (!$result) {
	die("* Ошибка при выполнении запроса!<br />");
}else{
	$photosUploaded = $result->fetch_assoc();
	$photosUploaded = (int)$photosUploaded["COUNT(*)"];
}

// Нету фотографий. Предложим загрузить.
if ($photosUploaded == 0) {
	print("<div class=\"err\">");
	print("<b>Фотографий не найдено.</b>");
	print("<br />");
	print("<p>Возможно, вы не откажетесь <a href=\"index.php?act=add&from=viewer\">загрузить</a> свои?</p>");
	print("</div>");
}else{

// Фотографии есть.
print("<div class=\"photogallery\">");
print("<table id=\"photogal\">");

// Получим информацию о фотографиях.
if (isset($_GET['order'])) {
	if ($_GET['order'] == 'asc' || $_GET['order'] == 1)
		$photosArray = getPhotosInfo($mysqli, 1);
	elseif ($_GET['order'] == 'desc' || $_GET['order'] == 2)
		$photosArray = getPhotosInfo($mysqli, 2);
	elseif ($_GET['order'] == 'likes' || $_GET['order'] == 3)
		$photosArray = getPhotosInfo($mysqli, 3);
	else
		$photosArray = getPhotosInfo($mysqli, 1);
}else{
	$photosArray = getPhotosInfo($mysqli, 1);
}

// Кол-во страниц расчитываем по формуле: СТР_ВСЕГО = ceil(ФОТО_ВСЕГО / ФОТО_НА_СТРАНИЦУ).
$pagesTotal = ceil($photosUploaded / PHOTO_PER_PAGE);
if (isset($_GET['page']) && $photosUploaded >= PHOTO_PER_PAGE)
	if ($_GET['page'] == 1 || $_GET['page'] > $pagesTotal || $_GET['page'] == -1 || $_GET['page'] == 0)
		$photoIndex = 0;
	else
		// Индекс, с которого начинать, расчитываем по формуле: ИНД = (СТР - 1) * ФОТО_НА_СТРАНИЦУ.
		$photoIndex = ($_GET['page'] - 1) * PHOTO_PER_PAGE;
else
	$photoIndex = 0;

if ($photosUploaded > PHOTO_PER_PAGE) {
	// Если мы вывели девять фотографий, но на деле их больше...
	print("<tr>");
	print("<td colspan=3>");
	// Порядок сортировки.
	print("<span style=\"float: left\"><form>");
	if (isset($_GET['page']))
		print("<input type=\"hidden\" name=\"page\" value=\"".$_GET['page']."\" />");
	print("<input type=\"hidden\" name=\"act\" value=\"view\" /><select onchange=\"document.forms[0].submit();\" name=\"order\"><option>Порядок сортировки</option><option value=\"asc\">По возрастанию</option><option value=\"desc\">По убыванию</option><option value=\"likes\">По лайкам</option></select></form></span>");
	print("<span style=\"float: center\" class=\"pageselector\">");
	if (isset($_GET['page'])) {
		for ($pag = 1; $pag < $pagesTotal + 1; $pag++)
			if ($_GET['page'] == $pag)
				if ($pag >= $pagesTotal)
					print("<b>{$pag}</b>");
				else
					print("<b>{$pag}</b>&nbsp;|&nbsp;");
			else
				if ($pag >= $pagesTotal)
					if (isset($_GET['order']) && ($_GET['order'] == 1 || $_GET['order'] == 2 || $_GET['order'] == 'asc' || $_GET['order'] == 'desc'))
						print("<a href=\"index.php?act=view&page={$pag}&order={$_GET['order']}\">{$pag}</a>");
					else
						print("<a href=\"index.php?act=view&page={$pag}\">{$pag}</a>");
				else
					if (isset($_GET['order']) && ($_GET['order'] == 1 || $_GET['order'] == 2 || $_GET['order'] == 'asc' || $_GET['order'] == 'desc'))
						print("<a href=\"index.php?act=view&page={$pag}&order={$_GET['order']}\">{$pag}</a>&nbsp;|&nbsp;");
					else
						print("<a href=\"index.php?act=view&page={$pag}\">{$pag}</a>&nbsp;|&nbsp;");
				
	}else{
		for ($pag = 1; $pag < $pagesTotal + 1; $pag++)
			if ($pag >= $pagesTotal)
				if ($pag == 1)
					print("<b>{$pag}</b>");
				else
					if (isset($_GET['order']) && ($_GET['order'] == 1 || $_GET['order'] == 2 || $_GET['order'] == 'asc' || $_GET['order'] == 'desc'))
						print("<a href=\"index.php?act=view&page={$pag}&order={$_GET['order']}\">{$pag}</a>");
					else
						print("<a href=\"index.php?act=view&page={$pag}\">{$pag}</a>");
			else
				if ($pag == 1)
					print("<b>{$pag}</b>&nbsp;|&nbsp;");
				else
					if (isset($_GET['order']) && ($_GET['order'] == 1 || $_GET['order'] == 2 || $_GET['order'] == 'asc' || $_GET['order'] == 'desc'))
						print("<a href=\"index.php?act=view&page={$pag}&order={$_GET['order']}\">{$pag}</a>&nbsp;|&nbsp;");
					else
						print("<a href=\"index.php?act=view&page={$pag}\">{$pag}</a>&nbsp;|&nbsp;");
						
	}
	print("</span>");
	print("</td>");
	print("</tr>");
}

// Циклично выводим фотографии (по 3 штуки в ряд).
for ($row = 0; $row < 3; $row++) {
	if ( $photoIndex >= $photosUploaded )
		break;

	print("<tr>");

	for ($cell = 0; $cell < 3; $cell++) {
		if ( $photoIndex >= $photosUploaded )
			break;

		print("<td>");

		$photoInfo = $photosArray[$photoIndex];
		$photoIndex++;
		
		// 0 - uid
		// 1 - date
		// 2 - email
		// 3 - phone
		// 4 - base path
		// 5 - base thumb path
		// 6 - global path
		// 7 - global thumb path
		print("<span class=\"galimg\">");
		print("<img id=\"galimg-{$row}-{$cell}\" image-uid=\"{$photoInfo[0]}\" src=\"{$photoInfo[7]}\" />");
		print("<br />");
		print("<span class=\"galimg imgdesc\">Загрузил: ");
		if ($photoInfo[2] == "anonymous@nomail.gov" || $photoInfo[2] == G_IMPEMAIL)
			print("<i>аноним</i>");
		else
			print("<a href=\"mailto: {$photoInfo[2]}\">{$photoInfo[2]}</a> (<i>{$photoInfo[3]}</i>)");
		print("<br />");
		print($photoInfo[1]."</span>");
		print("</span>");
		
		print("</td>");
	}
	
	print("</tr>");
}

if ($photosUploaded > PHOTO_PER_PAGE) {
	// Если мы вывели девять фотографий, но на деле их больше...
	print("<tr>");
	print("<td colspan=3>");
	print("<span class=\"pageselector\">");
	if (isset($_GET['page'])) {
		for ($pag = 1; $pag < $pagesTotal + 1; $pag++)
			if ($_GET['page'] == $pag)
				if ($pag >= $pagesTotal)
					print("<b>{$pag}</b>");
				else
					print("<b>{$pag}</b>&nbsp;|&nbsp;");
			else
				if ($pag >= $pagesTotal)
					if (isset($_GET['order']) && ($_GET['order'] == 1 || $_GET['order'] == 2 || $_GET['order'] == 'asc' || $_GET['order'] == 'desc'))
						print("<a href=\"index.php?act=view&page={$pag}&order={$_GET['order']}\">{$pag}</a>");
					else
						print("<a href=\"index.php?act=view&page={$pag}\">{$pag}</a>");
				else
					if (isset($_GET['order']) && ($_GET['order'] == 1 || $_GET['order'] == 2 || $_GET['order'] == 'asc' || $_GET['order'] == 'desc'))
						print("<a href=\"index.php?act=view&page={$pag}&order={$_GET['order']}\">{$pag}</a>&nbsp;|&nbsp;");
					else
						print("<a href=\"index.php?act=view&page={$pag}\">{$pag}</a>&nbsp;|&nbsp;");
				
	}else{
		for ($pag = 1; $pag < $pagesTotal + 1; $pag++)
			if ($pag >= $pagesTotal)
				if ($pag == 1)
					print("<b>{$pag}</b>");
				else
					if (isset($_GET['order']) && ($_GET['order'] == 1 || $_GET['order'] == 2 || $_GET['order'] == 'asc' || $_GET['order'] == 'desc'))
						print("<a href=\"index.php?act=view&page={$pag}&order={$_GET['order']}\">{$pag}</a>");
					else
						print("<a href=\"index.php?act=view&page={$pag}\">{$pag}</a>");
			else
				if ($pag == 1)
					print("<b>{$pag}</b>&nbsp;|&nbsp;");
				else
					if (isset($_GET['order']) && ($_GET['order'] == 1 || $_GET['order'] == 2 || $_GET['order'] == 'asc' || $_GET['order'] == 'desc'))
						print("<a href=\"index.php?act=view&page={$pag}&order={$_GET['order']}\">{$pag}</a>&nbsp;|&nbsp;");
					else
						print("<a href=\"index.php?act=view&page={$pag}\">{$pag}</a>&nbsp;|&nbsp;");
						
	}
	print("</span>");
	print("</td>");
	print("</tr>");
}

print("</table>");
print("</div>");
print("<div class=\"foot\"><a href=\"index.php?act=add&from=viewer\">Загрузить свою фотографию!</a></div>");
}

// Закрываем соединение с бд.
$mysqli->close();
?>
