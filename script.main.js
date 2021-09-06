// Отладка. Вывод сообщения с указанием времени.
function debug(str) {
	var d = new Date();
	var time = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
	console.log("[" + time + "]: " + str);
}

// Чтение url как файла.
function readURL(input) {

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) { $("#form-upload-image-preview").attr('src', e.target.result); }

        reader.readAsDataURL(input.files[0]);
    }
}

// Функция получения нужного параметра из строки запроса URL.
function getUrlVars() {
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

	for(var i = 0; i < hashes.length; i++) {
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = hash[1];
	}
	return vars;
}

// Устанавливает нужный заголовок документа в зависимости от действия.
function setTitleForAction(actionName) {
	if (actionName == "add") {
		document.title = "Добавление фотографии";
		return;
	}
	if (actionName == "view") {
		document.title = "Просмотр загруженных фотографий";
		return;
	}
	if (actionName == "admin") {
		document.title = "Администратирование";
		return;
	}
	// Параметр либо пуст либо не пуст, но точно, не то, что выше.
	if (actionName == null || actionName != null) {
		document.title = "undefined";
		return;
	}
	return;
}

// Валидация поля с вводом почтового адреса.
function validateEmailField() {
	var email = $("#form-upload-email").val();
	
	//var rege = /^[\w\.\d-_]+@[\w\.\d-_]+\.\w{2,4}$/i;
	var rege = /^[\w\.\d-_]+@[\w\.\d-_]+\.(?:\w{2,4}|yandex)$/i;	// .yandex support included.
	
	if (rege.test(email)) {
		debug("validateEmailField returned true!");
		return true;
	}else{
		debug("validateEmailField returned false (#0) !");
		$("#form-upload-email").focus();
		return false;
	}
}

// Валидация поля с вводом телефона.
function validatePhoneField() {
	var phone = $("#form-upload-phone").val();
	
	var rege = /^\+\d{1}\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
	
	if (rege.test(phone)) {
		debug("validatePhoneField returned true!");
		return true;
	}else{
		debug("validatePhoneField returned false (#0) !");
		$("#form-upload-phone").focus();
		return false;
	}
}

// Валидация поля с вводом пароля для удаления.
function validatePasswField() {
	var passw = $("#form-upload-passw").val();
	
	if (passw.length < 4) {
		debug("validatePasswField returned false (#0) !");
		return false;
	}else{
		debug("validatePasswField returned true!");
		return true;
	}
}

// Валидация поля с изображением.
function validateImageField() {
	var img = $("#form-upload-image").val();
	
	var ext = img.substring(img.length-3, img.length).toLowerCase();
	
	if ( ext == "jpg" || ext == "png" || ext == "gif" || ext == "jpeg" ) {
		debug("validateImageField returned true!");
		return true;
	}else{
		debug("validateImageField returned false (#0) !");
		return false;
	}
}

// Валидация всех полей формы.
function validateForm() {
	var errors = 0;
	
	if (validateEmailField() == false) {
		$("#form-upload-email-field").css("background-color", "#ff9898");
		errors++;
	}else{
		$("#form-upload-email-field").css("background-color", "inherit");
	}
	
	if (validatePhoneField() == false) {
		$("#form-upload-phone-field").css("background-color", "#FF9898");
		errors++;
	}else{
		$("#form-upload-phone-field").css("background-color", "inherit");
	}
	
	if (validatePasswField() == false) {
		$("#form-upload-passw").css("background-color", "#FF9898");
		errors++;
	}else{
		$("#form-upload-passw").css("background-color", "inherit");
	}
	
	if (validateImageField() == false) {
		$("#form-upload-image").css("background-color", "#FF9898");
		errors++;
	}else{
		$("#form-upload-image").css("background-color", "inherit");
	}
	
	if (errors != 0) {
		return false;
	}else{
		return true;
	}
}

// Удаление картинки.
function delPicture() {
	var passw = $("#viewer-img-passw").val();
	var picid = getUrlVars()["photoid"];
	
	if (!passw || passw.length < 4) {
		$("#viewer-img-form-del").css("background-color", "#FF9898");
		$("#viewer-img-passw").focus();
	}else{
		$("#viewer-img-form-del button").attr("disabled", "disabled");
		$("#viewer-img-form-del").css("background-color", "#494949");
		
		$.post("photoinfo.php", { act: "delete", id: picid, pw: passw })
			.done(function(data) {
				$("#error-desc").remove();
				var result = $.parseJSON(data);
				
				switch (result.result) {
					case "true":
						// Всё хорошо. Картинка удалена, обновим страницу.
						location.reload();
						break;
					case "false":
						// Что-то не так с паролем. Уточняем.
						$("#viewer-img-form-del button").removeAttr("disabled");
						$("#viewer-img-form-del").css("background-color", "#FF9898");
						$("#viewer-img-form-del").append("<span id=\"error-desc\"><br />Пароль введён неверно. Попыток осталось " + (result.attmt - result.attm) + "</span>");
						break;
					case "error":
						// Что-то не так. Уточняем.
						$("#viewer-img-form-del button").removeAttr("disabled");
						$("#viewer-img-form-del").css("background-color", "#FF9898");
						$("#viewer-img-form-del").append("<span id=\"error-desc\"><br />" + result.error + "</span>");
						break;
				}
			});
	}
}

// Форма жалобы на картинку.
function reportPicture(action) {
	if (action == "hide") {
		// Убираем форму без отправки.
		$("#viewer-img-form-report button").removeAttr("disabled");
		$("#photospace span").remove();
		$("#photospace select").remove();
		$("#photospace button").remove();
		$("#photospace br").remove();
		$("#photospace img").fadeIn('fast');		
	}
	if (action == "show") {
		// Просто покажем форму.
		$("#viewer-img-form-report button").attr("disabled", true);
		$("#photospace img").fadeOut('', function () {
			$("#photospace").append("<span><b>Выберите причину вашего недовольства:</b><br />");
			$("#photospace").append("<select id=\"photoreport-reason\">");
			$("#photospace select").load("reasons.txt");
			$("#photospace").append("</select><br />");
			$("#photospace").append("<button onclick=\"reportPicture('send')\">Пожаловаться</button></span>");
			$("#photospace").append("<button onclick=\"reportPicture('hide')\">Отмена</button></span>");
		});
	}
	if (action == "send") {
		var reasonId = $("#photoreport-reason").val();
		var photoId = getUrlVars()['photoid'];
		// Удаляем всё, что создали ранее.
		$("#viewer-img-form-report button").removeAttr("disabled");
		$("#photospace span").remove();
		$("#photospace select").remove();
		$("#photospace button").remove();
		$("#photospace br").remove();
		$("#photospace img").fadeIn('fast');
		// Отправляем запрос на жалобу на фотографию.
		$.ajax({
			type: 'POST',
			url: 'reports.php',
			data: { act: 'add', photoid: photoId, reasonid: reasonId },
		}).done(function(data) {
			debug(data);
		});
	}
}

// Количество "лайков" для картинки.
function getPictureLikes(photoid) {
	$.ajax({
		type: 'POST',
		url: 'photoinfo.php',
		data: { act: 'likeget', id: photoid },
	}).done(function(data) {
		var result = $.parseJSON(data);

		if (result.result == 'success') {
			debug(result.result + " (" + parseInt(result.value) + ")");
			return parseInt(result.value);
		}else{
			debug(result);
		}
	});
}

// "Лайк" картинки.
function likePicture() {
	$.ajax({
		type: 'POST',
		url: 'photoinfo.php',
		data: { act: 'likeadd', id: getUrlVars()['photoid'] },
	}).done(function(data) {
		location.reload();
	});
}

// Главная ф-ция.
$(document).ready(function() {
	// Количество "лайков" для картинки.
	function getPictureLikes(photoid) {
		return 1;
	}

	// Заменим заголовок в зависимости от нужного действия.
	setTitleForAction(getUrlVars()['act']);
	
	// Превью для изображения.
	$("#form-upload-image").change(function() { readURL(this); });
	
	// Показ пароля в поле для ввода пароля.
	$("#form-upload-passw").hover(function() { $(this).attr("type", "text") }, function() { $(this).attr("type", "password") });

	if (getUrlVars()["act"] == "view") {
		if (getUrlVars()["page"] != null) {
			// Страница указана явно.
			$(".galimg > img").click(function() { location.replace("index.php?act=view&page=" + getUrlVars()['page'] + "&photoid=" + $(this).attr('image-uid') + "&order=" + getUrlVars()['order']); });
		}else{
			// Предположим, что страница первая.
			$(".galimg > img").click(function() { location.replace("index.php?act=view&page=1&photoid=" + $(this).attr('image-uid')); });
		}
	}

	if ($("#btnlikes") != null) {
		$.ajax({
			type: 'POST',
			url: 'photoinfo.php',
			data: { act: 'likeget', id: getUrlVars()['photoid'] },
		}).done(function(data) {
			var result = data != "" ? $.parseJSON(data) : {};

			if (result.result == 'success') {
				debug(result.result + " (" + parseInt(result.value) + ")");
				if (result.value == 0)
					$("#btnlikes").html("Мне нравится");
				else
					$("#btnlikes").html("Мне нравится (" + result.value + ")");
			}else{
				debug(result);
			}
		});
	}
});