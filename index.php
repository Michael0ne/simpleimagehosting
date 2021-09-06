<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Особый случай - вход администратора.
if (isset($_POST['adminHash'])) {
	require_once("config.php");
	
	// Подключим шаблон заголовка.
	include("testp-head.html");
	
	include("testp-admin.php");
	
	// Подкючим шаблон footer.
	include("testp-foot.html");
	
	die();
}

if ($_GET['act'] == null)
	Header("Location: index.php?act=add&from=noact");

require_once("config.php");

// Переменные.
$act = $_GET['act'];
$errtype = isset($_GET['errtype']) ? $_GET['errtype'] : "";

// Подключим шаблон заголовка.
include("testp-head.html");

if ($act == "add")
	if ($errtype == "maxsizelimit")
		// Превышен лимит размера файла с изображенем.
		include("testp-imgmaxsizelim.html");
	elseif ($errtype == "uploadfail")
		include("testp-uplfail.html");
	else
		include("testp-form.html");
elseif ($act == "view")
	include("testp-viewer.php");
elseif ($act == "admin" || $_POST['adminHash'])
	include("testp-admin.php");
else
	die(include("testp-foot.html"));

// Подкючим шаблон footer.
include("testp-foot.html");

?>