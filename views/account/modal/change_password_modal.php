<?php
/** @var $user */
?>

<div class="modal" id="changePasswordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-danger">
                <h5 class="modal-title">Изменение пароля</h5>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form class="modal-form" id="changePasswordForm">
                    <label for="password">Текущий пароль</label>
                    <input type="password" id="password" name="password"">
                    <div class="error" id="error-password"></div>

                    <label for="new_password">Новый пароль</label>
                    <input type="password" id="new_password" name="new_password">
                    <div class="error" id="error-new_password"></div>

                    <label for="new_password_confirm">Повтор нового пароля</label>
                    <input type="password" id="new_password_confirm" name="new_password_confirm">
                    <div class="error" id="error-new_password_confirm"></div>
                    <br>

                    <button type="submit" class="btn btn-danger"><i class="fas fa-key"></i> &nbsp;Изменить</button>
                </form>
            </div>
        </div>
    </div>
</div>