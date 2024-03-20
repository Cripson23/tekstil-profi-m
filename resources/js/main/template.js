import {closeModal, setFormErrors, showModal} from "../modal.js";
import { showNotification, setNotificationInLocalStorage } from "../notification.js";

$(document).ready(function() {
    initModalForms();
});

// инициализация модальных форм
function initModalForms()
{
    /** Register **/
    // Открытие модального окна
    $('.register-button').on('click', function () {
        showModal($('#registrationModal'));
    });

    // Предотвратить отправку формы и закрыть модальное окно, осуществить регистрацию
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault(); // Предотвращаем стандартную отправку формы

        // Собираем данные формы
        let formData = {
            username: $('#username').val(),
            name: $('#name').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            'delivery_address': $('#delivery_address').val(),
            password: $('#password').val(),
            'password_confirm': $('#password_confirm').val()
        };
        // Отправляем данные на сервер через AJAX
        $.ajax({
            type: 'POST', // Метод отправки данных
            url: '/auth/register', // Путь к серверному скрипту обработки данных
            data: formData, // Данные, которые мы хотим отправить
            dataType: 'json', // Тип данных, который мы ожидаем получить в ответ
            success: function(response) {
                showNotification(response.message);
                closeModal();
            },
            error: function(xhr, status, error) {
                let responseJson = xhr.responseJSON;
                if (responseJson !== undefined && responseJson.status === 422) {
                    if (responseJson.errors) {
                        setFormErrors(responseJson.errors);
                    }
                }
                else {
                    showNotification('Произошла неизвестная ошибка во время регистрации!', 'error');
                }
            }
        });
    });

    /** Login **/
    $('.login-button').on('click', function () {
        showModal($('#loginModal'));
    });

    // Предотвратить отправку формы и закрыть модальное окно при отправке
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        // Собираем данные формы
        let formData = {
            username: $('#login_username').val(),
            password: $('#login_password').val(),
        };
        // Отправляем данные на сервер через AJAX
        $.ajax({
            type: 'POST', // Метод отправки данных
            url: '/auth/login', // Путь к серверному скрипту обработки данных
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
                    showNotification('Произошла неизвестная ошибка во время авторизации!', 'error');
                }
            }
        });
    });
}