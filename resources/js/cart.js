import {closeModal, showModal} from "./modal.js";
import { setNotificationInLocalStorage, showNotification } from "./notification.js";

let authStatus;

$(document).ready(function() {
    authStatus = $('#authStatus').length > 0 ? 1 : 0;
    if (authStatus) {
        initCart();
    }
});

function initCart()
{
    $.ajax({
        url: `/cart/data/`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            renderCart(response.data)
        },
        error: function(xhr, status, error) {
            showNotification('Не удалось получить содержимое корзины', 'error');
        }
    });
}

function renderCart(cartData) {
    let $cartModalBody = $('.modal-body-cart');
    $cartModalBody.empty();

    // Проверяем, есть ли товары в корзине
    if (cartData.products.length > 0) {
        let cartTable = $(`
            <table class="cart-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Товар</th>
                        <th>Цена</th>
                        <th>Скидка</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                        <th>Сумма скидки</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>`);

        let cartTableBody = cartTable.find('tbody');
        cartData.products.forEach(function (product) {
            let row = $(`
                <tr>
                    <td><img src="/resources/img/products/${product.id}/${product.images[0]}" style="width: 200px; height: auto;"></td>
                    <td>${product.title}</td>
                    <td>${product.price} ₽</td>
                    <td>${product.discount} ₽</td>
                    <td style="min-width: 190px">
                        <button class="quantity-btn decrease" data-product-id="${product.id}">-</button>
                        <input type="number" min="0" max="999" class="quantity-input" data-product-id="${product.id}" value="${product.quantity}">
                        <button class="quantity-btn increase" data-product-id="${product.id}">+</button>
                    </td>
                    <td>${product.total} ₽</td>
                    <td>${product.totalDiscount} ₽</td>
                    <td>
                      <i class="fa fa-trash remove-btn" data-product-id="${product.id}"></i>
                    </td>
                </tr>
            `);
            cartTableBody.append(row);
        });
        cartTableBody.append(
          `
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="font-weight: bold">${cartData.totalPrice} ₽</td>
                <td style="font-weight: bold">${cartData.totalDiscountAll} ₽</td>
                <td></td>
              </tr>
          `
        );

        $cartModalBody.append(cartTable);
        $cartModalBody.append('<button type="button" class="btn btn-primary checkout-btn">Оформить заказ &nbsp;<i class="fas fa-receipt"></i></button>');
    } else {
        $cartModalBody.append('<h3>Добавьте товары в корзину</h3>');
    }

    // Обновляем общую стоимость
    $('.cart-total').text(cartData['totalPrice'] + ' ₽');
}

// Добавление кол-ва товара в корзине
function quantityIncreaseProductCart(productId)
{
    $.ajax({
        url: `/cart/increase-quantity-item/`,
        type: 'POST',
        data: {'productId': productId},
        dataType: 'json',
        success: function(response) {
            initCart();
        },
        error: function(xhr, status, error) {
            let responseJson = xhr.responseJSON;
            if (responseJson !== undefined && responseJson.status === 422) {
                showNotification(responseJson.errors, 'error');
            } else {
                showNotification('Произошла неизвестная ошибка при добавлении товара в корзину', 'error');
            }
        }
    });
}

// Убавление кол-ва товара из корзины
function quantityDecreaseProductCart(productId)
{
    $.ajax({
        url: `/cart/decrease-quantity-item/`,
        type: 'POST',
        data: {'productId': productId},
        dataType: 'json',
        success: function(response) {
            initCart();
        },
        error: function(xhr, status, error) {
            let responseJson = xhr.responseJSON;
            if (responseJson !== undefined && responseJson.status === 422) {
                showNotification(responseJson.errors, 'error');
            } else {
                showNotification('Произошла неизвестная ошибка при убавлении товара из корзины', 'error');
            }
        }
    });
}

// Изменение кол-ва товара
function quantitySetProductCart(productId, quantity)
{
    $.ajax({
        url: `/cart/change-quantity-item/`,
        type: 'POST',
        data: {'productId': productId, 'quantity': quantity},
        dataType: 'json',
        success: function(response) {
            initCart();
        },
        error: function(xhr, status, error) {
            let responseJson = xhr.responseJSON;
            if (responseJson !== undefined && responseJson.status === 422) {
                showNotification(responseJson.errors, 'error');
            } else {
                showNotification('Произошла неизвестная ошибка при изменении количества товара из корзины', 'error');
            }
        }
    });
}

// Удаление товара из корзины
function removeProductFromCart(productId)
{
    $.ajax({
        url: `/cart/remove-item/`,
        type: 'POST',
        data: {'productId': productId},
        dataType: 'json',
        success: function(response) {
            initCart();
        },
        error: function(xhr, status, error) {
            let responseJson = xhr.responseJSON;
            if (responseJson !== undefined && responseJson.status === 422) {
                showNotification(responseJson.errors, 'error');
            } else {
                showNotification('Произошла неизвестная ошибка при удалении товара из корзины', 'error');
            }
        }
    });
}

/** Listeners **/
// Показ модалки
$('.floating-cart').on('click', function () {
    showModal($('#cartModal'))
});

// Уменьшение количества товара
$(document).on('click', '.quantity-btn.decrease', function() {
    let productId = $(this).data('product-id');
    quantityDecreaseProductCart(productId);
});

// Увеличение количества товара
$(document).on('click', '.quantity-btn.increase', function() {
    let productId = $(this).data('product-id');
    quantityIncreaseProductCart(productId);
});

$(document).on('change', '.quantity-input', function () {
    let productId = $(this).data('product-id');
    let quantity = $(this).val();
    quantitySetProductCart(productId, quantity);
});

// Удаление товара из корзины
$(document).on('click', '.remove-btn', function() {
    let productId = $(this).data('product-id');
    removeProductFromCart(productId);
});

// Добавление товара в корзину
$(document).on('click', '.add-to-cart', function () {
    if (authStatus) {
        let productId = $(this).data('product-id');
        quantityIncreaseProductCart(productId);
    } else {
        showNotification('Для добавления товара в корзину необходимо авторизоваться или зарегистрироваться', 'error');
    }
});

$(document).on('click', '.checkout-btn', function () {
    $.ajax({
        url: `/order/create/`,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            closeModal($('#cartModal'))
            initCart();
            setNotificationInLocalStorage(`Ваш заказ №${response.data.order_id} успешно создан!<br>Менеджер свяжется с вами в ближайшее время`);
            window.location.replace('/account/orders/');
        },
        error: function(xhr, status, error) {
            let responseJson = xhr.responseJSON;
            if (responseJson !== undefined && responseJson.error !== undefined) {
                showNotification(responseJson.error, 'error');
            } else {
                showNotification('Произошла неизвестная ошибка при оформлении заказа', 'error');
            }
        }
    });
});