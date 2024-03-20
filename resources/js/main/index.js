import {setNotificationInLocalStorage, showNotification} from "../notification.js";
import {closeModal, setFormErrors, showModal} from "../modal.js";

let authStatus;

$(document).ready(function() {
    authStatus = $('#authStatus').length > 0 ? 1 : 0;
    initSlider();
    initCategories();
});

/** Slider **/
function initSlider()
{
    let indexProductId = $('#index-product-id').data('id');
    if (indexProductId) {
        $.ajax({
            type: 'GET', // Метод отправки данных
            url: `/product/${indexProductId}/`, // Путь к серверному скрипту обработки данных
            dataType: 'json', // Тип данных, который мы ожидаем получить в ответ
            success: function (response) {
                setSlider(response.data);
                renderProduct(response.data);
            },
            error: function (xhr, status, error) {
                let responseJson = xhr.responseJSON;
                if (responseJson !== undefined && responseJson.status === 422) {
                    if (responseJson.error) {
                        showNotification(responseJson.error, 'error');
                    }
                } else {
                    showNotification('Произошла неизвестная ошибка при получении главного товара!', 'error');
                }
            }
        });
    }
}

function renderProduct(productData)
{
    let priceHtml;
    if (productData.old_price !== null) {
        priceHtml = `<span class="current-price-with-old">${productData.price} ₽</span>
                  <span class="old-price">${productData.old_price} ₽</span>`;
    } else {
        priceHtml = `<span class="current-price">${productData.price} ₽</span>`;
    }

    const productContent = `
        <h3 class="product-content__subtitle">${productData.name}</h3>
        <h2 class="product-content__title">${productData.brand_name}</h2>
        <p class="product-content__text">${productData.text}</p>
        <p class="product-content__price">
            ${priceHtml}
        </p>
        <button class="button-product add-to-cart" data-product-id="${productData.id}">В корзину</button>
        ${productData.description ? `<p class="product-content__desc">${productData.description}</p>` : ''}
    `;

    $('.product-content').append(productContent);
}

function setSlider(productData) {
    let images = productData.files;
    let currentIndex = 0;
    let $img = $('.product-img');
    let $productConTwo = $('.product-con-two'); // Контейнер для табов

    // Очищаем предыдущие табы и стрелки, если они были добавлены ранее
    $productConTwo.empty();
    $('.product-arrow').remove();

    // Создаем табы в зависимости от количества изображений
    if (images.length > 1) {
        images.forEach((image, index) => {
            let tabClass = index === 0 ? 'product-con__tabs product-con__tabs--one' : 'product-con__tabs';
            $productConTwo.append(`<div class="${tabClass}"></div>`);
        });
    }

    let $tabs = $('.product-con__tabs'); // Обновляем выборку табов после их добавления

    // Добавляем стрелки, если изображений больше одного
    if (images.length > 1) {
        $productConTwo.before('<div class="product-arrow product-arrow__left"></div>');
        $productConTwo.after('<div class="product-arrow product-arrow__right"></div>');
    }

    // Обновляем обработчики для стрелок
    $('.product-arrow__right').on('click', nextSlide);
    $('.product-arrow__left').on('click', prevSlide);

    // Обработчики для табов
    $tabs.on('click', function() {
        let index = $(this).index();
        updateSlider(index);
    });

    // Инициализация слайдера
    updateSlider(currentIndex);

    function updateSlider(index) {
        $img.attr('src', `../resources/img/products/${productData.id}/` + images[index]);
        $tabs.removeClass('product-con__tabs--one').eq(index).addClass('product-con__tabs--one');
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % images.length;
        updateSlider(currentIndex);
    }

    function prevSlide() {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateSlider(currentIndex);
    }
}

/** Categories **/
function initCategories()
{
    renderCategories('child');
    renderCategories('invent');
}

function getCategories(keyName)
{
    return new Promise((resolve, reject) => {
        $.ajax({
            type: 'POST', // Метод отправки данных
            url: '/product/category/type/list', // Путь к серверному скрипту обработки данных
            data: {'key_name': keyName}, // Данные, которые мы хотим отправить
            dataType: 'json', // Тип данных, который мы ожидаем получить в ответ
            success: function (response) {
                resolve(response.data);
            },
            error: function (xhr, status, error) {
                reject('Произошла ошибка при получении категорий');
                showNotification('Произошла неизвестная ошибка!', 'error');
            }
        });
    });
}

function renderCategories(keyName) {
    const container = document.getElementById(keyName);
    const countCategoriesPerLine = 3;

    if (!container) {
        console.error("Контейнер не найден");
        return;
    }

    getCategories(keyName).then(categoriesData => {
        let categories = categoriesData.items;
        if (categories !== undefined && categories.length > 0) {
            let titleElement = document.createElement('h2');
            titleElement.textContent = categoriesData['type_name'];
            titleElement.className = `category__title`;
            container.appendChild(titleElement);

            // Создаем обертку для всех блоков категорий
            const blocksWrapper = document.createElement('div');
            blocksWrapper.className = "categories-wrapper";
            container.appendChild(blocksWrapper);

            const remainder = categories.length % countCategoriesPerLine;

            categories.forEach((category, index) => {
                // Создаем блок для категории
                const categoryBlock = document.createElement('div');
                categoryBlock.className = `category-block`;
                categoryBlock.style.background = `url('resources/img/categories/${category.type_id}/${category.image}') no-repeat center / cover`;
                categoryBlock.innerHTML = `
                    <h3 class="category-block__title">${category.name}</h3>
                    <a href="product/category/${category.id}/" class="category-block__button">Подробнее</a>
                `;

                blocksWrapper.appendChild(categoryBlock);

                // Динамическое добавление блоков категорий в обертку
                if ((index + 1) % countCategoriesPerLine === 0 || index === categories.length - 1) {
                    // Каждые 3 категории или последняя категория создаем новую линию
                    const lineBreak = document.createElement('div');
                    lineBreak.className = "line-break";
                    blocksWrapper.appendChild(lineBreak);
                }
            });
        } else {
            console.error("Не удалось получить категории");
        }
    }).catch(error => {
        console.error(error);
    });
}

/** Curtains order **/
$('#curtains-order').on('click', function () {
    showModal($('#curtainsOrderModal'));
});

$('#curtainsOrderForm').on('submit', function (e) {
    e.preventDefault();

    // Собираем данные формы
    let formData = {
        curtains_name: $('#curtains-name').val(),
        curtains_email: $('#curtains-email').val(),
        curtains_phone: $('#curtains-phone').val(),
        curtains_wish: $('#curtains-wish').val(),
        authStatus: authStatus,
    };

    // Отправляем данные на сервер через AJAX
    $.ajax({
        type: 'POST',
        url: '/order/curtains/',
        data: formData,
        dataType: 'json',
        success: function(response) {
            closeModal($('#curtainsOrderModal'));
            showNotification(response.message, 'success');
        },
        error: function(xhr, status, error) {
            let responseJson = xhr.responseJSON;
            if (responseJson !== undefined) {
                if (responseJson.status === 422) {
                    if (responseJson.errors.authStatus) {
                        showNotification(responseJson.errors.authStatus[0], 'error');
                    } else if (responseJson.errors) {
                        setFormErrors(responseJson.errors);
                    }
                } else {
                    showNotification(responseJson.error, 'error');
                }
            }
            else {
                showNotification('Произошла неизвестная ошибка при отправке', 'error');
            }
        }
    });
});