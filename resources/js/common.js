/** Logout **/
import { setNotificationInLocalStorage } from "./notification.js";

$('.logout-button').on('click', function () {
	setNotificationInLocalStorage('Успешный выход из системы', 'success');
});

// обновление селектора (сброс выбранного значения и выбор нового)
export function updatePerPageSelector(pagination) {
	$('.select-per-page option').prop('selected', false); // Сброс всех selected
	$('.select-per-page option[value="' + pagination.per_page + '"]').prop('selected', true); // Установка selected для текущего значения
}