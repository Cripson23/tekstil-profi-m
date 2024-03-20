<div id="registrationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header modal-header-secondary">
	        <h5 class="modal-title">Создайте аккаунт</h5>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="registrationForm">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username">
                <div class="error" id="error-username"></div>

                <label for="name">ФИО</label>
                <input type="text" id="name" name="name">
                <div class="error" id="error-name"></div>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email">
                <div class="error" id="error-email"></div>

                <label for="phone">Номер телефона (в формате +7хххххххххх)</label>
                <input type="text" id="phone" name="phone">
                <div class="error" id="error-phone"></div>

                <label for="delivery_address">Адрес доставки</label>
                <input type="text" id="delivery_address" name="delivery_address">
                <div class="error" id="error-delivery_address"></div>

                <label for="password">Пароль</label>
                <input type="password" id="password" name="password">
                <div class="error" id="error-password"></div>

                <label for="password_confirm">Повторите пароль</label>
                <input type="password" id="password_confirm" name="password_confirm">
                <div class="error" id="error-password_confirm"></div>
                <br>

                <button type="submit" class="btn btn-secondary">Зарегистрироваться &nbsp;<i class="fas fa-user-plus"></i></button>
            </form>
        </div>
    </div>
</div>
