import {closeModal, setFormErrors, showModal} from "../modal.js";
import { setNotificationInLocalStorage, showNotification } from "../notification.js";
import { renderPagination } from "../pagination.js";

/** Edit profile **/
// Открытие модального окна
$('.edit-profile-button').on('click', function () {
	showModal($('#editProfileModal'));
});

// Предотвратить отправку формы и закрыть модальное окно, осуществить регистрацию
$('#editProfileModal').on('submit', function(e) {
	e.preventDefault(); // Предотвращаем стандартную отправку формы

	// Собираем данные формы
	let formData = {
		name: $('#name').val(),
		email: $('#email').val(),
		'delivery_address': $('#delivery_address').val(),
		phone: $('#phone').val(),
	};

	// Отправляем данные на сервер через AJAX
	$.ajax({
		type: 'POST', // Метод отправки данных
		url: '/account/profile/update', // Путь к серверному скрипту обработки данных
		data: formData, // Данные, которые мы хотим отправить
		dataType: 'json', // Тип данных, который мы ожидаем получить в ответ
		success: function(response) {
			setNotificationInLocalStorage(response.message, 'success');
		},
		error: function(xhr, status, error) {
			let responseJson = xhr.responseJSON;
			if (responseJson !== undefined && responseJson.status === 422) {
				if (responseJson.errors) {
					setFormErrors(responseJson.errors);
				}
			}
			else {
				showNotification('Произошла неизвестная ошибка при обновлении профиля!', 'error');
			}
		}
	});
});

/** Update password **/
// Открытие модального окна
$('.change-password-button').on('click', function () {
	showModal($('#changePasswordModal'));
});

// Предотвратить отправку формы и закрыть модальное окно, осуществить регистрацию
$('#changePasswordModal').on('submit', function(e) {
	e.preventDefault(); // Предотвращаем стандартную отправку формы

	// Собираем данные формы
	let formData = {
		password: $('#password').val(),
		new_password: $('#new_password').val(),
		new_password_confirm: $('#new_password_confirm').val(),
	};

	// Отправляем данные на сервер через AJAX
	$.ajax({
		type: 'POST', // Метод отправки данных
		url: '/account/profile/change-password', // Путь к серверному скрипту обработки данных
		data: formData, // Данные, которые мы хотим отправить
		dataType: 'json', // Тип данных, который мы ожидаем получить в ответ
		success: function(response) {
			setNotificationInLocalStorage(response.message, 'success');
		},
		error: function(xhr, status, error) {
			let responseJson = xhr.responseJSON;
			if (responseJson !== undefined && responseJson.status === 422) {
				if (responseJson.errors) {
					setFormErrors(responseJson.errors);
				}
			}
			else {
				closeModal();
				showNotification('Произошла неизвестная ошибка при изменении пароля!', 'error');
			}
		}
	});
});

/** Active history **/
let per_page = 5;
let page = 1;
let all_page_count;
let filters = {};

$(document).ready(function() {
	initActiveHistory();
});

// Установка фильтра по типу уведомления
$('.select-activity-type').on('change', function () {
	let selectedValue = $(this).val();

	if (selectedValue !== undefined && selectedValue !== '') {
		filters['type'] = selectedValue;
	} else {
		filters['type'] = undefined;
	}

	page = 1;

	initActiveHistory();
});

// Меняем кол-во на странице и обновляем
$('.select-per-page').on('change', function () {
	let selectedValue = $(this).val();
	if (selectedValue !== undefined && selectedValue !== '') {
		per_page = selectedValue;
	} else {
		per_page = 5;
	}

	initActiveHistory();
});

function initActiveHistory()
{
	$.ajax({
		type: 'POST', // Метод отправки данных
		url: '/account/profile/activity-history', // Путь к серверному скрипту обработки данных
		data: {'filters': filters, 'pagination': {'per_page': per_page, 'page': page}}, // Данные, которые мы хотим отправить
		dataType: 'json', // Тип данных, который мы ожидаем получить в ответ
		success: function(response) {
			let activeHistory = response.data;
			let all_items_count = activeHistory['totalCount'];
			all_page_count = Math.ceil(all_items_count / per_page);
			renderActiveHistory(activeHistory['items']);
			renderPagination(page, all_page_count, all_items_count);
		},
		error: function(xhr, status, error) {
			let responseJson = xhr.responseJSON;
			if (responseJson !== undefined && responseJson.status === 422) {
				showNotification(responseJson.errors, 'error');
			} else {
				showNotification('Произошла неизвестная ошибка!', 'error');
			}
		}
	});
}

function renderActiveHistory(activeHistoryData) {
	let activityHistoryContainer = $('.activity-history__items');
	activityHistoryContainer.empty(); // Очищаем контейнер перед рендерингом

	// Проходимся по каждому элементу активной истории и создаем карточку для каждого
	activeHistoryData.forEach(function(item) {
		let card = $('<div>').addClass('activity-history-card');

		let header = $('<div>').addClass('activity-history-card__header');
		let headerElement = $('<strong>').text(item.text);
		if (item.warning) {
			headerElement.prepend($('<i>').addClass('fa fa-exclamation-triangle').css('margin-right', '10px'));
		}
		header.append(headerElement);

		let details = $('<div>').addClass('activity-history-card__details');
		details.append($('<p>').html(`<strong>IP:</strong> &nbsp;${item.ip}`));
		details.append($('<p>').html(`<strong>User Agent:</strong> &nbsp;${item.user_agent}`));
		details.append($('<p>').html(`<strong>Страна:</strong> &nbsp;${item.country || 'Неизвестно'}`));
		details.append($('<p>').html(`<strong>Регион:</strong> &nbsp;${item.region || 'Неизвестно'}`));
		details.append($('<p>').html(`<strong>Город:</strong> &nbsp;${item.city || 'Неизвестно'}`));
		details.append($('<p>').html(`<strong>Дата и время:</strong> &nbsp;${item.created_at}`));
		card.append(header);
		card.append(details);
		activityHistoryContainer.append(card);
	});
}

// Обработка клика по кнопкам пагинации
$(document).on('click', '.pagination .page', function() {
	page = $(this).data('page');
	initActiveHistory();
});