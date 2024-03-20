import { renderPagination } from "../pagination.js";
import { showNotification } from "../notification.js";
import { updatePerPageSelector } from "../common.js";

$(document).ready(function() {
    renderProducts(true);
});

let all_page_count;
let filters = {};
let sort = {'field': 'created_at', 'direction': 'DESC'};
let pagination = {'page': 1, 'per_page': 10}
let categoryId = $('#product-category-id').data('category-id');

function renderProducts(isInit = false, scrollToHead = false)
{
    if (!categoryId) {
        categoryId = $('#product-category-id').data('category-id');
    }
    if (categoryId) {
        // Вызов функции для загрузки данных продуктов категории
        fetchCategoryProducts(categoryId, isInit, scrollToHead);
    } else {
        showNotification('Произошла ошибка при загрузке данных', 'error');
    }
}

// Функция для получения товаров категории
function fetchCategoryProducts(categoryId, isInit, scrollToHead) {
    $.ajax({
        url: `/product/category/${categoryId}/list/`,
        data: {'filters': filters, 'sort': sort, 'pagination': pagination},
        type: 'POST',
        dataType: 'json', // Ожидаемый тип данных ответа
        success: function(response) {
            let all_items_count = response.data['totalCount'];
            all_page_count = Math.ceil(all_items_count / pagination['per_page']);
            // Вызываем функцию для обновления UI
            updateUI(response.data.categoryTypeName, response.data.categoryName, response.data, isInit, scrollToHead);
        },
        error: function(xhr, status, error) {
            showNotification('Некорректное значение фильтра', 'error');
        }
    });
}

// Функция для обновления UI
function updateUI(categoryTypeName, categoryName, data, isInit, scrollToHead) {
    if (data.items && data.items.length > 0 || !isInit) {
        // Обновление заголовка категории
        $('.product-header').html(`
                <h2 class="category-type__title">
                    ${categoryTypeName} &nbsp;&nbsp;<i class="fa fa-arrow-right"></i>&nbsp;&nbsp; ${categoryName}
                </h2>
                <div class="data-manipulation-panel">
                    <div class="filter-panel">
                        <div class="price-range-slider__wrapper">
                            <div class="input-filter-name">
                                <label for="input-filter-name">Название &nbsp;&nbsp;</label>
                                <input id="input-filter-name" type="text" value="${filters.name || ''}">
                            </div>
                            <div class="input-filter-brand-name">
                                <label for="input-filter-brand-name">Бренд &nbsp;&nbsp;</label>
                                <input id="input-filter-brand-name" type="text" value="${filters.brand_name || ''}">
                            </div>
                            <p><label for="min-price">Цена</label> &nbsp;&nbsp;
                                <input type="number" id="min-price" value="${data.priceRange.minPrice}">
                                &nbsp;&nbsp;&nbsp;
                                <label for="max-price">
                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                </label>
                                &nbsp;&nbsp;&nbsp;
                                <input type="number" id="max-price" value="${data.priceRange.maxPrice}">
                            </p>
                            <div id="price-range-slider"></div>
                        </div>
                    </div>
                    <div class="select-area">
                        <select class="select-sort-type" name="sortBy">
                            <option value="created_at" ${sort.field === 'created_at' ? 'selected' : ''}>По новизне</option>
                            <option value="price_asc" ${sort.field === 'price' && sort.direction === 'ASC' ? 'selected' : ''}>Дешевле</option>
                            <option value="price_desc" ${sort.field === 'price' && sort.direction === 'DESC' ? 'selected' : ''}>Дороже</option>
                            <option value="discount_price" ${sort.field === 'discount_price' ? 'selected' : ''}>По скидке</option>
                            <option value="name" ${sort.field === 'name' ? 'selected' : ''}>По названию</option>
                            <option value="brand_name" ${sort.field === 'brand_name' ? 'selected' : ''}>По бренду</option>
                        </select>
                        <select class="select-per-page" name="perPage">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                            <option value="30">30</option>
                        </select>
                    </div>
                </div>
            `);
    } else {
        $('.product-header').html(`
            <h2 class="category-type__title">
                ${categoryTypeName} &nbsp;&nbsp;<i class="fa fa-arrow-right"></i>&nbsp;&nbsp; ${categoryName}
            </h2>`);
        $('.category-type__title').css('margin', '0px');
    }
    updatePerPageSelector(pagination);

    // Обновление списка продуктов
    if (data.items && data.items.length > 0) {
        let productsHtml = data.items.map(function(product) {
            let priceHtml;
            if (product.old_price !== null) {
                priceHtml = `<span class="current-price-with-old">${product.price} ₽</span>
                  <span class="old-price">${product.old_price} ₽</span>`;
            } else {
                priceHtml = `<span class="current-price">${product.price} ₽</span>`;
            }
            return `
                    <div class="container">
                       <div class="products-all">
                            <div class="product__wrapper">
                                <img src="/resources/img/products/${product.id}/${product.files[0]}" class="product-img">
                                <div class="product-content">
                                    <h3 class="product-content__subtitle">${product.name}</h3>
                                    <h2 class="product-content__title">${product.brand_name}</h2>
                                    <p class="product-content__text">${product.text}</p>
                                    <p class="product-content__price">
                                        ${priceHtml}
                                    </p>
                                    <button class="button-product add-to-cart" data-product-id="${product.id}">В корзину</button>
                                    ${product.description ? `<p class="product-content__desc">${product.description}</p>` : ''}
                                </div>
                            </div>
                       </div>
                    </div>
                `;
        }).join('');

        $('.products').html(productsHtml);
    } else {
        const notFoundText = isInit ?
            'В данной категории пока что нет товаров &nbsp;&nbsp;&nbsp;<i class="fas fa-frown"></i>' :
            'По вашим параметрам не найден ни один товар &nbsp;&nbsp;&nbsp;<i class="fas fa-search"></i>';
        $('.products').html(`<h3 class="not-found-products__header">${notFoundText}</h3>`);
    }

    // Инициализация слайдера цен
    initPriceRangeSlider();

    // Обновление и рендеринг пагинации
    renderPagination(pagination.page, all_page_count, data.items.length);

    if (scrollToHead) {
        scrollToElement('.product-header');
    }
}

// Слайдер диапазона цен
function initPriceRangeSlider()
{
    let minPrice = parseInt($("#min-price").val());
    let maxPrice = parseInt($("#max-price").val());

    let minPriceValue = filters['price'] !== undefined && filters['price']['from'] !== undefined
        ? filters['price']['from']
        : parseInt($("#min-price").val());
    let maxPriceValue = filters['price'] !== undefined && filters['price']['to'] !== undefined
        ? filters['price']['to']
        : parseInt($("#max-price").val());

    $("#price-range-slider").slider({
        range: true,
        min: minPrice,
        max: maxPrice,
        values: [minPriceValue, maxPriceValue], // Начальные значения
        slide: function(event, ui) {
            let minPrice = parseInt(ui.values[0]);
            let maxPrice = parseInt(ui.values[1]);
            $("#min-price").val(minPrice)
            $("#max-price").val(maxPrice)
        },
        stop: function(event, ui) { // Используйте событие `stop` или `change`
            filters['price'] = {'from': ui.values[0], 'to': ui.values[1]};
            renderProducts();
        }
    });

    $("#min-price").val($("#price-range-slider").slider("values", 0));
    $("#max-price").val($("#price-range-slider").slider("values", 1));
}

/** Listeners **/
// Фильтр по названию
$(document).on('change', '#input-filter-name', function () {
    let inputValue = $(this).val();

    if (inputValue !== undefined && inputValue !== '') {
        filters['name'] = inputValue;
    } else {
        filters['name'] = undefined;
    }

    renderProducts();
});

// Фильтр по бренду
$(document).on('change', '#input-filter-brand-name', function () {
    let inputValue = $(this).val();

    if (inputValue !== undefined && inputValue !== '') {
        filters['brand_name'] = inputValue;
    } else {
        filters['brand_name'] = undefined;
    }

    renderProducts();
});

// Фильтр по диапазону цены
$(document).on('change', '#min-price, #max-price', function() {
    let minPrice = parseInt($('#min-price').val(), 10);
    let maxPrice = parseInt($('#max-price').val(), 10);

    if (isNaN(minPrice) || isNaN(maxPrice)) {
        return;
    }

    $("#price-range-slider").slider("values", [minPrice, maxPrice]);

    if (minPrice > maxPrice) {
        $('#max-price').val(minPrice);
        return showNotification('Начальное значение не может быть больше конечного!', 'error');
    }

    filters['price'] = {'from': minPrice, 'to': maxPrice};

    renderProducts();
});

// Сортировка
$(document).on('change', '.select-sort-type', function () {
    let selectedValue = $(this).val();

    if (selectedValue !== undefined && selectedValue !== '') {
        switch (selectedValue) {
            case 'created_at':
                sort = {'field': selectedValue, 'direction': 'DESC'};
                break;
            case 'price_asc':
                sort = {'field': 'price', 'direction': 'ASC'};
                break;
            case 'price_desc':
                sort = {'field': 'price', 'direction': 'DESC'};
                break;
            case 'discount_price':
                sort = {'field': selectedValue, 'direction': 'DESC'};
                break;
            case 'name':
            case 'brand_name':
                sort = {'field': selectedValue, 'direction': 'ASC'};
                break;
        }

        renderProducts();
    }
});

// Обработка клика по кнопкам пагинации
$(document).on('click', '.pagination .page', function() {
    pagination['page'] = $(this).data('page');

    renderProducts(false, true);
});

// Меняем кол-во на странице и обновляем
$(document).on('change', '.select-per-page', function () {
    let selectedValue = $(this).val();
    if (selectedValue !== undefined && selectedValue !== '') {
        pagination.per_page = selectedValue;
    } else {
        pagination.per_page = 10;
    }

    renderProducts();
});

function scrollToElement(selector) {
    let target = $(selector);
    if (target.length) {
        $('html, body').animate({
            scrollTop: target.offset().top
        }, 1000);
    }
}


