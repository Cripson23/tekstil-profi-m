export { setFormErrors, closeModal };

$(document).ready(function() {
    // Закрытие модального окна при клике на кнопку закрыть (X)
    $('.close, .modal-backdrop').on('click', function () {
        let modal = $('.modal:visible');
        closeModal(modal);
    });
});

function closeModal(modal)
{
    modal.fadeOut();
    $('.modal-backdrop').fadeOut();

    let isEditModal = modal.hasClass('modal-edit');
    clearForm(modal, isEditModal);
}

export function showModal(modal)
{
    modal.fadeIn();
    $('.modal-backdrop').fadeIn();
    checkScrollbarAndSetPadding(modal);
}

function clearForm(modal, isEditModal = false)
{
    clearFormErrors();

    if (!isEditModal) {
        clearFormFields(modal);
    }
}

function setFormErrors(errors)
{
    clearFormErrors();
    for (let field in errors) {
        let errorField = $(`#error-${field}`);
        errorField.text(errors[field]);
        errorField.css({'margin-bottom': '10px', 'height': '20px'});
    }
}

function clearFormFields(modal) {
    modal.children('input, textarea, select').val('');
}

function clearFormErrors()
{
    // Очищаем текст ошибок
    $('.error').each(function() {
        $(this).text('');
    });

    // Возвращаем стили к исходным значениям (или удаляем индивидуальные стили)
    $('.error').css({'margin-bottom': '', 'height': ''});
}

function checkScrollbarAndSetPadding(modal) {
    // Проверяем, есть ли у модального окна вертикальная прокрутка
    let modalContent = modal.find('.modal-content');

    if(modalContent.get(0).scrollHeight > modalContent.innerHeight()) {
        // Если есть прокрутка, добавляем отступ справа
        modalContent.css('padding-right', '15px'); // Или другое значение отступа, если необходимо
    } else {
        // Если прокрутки нет, убираем отступ
        modalContent.css('padding-right', '0');
    }
}