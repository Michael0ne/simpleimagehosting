<?php
// СЕРВЕРНЫЕ НАСТРОЙКИ

// Уровень сжатия JPEG.
define("G_COMPRESSIONLEVEL", 100);
// Коэффицинт, после которого уменьшенной версии изображения нужно будет изменить ширину, дабы не нарушить пропорциональность.
define("G_COEIMG", 40);
// Максимальное количество попыток неудачного ввода пароля для удаления картинки.
define("G_MAX_WPADEL", 4);
// Удалять фотографии окончательно, либо оставлять их удаляя лишь запись из БД.
define("G_KEEPIMGALIVE", true);
// Данный e-mail будет использоваться при импорте фотографий. Если null (или undefined, 0, nil, -1, no, none, false) - использовать anonymous@nomail.gov.
define("G_IMPEMAIL", "false");

// АДМИНИСТРАТИРОВАНИЕ

// Пароль для доступа к панели администратора.
define("ADM_PASSWORD", "admin" . date('j'));
// Срок действия сессии (в минутах). (по умолчанию - 10 минут).
define("ADM_SESSLENG", 60);
// Массив с названиями страниц.
$pageNames = Array(
	'main' => "Панель",
	'moretime' => "Продлить сессию",
	'photos' => 'Управление фотографиями',
	'reports' => 'Жалобы',
	'logout' => 'Выйти'
);
// Массив с названиями секций.
$sectionNames = Array(
	'main' => 'Главная',
	'uploadsby' => 'Загрузки пользователя' . (isset($_GET['showu']) ? ' (' . $_GET['showu'] . ')' : ''),
	'importp' => 'Импорт',
	'last' => 'Недавние',
	'search' => 'Поиск',
);
// Массив с текстами используемыми в шаблоне вывода даты жалоб.
$reportsDates = Array(
	'last24' => 'за посление 24 часа.',
	'last12' => 'за последние 12 часов.',
	'lastX' =>  'за последние(-й) %1 час(-ов).'
);
// Массив с текстами используемыми на странице с выбором секции управления фото.
$photosPageLinks = Array(
	'uploadsby' => 'Показать загрузки пользователя',
	'importp' => 'Импорт и проверка фотографий',
);

// БАЗА ДАННЫХ

// Сервер БД.
define("DB_SERVER", "localhost");
// Имя пользователя.
define("DB_USER", "root");
// Пароль к бд.
define("DB_PASSWORD", "root");
// База к которой подключаемся по умолчанию.
define("DB_DEFAULTDB", "gallery");
// Таблица, в которой будет содержаться информация о фотографиях.
define("DB_DEFAULTTABL", "uphoto");

// ПОЧТА

// Что будет в заголовке "отправитель".
define("MAIL_SENDER", "webmaster@example.com");
// Кому посылать ответ.
define("MAIL_REPLYTO", "webmaster@example.com");
// Ваше имя (будет использовано при обращении).
define("MAIL_MYNAME", "Administrator");
// Кому отправлять уведомление.
define("MAIL_SENDTO", "webmaster@example.com");

// ФАЙЛОВАЯ СИСТЕМА

// Путь к директории для загрузки файлов (включая слэш).
define("FS_UPLOADDIR", "/imagehosting/uploads/");
// Базовый путь к сайту. Я использовал Денвер (Denwer) который автоматически, при установке, создал мне локальный домен test1.ru.
define("FS_SITEDIR", "http://example.com/");

// ПЕРВОНАЧАЛЬНАЯ НАСТРОЙКА

// Если не существуют нужные папки...
if (!file_exists("uploads/")) {
	mkdir("uploads/", 0755);
	mkdir("uploads/thumbs/", 0755);
}elseif (!file_exists("uploads/thumbs/")) {
	mkdir("uploads/thumbs/", 0755);
}

// Если не существует нужная таблица...
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);
if ( $mysqli->query("SHOW TABLES LIKE '".DB_DEFAULTTABL."'")->num_rows == 0 )
	if ( $mysqli->query("CREATE TABLE ".DB_DEFAULTTABL."(uid INT NOT NULL, PRIMARY KEY(uid), date TIMESTAMP, email TEXT, phone TEXT, imagelocal TEXT, imagelocal_thumb TEXT, imageglobal TEXT, imageglobal_thumb TEXT)") === true )
		$mysqli->close();
	else
		$mysqli->close();
else
	$mysqli->close();

// ФУНКЦИИ

// Создаёт соединение и возвращает его дескриптор.
function open_mysqli() {
	return $mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);
}

?>