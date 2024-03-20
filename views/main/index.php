<?php
	/** @var $auth */
	/** @var $indexProductId */
?>

<link rel="stylesheet" href="/resources/css/main/index.css">

<section class="product">
    <div class="container">
        <div class="product-all">
            <div class="product-con">
                <div class="product-arrow product-arrow__left"></div>
                <div class="product-arrow product-arrow__right"></div>
                <img class="product-img">
                <div class="product-con-two">
                    <div class="product-con__tabs product-con__tabs--one"></div>
                    <div class="product-con__tabs"></div>
                    <div class="product-con__tabs"></div>
                </div>
            </div>
            <div class="product-content">
	              <div id="index-product-id" data-id="<?= $indexProductId ?>"></div>
            </div>
        </div>
    </div>
</section>

<section class="child">
	<div class="container container--child" id = "child"></div>
</section>

<section class="curtains">
    <div class="container">
        <div class="curtains-all">
            <div class="curtains-content">
                <h2 class="curtains-content__title">Пошив штор для детских садов по вашим размерам</h2>
                <p class="curtains-content__text">Мы отшиваем шторы по индивидуальным дизайнам, сохраняя общий стиль для всего детского сада, при этом учитываем пожелания каждой возрастной группы.</p>
                <p class="curtains-content__desc">У нас можно заказать портьеры для актовых залов, которые будут создавать праздничное настроение многие годы!</p>

                <button id="curtains-order" class="btn btn-primary">Заказать</button>
            </div>
            <img src="/resources/img/curt.jpg" alt="curtains" class="curtains__img">
        </div>
    </div>
</section>

<section class="invent">
    <div class="container container--invent" id = "invent"></div>
</section>

<?php
	if ($auth['status']) {
	  include 'views/main/modal/curtains_order_auth_modal.php';
	} else {
	  include 'views/main/modal/curtains_order_modal.php';
	}
?>

<script type="module" src="/resources/js/main/index.js"></script>
