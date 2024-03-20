<?php

/** @var $title */
/** @var $view */
/** @var $auth */

$configCompany = Config::COMPANY;

// Get notification from PHP
if (!empty($_SESSION['notification'])) {
		$notification = $_SESSION['notification'];
		unset($_SESSION['notification']); // Сразу очищаем значение в сессии
} else {
		$notification = null;
}

?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <title><?= $title ?></title>

        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Asap+Condensed:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

        <link rel="icon" href="/resources/img/favicon.ico" type="image/x-icon">
        <link rel="shortcut icon" href="/resources/img/favicon.ico" type="image/x-icon">

        <link rel="stylesheet" href="/resources/css/common.css">
        <link rel="stylesheet" href="/resources/css/main/template.css">
        <link rel="stylesheet" href="/resources/css/modal.css">
        <link rel="stylesheet" href="/resources/css/cart.css">

	      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	      <script type="module" src="/resources/js/common.js"></script>
	      <script type="module" src="/resources/js/cart.js"></script>
        <script type="module" src="/resources/js/main/template.js"></script>

    </head>

    <body>
      <div class="content-wrap">
        <header class="header">
            <div class="container container--header" style="margin-left: <?= !$auth['status'] ? 350 : 363 ?>px">
                <div class="header-all">
                    <a href="/" class="header__logo">Tekstil-<span class="header__link">Profi M</span></a>
                    <div class="header-links">
                        <a href="/#child" class="header-links__item">Детская продукция</a>
                        <a href="/#invent" class="header-links__item">Мягкий Инвентарь</a>
                        <a href="/#map" class="header-links__item">Контакты</a>
                        <a href="/#map" class="header-links__item">Доставка</a>
                    </div>
                    <?php if (!$auth['status']): ?>
                        <div class="header-phone">
                            <!-- Кнопка "Регистрация" -->
                            <button class="btn btn-primary login-button"><i class="fas fa-sign-in-alt"></i> &nbsp;Вход</button>
                            <button class="btn btn-secondary register-button"><i class="fas fa-user-plus"> &nbsp;</i>Регистрация</button>
                        </div>
                    <?php else: ?>
                        <div class="header-phone">
                            <a href="/account/profile/" class="btn btn-primary personal-button"><i class="fas fa-user-circle"></i>&nbsp;&nbsp;&nbsp;&nbsp;Кабинет ( <?= $auth['username'] ?> )</a>
	                          <a href="/auth/logout/" class="btn btn-danger logout-button">Выйти &nbsp;<i class="fas fa-sign-out-alt"></i></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main>
            <section class="hero">
                <div class="container">
                    <div class="hero-content">
                        <div class="hero-content-info">
                            <div class="hero-content-info-double">
                                <h1 class="hero-content-info__title">Комплектация детских садов, больниц, общежитий, гостиниц</h1>
                                <p class="hero-content-info__text">Оптом от производителя. Работаем по всей России</p>
                            </div>

                            <a href="#map" class="hero-button">Подробнее</a>

                        </div>
                    </div>
                </div>
            </section>

            <?php include $view; ?>

            <section class="map">
                <div class="container" id = "map" >

                    <div class="map-content">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d397.7511674636028!2d37.91158254155231!3d55.67124572279032!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x414ab7ccd4640001%3A0x6a6729e0d5dc561b!2z0KLQtdC60YHRgtC40LvRjC3Qn9GA0L7RhNC4INCc!5e0!3m2!1sru!2sru!4v1708966257584!5m2!1sru!2sru" width="800" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        <div class="map-content-info">
                            <p class="map-content-info__adress"><?= $configCompany['ADDRESS'] ?></p>
                            <p class="map-content-info__time"><?= $configCompany['TIME_WORK'] ?></p>
                            <div class="map-content-info-two">
                                <a href="#" class="map-content-info-two__number"><?= $configCompany['PHONE_1'] ?></a>
                                <a href="#" class="map-content-info-two__num"><?= $configCompany['PHONE_2'] ?></a>
                            </div>
                            <p class="map-content-info__email"><?= $configCompany['EMAIL'] ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
      </div>
      <footer class="footer">
          <div class="container">
              <div class="footer-all">
                  <a href="/" class="footer__logo"><?= $configCompany['LOGO_1'] ?><span class="footer__link"><?= $configCompany['LOGO_2'] ?></span></a>
                  <div class="footer-con">
                      <a href="#" class="footer-all__email"><?= $configCompany['EMAIL'] ?></a>
                      <a href="#" class="footer-all__number"><?= $configCompany['PHONE_1'] ?></a>
                  </div>
              </div>
              <p class="footer__copyright">© <?= $configCompany['NAME'] ?>&nbsp;&nbsp;Все права защищены. 2024</p>
          </div>
      </footer>

      <?php include 'views/main/modal/registration_modal.php'; ?>
      <?php include 'views/main/modal/login_modal.php'; ?>

      <?php if ($auth['status']) include 'views/common/modal/cart_modal.php'; ?>

      <div class="modal-backdrop"></div>

      <?php if ($auth['status']): ?>
        <div class="floating-cart">
	        <i class="fa fa-shopping-cart"></i>
	        <span class="cart-total"></span>
        </div>
      <?php endif; ?>

      <?php if ($auth['status']): ?>
          <i id="authStatus" hidden></i>
      <?php endif; ?>
    </body>

    <!-- Set notification from PHP -->
		<?php if ($notification): ?>
        <script>
            let notification = <?= $notification; ?>;
            localStorage.setItem('notification', JSON.stringify(notification));
            location.reload();
        </script>
		<?php endif; ?>
</html>