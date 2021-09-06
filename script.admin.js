// JQuery is already in.
$(document).ready(function() {
inSelectMode = false;
errorsTotal = $("#errors-total").val();
selectedImages = [];
$("div[id^='photo_']").hover(function() {
	// Mouse is over the element.
	$(this).children(".photoinfodesc").fadeIn("fast");
},
function() {
	// Mouse is out the element focus.
	$(this).children(".photoinfodesc").fadeOut("fast");
});

$(".photoinfopw").hover(function() {
	$(this).html($(this).attr("data-pass"));
},
function() {
	$(this).html("скрыто");
});

updateSelectedImages();
});

function debug(str) {
	var d = new Date();
	var time = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
	console.log("[" + time + "]: " + str);
}

function updateSelectedImages() {
	selectedImages = [];
	debug("Flushed selectedImages array.");
	$.each($(".difcheck > input[type='checkbox']"), function() {
		// Каждый чекбокс обойдём.
		if ($(this).prop("checked")) {
			selectedImages[selectedImages.length] = $(this).val();
			debug("Added --  " + $(this).val());
		}
	});
	if (selectedImages.length == 0) {
		$("#btn-empty").attr("disabled", "true");
		$("#btn-addd").attr("disabled", "true");
	}else{
		$("#btn-empty").removeAttr("disabled");
		$("#btn-addd").removeAttr("disabled");
	}
	$("#btn-empty").text("Удалить выбранные изображения (" + selectedImages.length + ")");
	$("#btn-addd").text("Добавить выбранные изображения (" + selectedImages.length + ")");
	debug("Finished building selectedImages array.");
}

function toggleChecks() {
	// Переключить выбор со всех пунктов на ни сколько.
	if ($("#chk-selallimg").prop("checked")) {
		// Выделить все строки.
		$.each($(".difcheck > input[type='checkbox']"), function() {
			if ($(this).prop("checked") == false)
				$(this).prop("checked", true);
		});
	}else{
		// Отменить выделение всех строк.
		$.each($(".difcheck > input[type='checkbox']"), function() {
			if ($(this).prop("checked"))
				$(this).removeAttr("checked");
		});
	}
	updateSelectedImages();
}

function addEmpty() {
	// Добавляет все указанные в массиве изображения.
	if (selectedImages.length == 0)
		return;
		
	$.ajax({
		type: "POST",
		url: "photoinfo.php",
		data: { act: "addnodb", name: selectedImages }
	})
	.done(function(msg) {
		if (msg == "1") {
			debug(msg);
			location.reload(true);
		}else{
			debug(msg);
		}
	});
}

function remEmpty() {
	// Удаляет все указанные в массиве изображения.
	if (selectedImages.length == 0)
		return;
	
	$.ajax({
		type: "POST",
		url: "photoinfo.php",
		data: { act: "delnodb", name: selectedImages }
	})
	.done(function(msg) {
		if (msg == "done") {
			debug(msg);
			location.reload(true);
		}else{
			debug(msg);
		}	
	});
}

function ignoreThis(id) {
	if (id != null) {
		$("div[data-errid='" + id + "']").slideUp('fast');
		errorsTotal -= 1;
		if (errorsTotal == 0) {
			$("#btn-ignall").attr("disabled", "true");
			$("#btn-expall").removeAttr("disabled");
		}
	}
}

function ignoreAll() {
	$.each($("div#errors-list > div"), function() {
		$(this).slideUp('fast');
	});
	$("#btn-ignall").attr("disabled", "true");
	$("#btn-expall").removeAttr("disabled");
}

function exposeAll() {
	errorsTotal = $("#errors-total").val();
	$.each($("div#errors-list > div"), function () {
		$(this).slideDown('fast');
	});
	$("#btn-ignall").removeAttr("disabled");
	$("#btn-expall").attr("disabled", "true");
}

function genThumb(id) {
	if (id != null) {
		var filename = $("div[data-errid='" + id + "']").children("#filename").val();
		var filename_thumb = $("div[data-errid='" + id + "']").children("#filename_thumb").val();
		
		$.ajax({
			type: "POST",
			url: "photoinfo.php",
			data: { act: "makethumb", name: filename, thumbname: filename_thumb }
		})
		.done(function(msg) {
			debug(msg);
			$("div[data-errid='" + id + "']").slideUp('fast', function() {
				$(this).remove();
			});
			errorsTotal--;
			if (errorsTotal == 0) {
				$("#btn-ignall").remove();
				$("#btn-expall").remove();
			}else{
				$("#btn-ignall").text("ИГНОРИРОВАТЬ ВСЕ (" + errorsTotal + ") ОШИБКИ");
				$("#btn-expall").text("ПОКАЗАТЬ ВСЕ (" + errorsTotal + ") ОШИБКИ");
			}	
		});
	}
	
	return;
}

function delPhotos(id) {

	if (id != null) {
		var photoid = $("div[data-errid='" + id + "']").children("#photoid").val();
		
		$.ajax({
			type: "POST",
			url: "photoinfo.php",
			data: { act: "delete", id: photoid, pw: "-1" }
		})
		.done(function(msg) {
			alert(msg);
		});
	
		return;
	}
		
	// Массив с ID фотографий для удаления.
	photosForDeletion = [];
	// Массив с паролями для удаления фотографий.
	photosPasswords = [];
	
	$.each($("div[id^='photo_']"), function() {
		if ($(this).attr("data-selected") == "true") {
			// Пройдёмся по каждому div'у с интересующими нас параметрами и получим ID фотографии.
			photosForDeletion[photosForDeletion.length] = ($(this).attr('id').split('_'))[1];
			photosPasswords[photosPasswords.length] = $(this).attr("data-passw");
		}
	});
	
	// Массив получен. Передадим его в запрос.
	$.ajax({
		type: "POST",
		url: "photoinfo.php",
		data: { act: "delete", id: photosForDeletion, pw: photosPasswords }
	})
	.done(function(msg) {
		var data = $.parseJSON(msg);

		if (data.result == 'succ')
			location.reload();
		else
			alert(data.error);
	});
}

function togglePhotoSel() {
	if (inSelectMode) {
		inSelectMode = false;
		$("#btnPhotoSel").css("background-color", "inherit");
	}else{
		inSelectMode = true;
		$("#btnPhotoSel").css("background-color", "green");
	}

	debug(inSelectMode);

	if (inSelectMode) {
		// Был включен режим выделения.
		
		var photosSelected = 0;
		
		var spanActionsSelected = "<span id=\"spanActionsSelected\">&nbsp;|&nbsp;С выбранными <span id=\"photosSelectedSp\"></span> <button id=\"btnPhotosDel\" onclick=\"delPhotos()\" disabled>Удалить</button></span>";
		
		$("#btnPhotoSel").append(spanActionsSelected);
		
		// Включаем возможность выделять изображения.
		$("div[id^='photo_']").bind("click", function() {
			if ($(this).attr("data-selected") == "true") {
				// Убираем выделение с изображения.
				$(this).removeAttr("style");
				$(this).attr("data-selected", "false");
				debug($(this).attr('id') + " deselected");
				photosSelected--;
				if (photosSelected > 0) {
					$("#photosSelectedSp").text("(" + photosSelected + ")");
					if ($("#btnPhotosDel").attr("disabled"))
						$("#btnPhotosDel").removeAttr("disabled");
				}else{
					$("#photosSelectedSp").text("");
					if ($("#btnPhotosDel").attr("disabled"))
						$("#btnPhotosDel").removeAttr("disabled");
					else
						$("#btnPhotosDel").attr("disabled", "");
				}
				debug("Photos selected: " + photosSelected);
			}else{
				// Выделяем это изображение.
				$(this).css("border-color", "green");
				$(this).attr("data-selected", "true");
				debug($(this).attr('id') + " selected");
				photosSelected++;
				debug("Photos selected: " + photosSelected);
				if (photosSelected > 0) {
					$("#photosSelectedSp").text("(" + photosSelected + ")");
					if ($("#btnPhotosDel").attr("disabled"))
						$("#btnPhotosDel").removeAttr("disabled");
				}
			}
		});
	}else{
		// Был выключен режим выделения.
		
		$("#spanActionsSelected").fadeOut("fast", function() { $(this).remove() });
		
		// Снимаем выделения со всех изображений.
		$.each($("div[id^='photo_']"), function() {
			$(this).unbind("click");
			if ($(this).attr("data-selected") == "true") {
				$(this).removeAttr("style");
				$(this).attr("data-selected", "false");
				debug($(this).attr('id') + " deselected");
			}
		});
	}
}