/** Pagination  **/
export function renderPagination(page, all_page_count, all_items_count) {
    $('.pagination').empty(); // Очистить текущие кнопки пагинации

    if (all_items_count === 0) {
        return;
    }

    // Добавляем кнопку "Предыдущая", если это не первая страница
    if (page > 1) {
        $('.pagination').append(`<button class="page" data-page="${page - 1}"><<</button>`);
    }

    // Всегда отображаем первую страницу
    if (page > 3) {
        $('.pagination').append(`<button class="page" data-page="1">1</button>`);
        if (page > 4) {
            $('.pagination').append(`<span>...</span>`);
        }
    }

    // Отображаем две страницы перед текущей страницей, если они существуют
    for (let i = Math.max(1, page - 2); i < page; i++) {
        $('.pagination').append(`<button class="page" data-page="${i}">${i}</button>`);
    }

    if (all_page_count > 1) {
        // Отображаем текущую страницу
        $('.pagination').append(`<button class="page active" data-page="${page}">${page}</button>`);
    }

    // Отображаем две страницы после текущей страницы, если они существуют
    for (let i = page + 1; i <= Math.min(page + 2, all_page_count); i++) {
        $('.pagination').append(`<button class="page" data-page="${i}">${i}</button>`);
    }

    // Всегда отображаем последнюю страницу, если есть пропуски
    if (page < all_page_count - 2) {
        if (page < all_page_count - 3) {
            $('.pagination').append(`<span>...</span>`);
        }
        $('.pagination').append(`<button class="page" data-page="${all_page_count}">${all_page_count}</button>`);
    }

    // Добавляем кнопку "Следующая", если это не последняя страница
    if (page < all_page_count) {
        $('.pagination').append(`<button class="page" data-page="${page + 1}">>></button>`);
    }
}