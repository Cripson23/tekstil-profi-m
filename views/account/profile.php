<?php
	/** @var $user */
	/** @var $activityTypesList */
?>

<link rel="stylesheet" href="/resources/css/account/profile.css">
<link rel="stylesheet" href="/resources/css/common.css">

<script type="module" src="/resources/js/account/profile.js"></script>

<section class="profile-header">
    <!-- Добавляем изображение аватара -->
    <img src="/resources/img/<?= $user['avatar'] ?>" alt="Аватар пользователя" class="user-avatar">

    <p><strong>Имя пользователя:</strong> &nbsp;<?= $user['username'] ?></p>
	<p><strong>Дата регистрации:</strong> &nbsp;<?= $user['created_at'] ?></p>
</section>

<section class="profile-details">
	<div class="section-header">
		<h2>&nbsp;Профиль</h2>
	</div>
	<div class="section-body">
		<p><strong>ФИО:</strong> &nbsp;<?= $user['name'] ?></p>
		<p><strong>E-mail:</strong> &nbsp;<?= $user['email'] ?></p>
		<p><strong>Телефон:</strong> &nbsp;<?= $user['prepared_phone'] ?></p>
		<p><strong>Адрес доставки:</strong>&nbsp; <?= $user['delivery_address'] ?></p>
	</div>

	<button class="btn btn-primary edit-profile-button"><i class="fas fa-edit"></i> &nbsp;Редактировать</button>
	<button class="btn btn-danger change-password-button"><i class="fas fa-key"></i> &nbsp;Изменить пароль</button>

</section>

<section class="activity-history">
	<div class="section-header">
		<h2>&nbsp;История активности</h2>
    <div class="data-manipulation-panel">
        <div class="select-area">
            <select class="select-activity-type">
                    <option value="" selected>Все</option>
                <?php foreach ($activityTypesList as $key => $name): ?>
                    <option value="<?= $key ?>"><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            <select class="select-per-page">
                <option value="5" selected>5</option>
                <option value="10">10</option>
                <option value="15">15</option>
            </select>
        </div>
        <div class="pagination"></div>
    </div>
	</div>
	<div class="section-body">
		<div class="activity-history__items"></div>
	</div>
</section>

<?php include 'views/account/modal/edit_profile_modal.php'; ?>
<?php include 'views/account/modal/change_password_modal.php'; ?>
