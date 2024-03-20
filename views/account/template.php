<?php
	/** @var $title */
	/** @var $view */

	$configCompany = Config::COMPANY;
?>

<!DOCTYPE html>
<html lang="ru">
	<head>
		<title><?= $title ?></title>

		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="stylesheet" href="/resources/css/common.css">
		<link rel="stylesheet" href="/resources/css/account/template.css">
		<link rel="stylesheet" href="/resources/css/modal.css">
		<link rel="stylesheet" href="/resources/css/cart.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <link rel="icon" href="/resources/img/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/resources/img/favicon.ico" type="image/x-icon">

		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

		<script type="module" src="/resources/js/common.js"></script>
    <script type="module" src="/resources/js/cart.js"></script>

	</head>
	<body>
		<div class="content-wrap">
			<header>
				<div class="container">
					<nav>
						<a href="/" class="header__logo"><?= $configCompany['LOGO_1'] ?><span class="header__link"><?= $configCompany['LOGO_2'] ?></span></a>
						<div class="nav-links">
							<a href="/" class="btn btn-primary"><i class="fa fa-home" aria-hidden="true"></i> &nbsp;Главная</a>
	                        <?php if (preg_match('#^/account/profile/?$#', $_SERVER['REQUEST_URI'])): ?>
							    <a href="/account/orders/" class="btn btn-primary orders-button"><i class='fas fa-shopping-basket'></i>&nbsp; Мои заказы</a>
	                        <?php else: ?>
	                            <a href="/account/profile/" class="btn btn-primary profile-button"><i class="fas fa-user-circle"></i>&nbsp; Мой профиль</a>
	                        <?php endif; ?>
							<a href="/auth/logout/" class="btn btn-danger logout-button">Выйти&nbsp; <i class="fas fa-sign-out-alt"></i></a>
						</div>
					</nav>
				</div>
			</header>

			<main class="container">
					<?php include $view ?>
			</main>
		</div>
		<footer>
			<div class="container">© <?= $configCompany['NAME'] ?>&nbsp;&nbsp;Все права защищены. 2024</div>
		</footer>

		<div class="modal-backdrop"></div>
		<div class="floating-cart">
			<i class="fa fa-shopping-cart"></i>
			<span class="cart-total"></span>
		</div>

		<i id="authStatus" hidden></i>

    <?php include 'views/common/modal/cart_modal.php'; ?>
	</body>
</html>