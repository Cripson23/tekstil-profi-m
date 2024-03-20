<?php
	/** @var $user */
?>

<div class="modal modal-edit" id="editProfileModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header modal-header-primary">
				<h5 class="modal-title">Редактирование профиля</h5>
				<span class="close">&times;</span>
			</div>
			<div class="modal-body">
				<form class="modal-form" id="editProfileForm">
					<label for="name">ФИО</label>
					<input type="text" id="name" name="name" value="<?= $user['name'] ?>">
					<div class="error" id="error-name"></div>

					<label for="email">E-mail</label>
					<input type="email" id="email" name="email" value="<?= $user['email'] ?>">
					<div class="error" id="error-email"></div>

					<label for="delivery_address">Адрес доставки</label>
					<input type="text" id="delivery_address" name="delivery_address" value="<?= $user['delivery_address'] ?>">
					<div class="error" id="error-delivery_address"></div>

					<label for="phone">Телефон</label>
					<input type="text" id="phone" name="phone" value="<?= $user['phone'] ?>">
					<div class="error" id="error-phone"></div>
					<br>

					<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> &nbsp;Сохранить</button>
				</form>
			</div>
		</div>
	</div>
</div>