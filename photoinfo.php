<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Настройки уже подключены.

function photoGetLike($id) {
	if ($id == false)
		die("{ \"result\": \"error\", \"error\": \"Не указано ID фотографии.\" }");

	// Подключаемся к БД MySQL исходя из настроек.
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);

	if ($mysqli->connect_error)
		die("{ \"result\": \"error\", \"error\": \"Ошибка при подключении к базе данных! (#".$mysqli->connect_errno.": ".$mysqli->connect_error.")");

	$result = $mysqli->query("SELECT `liked` FROM `uphoto` WHERE `uid` = '" . $id . "'");

	if (!$result)
		die("{ \"result\": \"error\", \"error\": \"Неудалось выполнить запрос к БД!\" }");

	$result = $result->fetch_array();
	$result = (int)$result['liked'];

	// Закроем активное соединение с БД.
	$mysqli->close();
	
	if (isempty($result))
		$result = 0;

	die("{ \"result\": \"success\", \"value\": \"".$result."\" }");
}

// Данная ф-ция добавляет "лайк" к указанной фотографии.
// На входе:	id - ID фотографии.
// На выходе:	JSON object.
function photoAddLike($id) {
	if ($id == false)
		die("{ \"result\": \"error\", \"error\": \"Не указано ID фотографии.\" }");

	// Подключаемся к БД MySQL исходя из настроек.
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);

	if ($mysqli->connect_error)
		die("{ \"result\": \"error\", \"error\": \"Ошибка при подключении к базе данных! (#".$mysqli->connect_errno.": ".$mysqli->connect_error.")");

	$result = $mysqli->query("SELECT `liked` FROM `uphoto` WHERE `uid` = '" . $id . "'");

	if (!$result)
		die("{ \"result\": \"error\", \"error\": \"Неудалось выполнить запрос к БД!\" }");

	$result = $result->fetch_array();
	$photoLikesTotal = (int)$result['liked'];
	$photoLikesTotal++;

	$result = $mysqli->query("UPDATE `uphoto` SET `liked` = '" . $photoLikesTotal . "' WHERE `uid` = '" . $id . "'");

	if (!$result)
		die("{ \"result\": \"error\", \"error\": \"Неудалось выполнить запрос к БД!\" }");

	$result = $result->fetch_array();
	$result = (int)$result['liked'];

	// Закроем активное соединение с БД.
	$mysqli->close();

	die("{ \"result\": \"success\", \"value\": \"".$result."\" }");
}

// Данная ф-ция выводи информацию о фотографии в формате JSON.
// На входе:	id - ID фотографии, field - поле, значение которого необходимо узнать.
// На выходе:	JSON object.
function getPhotoInfo($id, $field) {
	if ($field == false || $id == false)
		die("{ \"result\": \"error\", \"error\": \"Не указан параметр или ID фотографии.\" }");

	// Подключаемся к БД MySQL исходя из настроек.
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);

	if ($mysqli->connect_error)
		die("{ \"result\": \"error\", \"error\": \"Ошибка при подключении к базе данных! (#".$mysqli->connect_errno.": ".$mysqli->connect_error.")");

	$result = $mysqli->query("SELECT * FROM `uphoto` WHERE `uid` = '" . $id . "'");

	if (!$result)
		die("{ \"result\": \"error\", \"error\": \"Неудалось выполнить запрос к БД!\" }");

	$result = $result->fetch_array();

	// Закроем активное соединение с БД.
	$mysqli->close();

	switch ($field) {
		case 'date':
		case 1:
			die("{ \"result\": \"true\", \"value\": \"" . $result['date'] . "\" }");
		case 'email':
		case 2:
			die("{ \"result\": \"true\", \"value\": \"" . $result['email'] . "\" }");
		case 'phone':
		case 3:
			die("{ \"result\": \"true\", \"value\": \"" . $result['phone'] . "\" }");
		case 'imageglobal':
		case 4:
			die("{ \"result\": \"true\", \"value\": \"" . $result['imageglobal'] . "\" }");
		case 'imageglobal_thumb':
		case 5:
			die("{ \"result\": \"true\", \"value\": \"" . $result['imageglobal_thumb'] . "\" }");
		case 'delpassw':
		case 6:
			if (isset($_COOKIE['adminHash']) && $_COOKIE['adminHash'] == ADM_PASSWORD)
				die("{ \"result\": \"true\", \"value\": \"" . $result['delpassw'] . "\" }");
			else
				die("{ \"result\": \"error\", \"error\": \"Параметр указан неверно.\" }");
		default:
			die("{ \"result\": \"error\", \"error\": \"Параметр указан неверно.\" }");
	}
}

// Данная ф-ция выводит информацию о фотографиях в формате JSON.
// На входе:	db - активное соединение с БД и открытой таблицей с фотографиями.
// На выходе:	JSON object.
function getPhotosInfo($db, $order = 1) {
		// Соединение установлено заранее. Просто производим выборку из таблицы.
	if ($order == 1)
		$query = sprintf("SELECT * FROM ".DB_DEFAULTTABL." ORDER BY `date` ASC");
	if ($order == 2)
		$query = sprintf("SELECT * FROM ".DB_DEFAULTTABL." ORDER BY `date` DESC");
	if ($order == 3)
		$query = sprintf("SELECT * FROM ".DB_DEFAULTTABL." ORDER BY `liked` DESC");
	$result = $db->query($query);
	
	if (!$result)
		return "{ error: \"".$db->connect_error."\" }";
	
	// Если метода fetch_all не существует.
	if (method_exists('mysqli_result', 'fetch_all') == false)
		for ($res = array(); $tmp = $result->fetch_array(MYSQLI_NUM);) $res[] = $tmp;
	else
		$res = $result->fetch_all();
	
	return $res;
}

/**
 * @brief Удаляет фотографию указанную в $id.
 */
function photoDelete($db, $id) {
	if (!$id)
		die("{ \"result\": \"error\", \"error\": \"Не указан id фотографии.\" }");
	
	if (!$db)
		die("{ \"result\": \"error\", \"error\": \"Невозможно подключиться к базе данных.\" }");
	
	if (is_array($id)) {
		// Указан массив ID. Проходимся по нему и удаляем каждую фотографию.
		
		foreach($id as $index) {
			$query = sprintf("SELECT `imagelocal`,`imagelocal_thumb` FROM `uphoto` WHERE `uid`='%s'",
							$db->real_escape_string($index)
							);
			$result = $db->query($query);
			if (!$result)
				die("{ \"result\": \"error\", \"error\": \"Ошибка при выполнении запроса к базе данных!\" }");
			
			$result = $result->fetch_assoc();
			$imgpath = $result["imagelocal"];
			$thupath = $result["imagelocal_thumb"];
			
			// Удаляем запись из дб.
			$query = sprintf("DELETE FROM `uphoto` WHERE `uid` = '%s'",
							$db->real_escape_string($index)
							);
			$result = $db->query($query);
			if (!$result)
				die("{ \"result\": \"error\", \"error\": \"Ошибка при выполнении запроса к базе данных!\" }");
	
			// Удаляем окончательно, только лишь если так указано в настройках.
			if (G_KEEPIMGALIVE) {
				// Всё хорошо, удаляем фотографию.
				unlink($imgpath);
				unlink($thupath);

				$db->close();
				die("{ \"result\": \"succ\" }");
			}else{
				$db->close();
				die("{ \"result\": \"succ\" }");
			}
		}
	}else{
		// Просто строка. Всё как обычно.
			
		// Выполняем запрос к бд - получаем информацию о путях к картинке.
		$query = sprintf("SELECT `imagelocal`,`imagelocal_thumb` FROM `uphoto` WHERE `uid`='%s'",
						$db->real_escape_string($id)
						);
		$result = $db->query($query);
		if (!$result)
			die("{ \"result\": \"error\", \"error\": \"Ошибка при выполнении запроса к базе данных!\" }");
	
		$result = $result->fetch_assoc();
		$imgpath = $result["imagelocal"];
		$thupath = $result["imagelocal_thumb"];
	
		// Удаляем запись из дб.
		$query = sprintf("DELETE FROM `uphoto` WHERE `uid` = '%s'",
						$db->real_escape_string($id)
						);
		$result = $db->query($query);
		if (!$result)
			die("{ \"result\": \"error\", \"error\": \"Ошибка при выполнении запроса к базе данных!\" }");

		// Удаляем окончательно, только лишь если так указано в настройках.
		if (G_KEEPIMGALIVE) {
			// Всё хорошо, удаляем фотографию.
			unlink($imgpath);
			unlink($thupath);
		}
	}
}

/**
 * @brief Проверяет правильность ввода пароля для картинки. Максимальное количество разрешённых попыток определено константой G_MAX_WPADEL (4).
 */
function getPhotoPasswd($id, $pw) {
	
	if (isset($_COOKIE["passwordAttemptsFailed"]))
		if ($_COOKIE["passwordAttemptsFailed"] == G_MAX_WPADEL)
			die("{ \"result\": \"error\", \"error\": \"Исчерпан лимит на удаление фотографии, слишком много неверных попыток ввода пароя.\" }");
	
	if (!$id)
		if (!$pw)
			die("{ \"result\": \"error\", \"error\": \"Не указаны id фотографии и пароль.\" }");
		else
			die("{ \"result\": \"error\", \"error\": \"Не указан id фотографии.\" }");

	// Подключаемся к БД MySQL исходя из настроек.
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);
	
	if ($mysqli->connect_error)
		die("{ \"result\": \"error\", \"error\": \"Ошибка при подключении к базе данных! (#".$mysqli->connect_errno.": ".$mysqli->connect_error.")");
	
	// Выполняем запрос к бд - ищем пароль.
	$query = sprintf("SELECT `delpassw` FROM `uphoto` WHERE `uid`='%s'",
					$mysqli->real_escape_string($id)
					);
	$result = $mysqli->query($query);
	if (!$result)
		die("{ \"result\": \"error\", \"error\": \"Ошибка при выполнении запроса к базе данных!\" }");
	
	// В $result находится пароль. Надо сверить его с предложенным.
	$result = $result->fetch_assoc();
	$result = $result["delpassw"];
	
	if ($pw === $result || $_COOKIE["adminHash"] == md5(AD_PASSWORD)) {
		print("{ \"result\": \"true\" }");
		
		// Тут же удалим фотографию.
		photoDelete($mysqli, $id);
	}else{
		if (isset($_COOKIE["passwordAttemptsFailed"]) == false) {
			setcookie("passwordAttemptsFailed", 1, time()+60);
			print("{ \"result\": \"false\", \"attm\": \"0\", \"attmt\": \"".G_MAX_WPADEL."\" }");
		}else{
			setcookie("passwordAttemptsFailed", $_COOKIE["passwordAttemptsFailed"] + 1, time()+60);
			print("{ \"result\": \"false\", \"attm\": \"".$_COOKIE["passwordAttemptsFailed"]++."\", \"attmt\": \"".G_MAX_WPADEL."\" }");
		}
 
	} 
	
	// Закрываем соединение с бд.
	$mysqli->close();
	
}

/**
 * @brief	Генерирует превью для $fn фотографии.
*/
function photoMakeThumb($fn, $ft) {
	if (!$fn || !$ft)
		die("{ \"result\": \"error\", \"error\": \"Не указан id фотографии.\" }");

	$image = new Imagick($fn);
	
	// GIF почему-то не обрабатываются... :(
	if ($image->getImageFormat() == "GIF") {
		print("noGIF");
		file_put_contents($ft, base64_decode("data:image/gif;base64,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"));
		die();	
	}

	$image->adaptiveResizeImage(250, 250);

	Header("Content-Type: image/{$image->getImageFormat()}");

	$data = $image->getImageBlob();
	file_put_contents($ft, $data);

	$image->clear();
	$image->destroy();
}

/**
 * @brief	Добавляет в БД информацию о фотографии на сервере. 
*/
function photoAdd($fileName) {
	if (!$fileName)
		die("{ \"result\": \"error\", \"error\": \"Не указан путь к фотографии.\" }");

	// Подключаемся к БД MySQL исходя из настроек.
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DEFAULTDB);

	if ($mysqli->connect_error)
		die("{ \"result\": \"error\", \"error\": \"Ошибка при подключении к базе данных! (#".$mysqli->connect_errno.": ".$mysqli->connect_error.")");

	// Выполняем запрос к БД и вставляем новую строку(-и).
	if (is_array($fileName)) {
		foreach ($fileName as $id) {
			$uid = rand();
			
			// Если в настройках указан не анонимный е-маил, используем его (учитываем, что он не пустая строка).
			if (preg_match("/^(null|nil|undefined|0|-1|no|none|false)$/", G_IMPEMAIL))
				$emailAddr = "anonymous@nomail.gov";
			else
				if (preg_match("/^[\w\.\d-_]+@[\w\.\d-_]+\.(?:\w{2,4}|yandex)$/i", G_IMPEMAIL))
					$emailAddr = G_IMPEMAIL;
				else
					$emailAddr = "anonymous@nomail.gov";
			
			$phoneNumber = "+1(333)666-77-77";
			
			$imageName = glob(FS_UPLOADDIR . $id . "_*.???");
			$imageName = substr($imageName[0], strrpos($imageName[0], "/".$id) + 1);
			
			// Переименовываем файл.
			rename("uploads/" . $imageName, "uploads/" . str_replace($id, $uid, $imageName));
			$imageName = str_replace($id, $uid, $imageName);
			
			$imageNameLoc = FS_UPLOADDIR . $imageName;
			$imageNameLocThumb = FS_UPLOADDIR . "thumbs/" . $imageName;
			
			$imageNameGlob = FS_SITEDIR . "uploads/" . $imageName;
			$imageNameGlobThumb = FS_SITEDIR . "uploads/thumbs/" . $imageName;
			
			$delpassw = rand(0, 999999);
			
			// Заносим данные в таблицу.
			$query = sprintf("INSERT INTO ".DB_DEFAULTTABL." (`uid`, `date`, `email`, `phone`, `imagelocal`, `imagelocal_thumb`, `imageglobal`, `imageglobal_thumb`, `delpassw`) VALUES ('%s', NOW(), '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
						$uid, 
						$emailAddr, 
						$phoneNumber, 
						$mysqli->real_escape_string($imageNameLoc),
						$mysqli->real_escape_string($imageNameLocThumb),
						$mysqli->real_escape_string($imageNameGlob),
						$mysqli->real_escape_string($imageNameGlobThumb),
						$mysqli->real_escape_string($delpassw)
						);
			$result = $mysqli->query($query);

			if (!$result)
				die("{ \"result\": \"error\", \"error\": \"Ошибка при выполнении запроса к базе данных!\" }");
		}

		print($result);
	}else{
		$uid = rand();

		// Если в настройках указан не анонимный е-маил, используем его (учитываем, что он не пустая строка).
		if (preg_match("/^(null|nil|undefined|0|-1|no|none|false)$/", G_IMPEMAIL))
			$emailAddr = "anonymous@nomail.gov";
		else
			if (preg_match("/^[\w\.\d-_]+@[\w\.\d-_]+\.(?:\w{2,4}|yandex)$/i", G_IMPEMAIL))
				$emailAddr = G_IMPEMAIL;
			else
				$emailAddr = "anonymous@nomail.gov";

		$phoneNumber = "+1(333)666-77-77";

		$imageName = glob(FS_UPLOADDIR . $fileName . "_*.???");
		$imageName = substr($imageName[0], strrpos($imageName[0], "/".$fileName) + 1);

		// Переименовываем файл.
		rename("uploads/" . $imageName, "uploads/" . str_replace($fileName, $uid, $imageName));
		$imageName = str_replace($fileName, $uid, $imageName);

		$imageNameLoc = FS_UPLOADDIR . $imageName;
		$imageNameLocThumb = FS_UPLOADDIR . "thumbs/" . $imageName;

		$imageNameGlob = FS_SITEDIR . "uploads/" . $imageName;
		$imageNameGlobThumb = FS_SITEDIR . "uploads/thumbs/" . $imageName;

		$delpassw = rand(0, 999999);

		// Заносим данные в таблицу.
		$query = sprintf("INSERT INTO ".DB_DEFAULTTABL." (`uid`, `date`, `email`, `phone`, `imagelocal`, `imagelocal_thumb`, `imageglobal`, `imageglobal_thumb`, `delpassw`) VALUES ('%s', NOW(), '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
						$uid, 
						$emailAddr, 
						$phoneNumber, 
						$mysqli->real_escape_string($imageNameLoc),
						$mysqli->real_escape_string($imageNameLocThumb),
						$mysqli->real_escape_string($imageNameGlob),
						$mysqli->real_escape_string($imageNameGlobThumb),
						$mysqli->real_escape_string($delpassw)
						);
		$result = $mysqli->query($query);

		if (!$result)
			die("{ \"result\": \"error\", \"error\": \"Ошибка при выполнении запроса к базе данных!\" }");
		
		print($result);
	}
	
	// Закрываем соединение с бд.
	$mysqli->close();
	
}


if (isset($_POST["act"]))
	// Получить "лайк" для фотографии.
	if ($_POST["act"] == "likeget") {
		if (isset($_POST["id"])) {
			require_once("config.php");
			photoGetLike($_POST["id"]);
		}
	}
	// "Лайк" для фотографии.
	if ($_POST["act"] == "likeadd") {
		if (isset($_POST["id"])) {
			require_once("config.php");
			photoAddLike($_POST["id"]);
		}
	}
	// Информация о фотографии.
	if ($_POST["act"] == "info") {
		if (isset($_POST["id"]) && isset($_POST["field"])) {
			require_once("config.php");
			getPhotoInfo($_POST["id"], $_POST["field"]);
		}
	}
	// Удаляем фотографию.
	if ($_POST["act"] == "delete") {
		if (isset($_POST["id"]) && isset($_POST["pw"])) {
			require_once("config.php");
			if (is_array($_POST["id"]) && is_array($_POST["pw"])) {
				if ($_COOKIE["adminHash"] == md5(ADM_PASSWORD)) {
					$mysqli = open_mysqli();
					photoDelete($mysqli, $_POST["id"]);
				}
			}else{
				getPhotoPasswd($_POST["id"], $_POST["pw"]);
			}
		}
	}
	// Сгенерируем thumbnail.
	if ($_POST["act"] == "makethumb") {
		if (isset($_POST["name"]) && isset($_POST["thumbname"])) {
			require_once("config.php");
			if ($_COOKIE["adminHash"] == md5(ADM_PASSWORD))
				photoMakeThumb($_POST["name"], $_POST["thumbname"]);
		}
	}
	// Удаляем фотографию(-и) которых нет в БД.
	if ($_POST["act"] == "delnodb") {
		if (isset($_POST["name"])) {
			require_once("config.php");
			if ($_COOKIE["adminHash"] == md5(ADM_PASSWORD)) {
				// Удаляем фотографии.
				if (is_array($_POST["name"])) {
					foreach ($_POST["name"] as $fn) {
						$fileName = glob(FS_UPLOADDIR . $fn . "_*.???");
						$fileNameThumb = glob(FS_UPLOADDIR . "thumbs/" . $fn . "_*.???");
						unlink($fileName[0]);
						(file_exists($fileNameThumb[0]) ? unlink($fileNameThumb[0]) : "");
					}
					print("done");
				// Удаляем одну фотографию.
				}else{
					$fileName = glob(FS_UPLOADDIR . $_POST["name"] . "_*.???");
					$fileNameThumb = glob(FS_UPLOADDIR . "thumbs/" . $_POST["name"] . "_*.???");
					unlink($fileName[0]);
					(file_exists($fileNameThumb[0]) ? unlink($fileNameThumb[0]) : "");
					
					print("done");
				}
			}
		}
	}
	// Добавляем фотографию(-и) которых нет в БД. Все параметры будут такими, как если бы эти фотографии загружал аноним (не предусмотрено системой, кстати).
	if ($_POST["act"] == "addnodb") {
		if (isset($_POST["name"])) {
			require_once("config.php");
			if ($_COOKIE["adminHash"] == md5(ADM_PASSWORD))
				// Добавляем фотографии.
				photoAdd($_POST["name"]);
		}
	}

?>