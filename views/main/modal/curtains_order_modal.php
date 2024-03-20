<div class="modal" id="curtainsOrderModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header modal-header-primary">
				<h5 class="modal-title">Заказ пошива штор</h5>
				<span class="close">&times;</span>
			</div>
			<div class="modal-body">
				<p>Оставьте информацию о себе (для обратной связи) и пожелания к пошиву в форме ниже.</p>
				<p>Мы обязательно свяжемся с вами для обсуждения деталей!</p>
				<br>
				<form class="modal-form" id="curtainsOrderForm">
					<label for="curtains-name">ФИО</label>
					<input type="text" id="curtains-name" name="curtains-name">
					<div class="error" id="error-curtains-name"></div>

					<label for="curtains-email">E-mail</label>
					<input type="email" id="curtains-email" name="curtains-email">
					<div class="error" id="error-curtains-email"></div>

					<label for="curtains-phone">Телефон (в формате +7хххххххххх)</label>
					<input type="text" id="curtains-phone" name="curtains-phone">
					<div class="error" id="error-curtains-phone"></div>

					<label for="curtains-wish">Пожелания к пошиву</label>
					<textarea id="curtains-wish" name="curtains-wish"></textarea>
					<div class="error" id="error-curtains-wish"></div>
					<br>

					<button type="submit" class="btn btn-primary">Отправить &nbsp;<i class="fa fa-paper-plane"></i></button>
				</form>
			</div>
		</div>
	</div>
</div>