<div class="modal" id="loginModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-primary">
                <h5 class="modal-title">Авторизация</h5>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form class="modal-form" id="loginForm">
                    <label for="login_username">Имя пользователя</label>
                    <input type="text" id="login_username" name="login_username">
                    <div class="error" id="error-login_username"></div>

                    <label for="login_password">Пароль</label>
                    <input type="password" id="login_password" name="login_password">
                    <div class="error" id="error-login_password"></div>
	                  <br>

                    <button type="submit" class="btn btn-primary">Войти &nbsp;<i class="fas fa-sign-in-alt"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>