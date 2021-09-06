<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once("config.php");

$uid = rand();

// Узнать расширение файла.
$image_extension = substr($_FILES["form-upload-image"]["name"], strpos($_FILES["form-upload-image"]["name"], '.') + 1, strlen($_FILES["form-upload-image"]["name"]));
// Узнать имя файла. Если оно длиннее 6 букв - обрезать до 5 и вставить расширение после.
$image_name = (strlen($_FILES["form-upload-image"]["name"]) >= 6) ? (substr($_FILES["form-upload-image"]["name"], 0, 5))."...".$image_extension : $_FILES["form-upload-image"]["name"];
// Путь к файлу (локальный) и его имя + расширение.
$image_name_loc = FS_UPLOADDIR . $uid . "_" . basename($_FILES["form-upload-image"]["name"]);


// Размер файла не должен быть более 20 Mb!
if ($_FILES["form-upload-image"]["size"] > 20971520) {
	Header("Location: index.php?act=add&errtype=maxsizelimit");
	die();
}

// Загружаем файл.
if ( is_uploaded_file($_FILES["form-upload-image"]["tmp_name"]) )
	if ( move_uploaded_file($_FILES["form-upload-image"]["tmp_name"], $image_name_loc ) )
		$upload_success = true;
	
if ($upload_success == false) {
	Header("Location: index.php?act=add&errtype=uploadfail");
	die();
}

// Картинка загружена. Сделаем уменьшенную копию (если размеры больше 250х250).
$image = new Imagick($image_name_loc);
$image->adaptiveResizeImage(250, 250);
$data = $image->getImageBlob();
file_put_contents(FS_UPLOADDIR . "/thumbs/" . $uid . "_". basename($_FILES["form-upload-image"]["name"]), $data);
	
// Подключаемся к БД MySQL исходя из настроек.
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);

if ($mysqli->connect_error) {
	die("* Ошибка при подключении к базе данных!<br />(#".$mysqli->connect_errno.": ".$mysqli->connect_error);
}

// Email адрес.
$emailAddr = $_POST["form-upload-email"];
// Номер телефона.
$phoneNumber = $_POST["form-upload-phone"];
// Пароль для удаления.
$delpassw = $_POST["form-upload-passw"];

// Заносим данные в таблицу.
$query = sprintf("INSERT INTO ".DB_DEFAULTTABL." (`uid`, `date`, `email`, `phone`, `imagelocal`, `imagelocal_thumb`, `imageglobal`, `imageglobal_thumb`, `delpassw`) VALUES ('%s', NOW(), '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
				$uid, 
				$emailAddr, 
				$phoneNumber, 
				$mysqli->real_escape_string($image_name_loc),
				$mysqli->real_escape_string(FS_UPLOADDIR . "thumbs/" . $uid . "_" . basename($_FILES["form-upload-image"]["name"])),
				$mysqli->real_escape_string(FS_SITEDIR . "uploads/" . $uid . "_" . basename($_FILES["form-upload-image"]["name"])),
				$mysqli->real_escape_string(FS_SITEDIR . "uploads/thumbs/" . $uid . "_" . basename($_FILES["form-upload-image"]["name"])),
				$mysqli->real_escape_string($delpassw)
				);
$result = $mysqli->query($query);

if (!$result) {
	die("* Ошибка при выполнении запроса!<br />");
}

// Закрываем соединение с бд.
$mysqli->close();

// Отправим письмо с уведомлением о том, что было добавлено новое фото.
// Переменные необходимые для отправки письма.

// Текст сообщения
$msg = "<h2>Добрый день, ". MAIL_MYNAME . "!</h2><br />";
$msg .= "<p>Было добавлено новое изображение: <a href=\"". FS_SITEDIR . "uploads/" . $uid . "_" .$_FILES["form-upload-image"]["name"] ."\">открыть на сайте</a><sup>(прямая ссылка)</sup>.</p><br />";
$msg .= "<p>Отправитель оставил данные обратной связи:<br />";
$msg .= "<ul><li>Телефон: {$phoneNumber}</li><li>E-mail: <a href=\"mailto:{$emailAddr}\">{$emailAddr}</a></li></ul><br />";
$msg .= "<br />Хорошего дня!";

// Заголовки.
$sid = md5(uniqid(time()));

$headers = "From: " . MAIL_SENDER . "\n";
$headers .= "Reply-To: " . MAIL_REPLYTO . "\n";
$headers .= "MIME-Version: 1.0\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"".$sid."\"\n\n";
$headers .= "Multipart message begins.\n";

$headers .= "--".$sid."\n";
$headers .= "Content-Type: text/html; charset=utf-8\n";
$headers .= "Content-Transfer-Encoding: 7bit\n\n";
$headers .= $msg."\n\n";

$headers .= "--".$sid."\n";
$headers .= "Content-Type: application/octet-stream; name=\"". $uid . "_" . $_FILES["form-upload-image"]["name"] ."\"\n";
$headers .= "Content-Transfer-Encoding: base64\n";
$headers .= "Content-Disposition: attachment; filename=\"". $uid . "_" . $_FILES["form-upload-image"]["name"] ."\"\n\n";
$headers .= chunk_split(base64_encode(file_get_contents($image_name_loc)))."\n\n";

// Тема сообщения.
$subject = "Добавлено новое фото ({$image_name})";

// Отправка письма.
@mail(MAIL_SENDTO, $subject, null, $headers);

// Перенаправление обратно, на страницу просмотра изображений.
die("<script>location.replace(\"index.php?act=view&from=add\");</script>");

?>