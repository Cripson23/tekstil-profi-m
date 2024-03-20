<?php
	/** @var $categoryId */
?>

<link rel="stylesheet" href="/resources/css/main/category.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script type="module" src="/resources/js/main/category.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<div id="product-category-id" data-category-id="<?= $categoryId ?>"></div>
<div class="product-header"></div>
<section class="products"></section>
<div class="pagination"></div>