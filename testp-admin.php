<?php

// Панель администратирования.
// Функции:
//	* удаление уже существующих фотографий
//	* сканирование директории с фотографиями и импорт фотографий, которых нету в бд
//	* переименование фотографий
//	* генерация превью для фотографии(-й ?)

if (isset($_COOKIE["adminHash"])) {
	if ($_COOKIE["adminHash"] == md5(ADM_PASSWORD))
		// Пароль взятый из cookie верен. Можно запустить панель управления сайтом.
		if (isset($_GET["logout"])) {
			setcookie("adminHash", null, -1);
			Header("Location: index.php");
		}
		
		if (isset($_GET["main"]) == false)
			if (isset($_GET["moretime"]))
				// Продлеваем сессию ещё на n минут.
				setcookie("adminHash", $_COOKIE["adminHash"], time()+60*ADM_SESSLENG);
				
		
		$authorized = true;
}elseif(isset($_POST["adminHash"])) {
	if (md5($_POST["adminHash"]) == md5(ADM_PASSWORD)) {
		// Пароль взятый из запроса верен. Установим cookie и запустим панель управления сайтом.
		// Cookie истечёт через n минут.
		setcookie("adminHash", md5($_POST["adminHash"]), time()+60*ADM_SESSLENG);
		$authorized = true;
		// Был передан POST запрос, следовательно в строке пусто. Перейдём на страницу заново.
		if (substr_count($_SERVER["HTTP_REFERER"], "&section") == 1 || substr_count($_SERVER["HTTP_REFERER"], '&') >= 1)
			Header("Location: ".$_SERVER["HTTP_REFERER"]);
		else
			Header("Location: index.php?act=admin");
	}else{
		$authorized = false;
		$wrongpass = true;
	}
}else{
	$wrongpass = false;
	$authorized = false;
}

if ($authorized == false) {
	if ($wrongpass == true)
		// Был введён неверный пароль. Укажем на это.
		include("testp-admin-auth-wpa.html");
	else
		// Мы неавторизованы. Выведем форму авторизации.
		include("testp-admin-auth.html");
	
	// Подкючим шаблон footer.
	include("testp-foot.html");
	
	die();
}

// Авторизовались. Покажем саму панель управления сайтом.

// Вырежем из URL страницу, которую мы хотим отобразить.
$adminPage = (strpos(urldecode($_SERVER["REQUEST_URI"]), '&') != 0) ? substr(urldecode($_SERVER["REQUEST_URI"]), strpos(urldecode($_SERVER["REQUEST_URI"]), '&') + 1) : "main";

// Если есть ещё какие-то параметры, то их тоже вырежем в отдельный массив.
if (strpos($adminPage, '&') != 0) {
	$paramsArray = explode('&', substr($adminPage, strpos($adminPage, '&') + 1));
	$adminPage = substr($adminPage, 0, strpos($adminPage, '&'));
	
	foreach($paramsArray as $paramIndex => $paramValue)
		$params[substr($paramValue, 0, strpos($paramValue, '='))] = substr($paramValue, strpos($paramValue, '=') + 1);
	
	unset($paramsArray);
}


print("<div id=\"nav\" role=\"nav\" style=\"background-color: white;padding: 5px 10px 5px 10px;margin: 0;\">");
// Выведем ссылки на все страницы что есть.
foreach($pageNames as $pageName => $pageDescription)
	print("<a href=\"?act=admin&" . $pageName . "\">" . $pageDescription . "</a>&nbsp;|&nbsp;");
print("<a style=\"background-color: #ff4646\" href=\"?act=add\">Сайт</a>
</div>");
print("<div style=\"padding: 10px 0 10px 0px;margin: 0\">");
print("<h2>".($pageNames[$adminPage] != null ? $pageNames[$adminPage] : "Неизвестная страница").($sectionNames[$params["section"]] != null ? " | ".$sectionNames[$params["section"]] : "")."</h2>");

switch($adminPage) {
	case 'main':
		// Страница с описанием и навигацией.
		include_once("testp-admin-main.php");
		break;
	case 'moretime':
		// Больше времени для текущей сессии.
		print("<p>Сессия успешно продлена на ".ADM_SESSLENG." минут.</p>");
		print("<br />");
		print("<a href=\"{$_SERVER["HTTP_REFERER"]}\">Обратно</a>");
		break;
	case 'photos':
		// Страница с управлением фотографиями.
		include_once("testp-admin-photos.php");
		break;
	case 'reports':
		// Страница с жалобами на фотографии.
		include_once("testp-admin-reports.php");
		break;
	default:
		// Неизвестная страница.
		print("<p>Данной страницы не существует</p>");
		break;
}

print("</div>");

?>