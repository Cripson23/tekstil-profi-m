export { showNotification }

$(document).ready(function() {
    // Проверяем, есть ли сообщение для показа
    let notification = localStorage.getItem('notification');
    if (notification) {
        notification = JSON.parse(notification);
        showNotification(notification['message'], notification['type']);
        // Удаляем сообщение из localStorage, чтобы оно не показывалось снова
        localStorage.removeItem('notification');
    }
});

export function setNotificationInLocalStorage(message, type)
{
    let notification = {'message': message, 'type': type};
    localStorage.setItem('notification', JSON.stringify(notification));
    location.reload();
}

function showNotification(message, type = 'success') {
    // Определяем цвет уведомления в зависимости от типа
    const backgroundColor = type === 'error' ? 'tomato' : 'forestgreen';

    // Создаем элемент уведомления с заданным сообщением и цветом фона
    const notification = $(`<div class="notification">${message}</div>`);
    notification.css({'background-color': backgroundColor});

    $('body').append(notification);
    $('.notification').fadeIn().delay(3000).fadeOut('slow', function() { $(this).remove(); });
}