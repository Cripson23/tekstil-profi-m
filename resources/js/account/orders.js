import { showNotification } from "../notification.js";
import { renderPagination } from "../pagination.js";
import { updatePerPageSelector } from "../common.js";
import {showModal} from "../modal.js";

$(document).ready(function() {
	renderOrders(true);
});

let all_page_count;
let filters = {};
let sort = {'field': 'created_at', 'direction': 'DESC'};
let pagination = {'page': 1, 'per_page': 10}
let statusList;

function renderOrders(isInit = false)
{
	$.ajax({
		url: `/order/list/`,
		data: {'filters': filters, 'sort': sort, 'pagination': pagination},
		type: 'POST',
		dataType: 'json', // Ожидаемый тип данных ответа
		success: function(response) {
			let all_items_count = response.data['totalCount'];
			all_page_count = Math.ceil(all_items_count / pagination['per_page']);
			// Вызываем функцию для обновления UI
			updateUI(response.data, isInit);
		},
		error: function(xhr, status, error) {
			showNotification('Не удалось получить список заказов', 'error');
		}
	});
}

// Функция для обновления UI
function updateUI(data, isInit) {
	statusList = data.statusList;
	let ordersHtml = '';
	if (data.items && data.items.length > 0 || !isInit) {
		// Получение списка статусов
		let statusListOptions = '<option value="" selected>Не выбран</option>';
		for (const status in statusList) {
			statusListOptions += `<option value="${status}" ${filters.status === status ? 'selected' : ''}>${statusList[status]}</option>`;
		}
		
		// Обновление заголовка заказов
		ordersHtml = `
			<div class="section-header">
				<h2>&nbsp;Мои заказы</h2>
			    <div class="data-manipulation-panel">
			            <div class="select-area">
					            <div class="input-filter-id">
							<label for="input-filter-id">Номер заказа</label>
								<input id="input-filter-id" type="number" value="${filters.id ?? ''}">
							</div>
							<div class="input-filter-status">
								<label for="select-filter-status">Статус</label>
								<select id="select-filter-status" name="select-filter-status">
									${statusListOptions}
								</select>
							</div>
				            <select class="select-per-page" name="perPage">
								<option value="10" selected>10</option>
								<option value="15">15</option>
								<option value="30">30</option>
							</select>
						</div>
			        </div>
			    </div>
		    </div>
		`;
	}

	updatePerPageSelector(pagination);

	// Обновление списка заказов
	if (data.items && data.items.length > 0) {
		ordersHtml += `
            <div class="section-body">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th data-sort-field="id">Номер заказа <i class="fa fa-sort"></i></th>
					        <th data-sort-field="total_price">Сумма заказа <i class="fa fa-sort"></i></th>
					        <th data-sort-field="total_discount">Скидка <i class="fa fa-sort"></i></th>
					        <th data-sort-field="total_positions_quantity">Позиций <i class="fa fa-sort"></i></th>
					        <th data-sort-field="total_products_quantity">Товаров <i class="fa fa-sort"></i></th>
					        <th data-sort-field="created_at">Дата создания <i class="fa fa-sort"></i></th>
					        <th>Статус</i></th>
                        </tr>
                    </thead>
                    <tbody>`;
		data.items.forEach(order => {
			ordersHtml += `
                        <tr data-order-id="${order.id}">
                            <td>${order.id}</td>
                            <td>${order.total_price}</td>
                            <td>${order.total_discount}</td>
                            <td>${order.total_positions_quantity}</td>
                            <td>${order.total_products_quantity}</td>
                            <td>${order.created_at}</td>
                            <td style="color: ${getStatusColor(order.status)}">${statusList[order.status]}</td>
                        </tr>`;
		});
		ordersHtml += `
                    </tbody>
                </table>
            </div>`;
	} else {
		const notFoundText = isInit ?
		  'У вас нет ни одного заказа &nbsp;&nbsp;<i class="fas fa-frown"></i>' :
		  'По вашим параметрам не найден ни один заказ &nbsp;&nbsp;<i class="fas fa-search"></i>';
		ordersHtml += `<h3 class="not-found-orders__header">${notFoundText}</h3>`;
	}
	$('.orders').html(ordersHtml);

	// Обновление и рендеринг пагинации
	updatePerPageSelector(pagination);
	renderPagination(pagination.page, all_page_count, data.items.length);
	updateSortUI();
}

function updateSortUI() {
	// Удаляем классы сортировки у всех иконок
	$('.orders-table th i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');

	// Получаем элемент иконки для активного поля сортировки
	const activeIcon = $(`.orders-table th[data-sort-field="${sort.field}"] i`);

	// Устанавливаем класс в зависимости от направления сортировки
	if (sort.direction === 'ASC') {
		activeIcon.removeClass('fa-sort fa-sort-down').addClass('fa-sort-up');
	} else {
		activeIcon.removeClass('fa-sort fa-sort-up').addClass('fa-sort-down');
	}
}

function renderOrderModal(orderId)
{
	$.ajax({
		url: `/order/${orderId}/`,
		type: 'GET',
		dataType: 'json', // Ожидаемый тип данных ответа
		success: function(response) {
			updateOrderModal(response.data);
		},
		error: function(xhr, status, error) {
			showNotification('Не удалось получить данные о заказе', 'error');
		}
	});
}

function updateOrderModal(orderData) {
	let orderModal = $('#viewOrderModal');

	// Очистка предыдущих данных
	orderModal.find('.modal-title').empty();
	orderModal.find('.order-details .section-body').empty();
	orderModal.find('.user-details .section-body').empty();
	orderModal.find('.products-details tbody').empty();

	const orderName = `Заказ №${orderData.id}`;
	orderModal.find('.modal-title').append(orderName);

	// Заполнение данных заказа
	const orderInfoHtml = `
        <div><p><strong>Сумма заказа:</strong> ${orderData.total_price} ₽</p></div>
        <div><p><strong>Скидка:</strong> ${orderData.total_discount} ₽</p></div>
        <div><p><strong>Позиций:</strong> ${orderData.total_positions_quantity}</p></div>
        <div><p><strong>Товаров:</strong> ${orderData.total_products_quantity}</p></div>
        <div>
			<p>
				<strong>Статус:</strong> 
				<label style="color: ${getStatusColor(orderData.status)}">${statusList[orderData.status]}</label>
			</p>
		</div>
        <div><p><strong>Дата создания:</strong> ${orderData.created_at}</p></div>
	`
	orderModal.find('.order-details .section-body').append(orderInfoHtml);

	// Заполнение данных пользователя
	const userInfoHtml = `
        <div><p><strong>ФИО:</strong> ${orderData.user_name}</p></div>
       	<div><p><strong>E-mail:</strong> ${orderData.user_email}</p></div>
        <div><p><strong>Адрес доставки:</strong> ${orderData.user_delivery_address}</p></div>
        <div><p><strong>Номер телефона:</strong> ${orderData.user_phone}</p></div>
    `;
	orderModal.find('.user-details .section-body').append(userInfoHtml);

	// Заполнение данных о продуктах
	orderData.products.forEach(function (product) {
		let productRow = $(`
            <tr>
                <td><img src="${product.image_full_path}" style="width: 100px; height: auto;"></td>
                <td>${product.title}</td>
                <td>${product.price} ₽</td>
                <td>${product.discount} ₽</td>
                <td>${product.quantity}</td>
                <td>${product.total_price} ₽</td>
                <td>${product.total_discount} ₽</td>
            </tr>
        `);
		orderModal.find('.products-details tbody').append(productRow);
	});

	showModal(orderModal);
}

function getStatusColor(status)
{
	switch (parseInt(status)) {
		case 3:
			return 'forestgreen';
		case 4:
			return 'tomato';
		default:
			return 'black';
	}
}

/** Listeners **/
$(document).on('click', '.orders-table th', function() {
	const sortField = $(this).data('sort-field');
	if (sortField !== undefined) {
		if (sort.field === sortField) {
			// Переключаем направление сортировки для текущего поля
			sort.direction = sort.direction === 'DESC' ? 'ASC' : 'DESC';
		} else {
			// Устанавливаем новое поле сортировки и направление по умолчанию
			sort.field = sortField;
			sort.direction = 'ASC';
		}
	}
	// Обновляем UI сортировки
	renderOrders();
});

// Показ модалки
$(document).on('click', '.orders-table tbody tr', function () {
	const orderId = $(this).data('order-id');
	renderOrderModal(orderId);
});

// Меняем кол-во на странице и обновляем
$(document).on('change', '.select-per-page', function() {
	pagination.page = 1;
	pagination.per_page = $(this).val();
	renderOrders();
});

// Фильтр по номеру заказа
$(document).on('change', '#input-filter-id', function() {
	pagination.page = 1;
	let filterIdValue = $(this).val();
	if (filterIdValue !== "") {
		filters.id = $(this).val();
		renderOrders();
	} else {
		filters.id = undefined;
		renderOrders();
	}
});

// Фильтр по статусу
$(document).on('change', '#select-filter-status', function() {
	pagination.page = 1;
	let filterStatusValue = $(this).val();
	if (filterStatusValue !== "") {
		filters.status = $(this).val();
		renderOrders();
	} else {
		filters.status = undefined;
		renderOrders();
	}
});

// Обработка клика по кнопкам пагинации
$(document).on('click', '.pagination .page', function() {
	pagination.page = $(this).data('page');
	renderOrders(false, true);
});