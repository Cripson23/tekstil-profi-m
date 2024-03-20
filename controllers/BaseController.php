<?php

namespace controllers;

use Config;
use User;

class BaseController
{
    protected string $viewsSectionName;
    protected string $companyName;
	protected ?string $previousUrl;

    protected array $session = [];
    protected array $cookies = [];
    protected array $getParams = [];
    protected array $postParams = [];

    public function __construct()
    {
        $this->companyName = Config::COMPANY['NAME'];
		$this->previousUrl = $_SERVER['HTTP_REFERER'];

        session_start(); // сессия начата до работы с $_SESSION

        $this->getParams = $this->sanitizeData($_GET);
        $this->postParams = $this->sanitizeData($_POST);
        $this->session = $this->sanitizeData($_SESSION); // Обработка $_SESSION по месту использования
        $this->cookies = $this->sanitizeData($_COOKIE);
    }

    /**
     * Отрисовывает представление с шаблоном
     *
     * @param string|null $subFolder
     * @param string $viewFileName
     * @param array $variables
     * @return void
     */
    protected function render(string $viewFileName, array $variables = [], ?string $subFolder = null)
    {
        extract($variables);

        $subFolder = !$subFolder ? '' : ($subFolder . '/');

        $view = "views/$this->viewsSectionName/$subFolder$viewFileName.php";
        $template = "views/$this->viewsSectionName/template.php";

        include $template;
    }

	/**
	 * @return void
	 */
	protected function renderNotFoundPage()
	{
		include 'views/common/404.php';
		exit;
	}

    /**
     * Обработка входных данных
     *
     * @param array $data
     * @return array
     */
    private function sanitizeData(array $data): array
    {
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitizedData[$key] = $this->sanitizeData($value); // Рекурсивная очистка для многомерных массивов
            } else {
				$value = htmlspecialchars($value);
                // FILTER_SANITIZE_SPECIAL_CHARS для предотвращения XSS
                $sanitizedData[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $sanitizedData;
    }

    /**
     * Возвращает полный заголовок страницы с названием сайта.
     *
     * @param string $pageTitle Заголовок страницы.
     * @return string Полный заголовок страницы.
     */
    protected function getTitle(string $pageTitle): string
    {
        return "{$pageTitle} | {$this->companyName}";
    }

	/**
	 * Отправляем ответ в JSON
	 * @param array $response
	 * @return void
	 */
    protected function sendJsonResponse(array $response)
    {
		$status = $response['status'];

        if (empty($status)) {
            $status = $response['success'] ? 200 : 500;
        }

        header('Content-Type: application/json');
        header("HTTP/1.1 {$status}");

        echo json_encode($response);
		exit;
    }

	/**
	 * @return void
	 */
	protected function redirect(string $path)
	{
		header("Location: {$path}");
		exit;
	}

	/** Auth **/

	/**
	 * Получает данные авторизации пользователя в сессии
	 *
	 * @return array
	 */
	protected function getAuthData(): array
	{
		return [
		  'user_id' => !empty($this->session['user_id']) ? $this->session['user_id'] : null,
		  'username' => !empty($this->session['username']) ? $this->session['username'] : null,
		  'status' => !empty($this->session['user_id']) && !empty($this->session['username']),
		];
	}

	/**
	 * Задаёт уведомление через сессию
	 *
	 * @param string $message
	 * @param string $type
	 * @return void
	 */
	protected static function setSessionNotification(string $message, string $type = 'success')
	{
		$_SESSION['notification'] = json_encode(['message' => $message, 'type' => $type]);
	}

	/**
	 * Проверяет пользователя на авторизованность и активность, возвращает его данные
	 * @return array
	 */
	protected function getAuthUser(): array
	{
		$authData = $this->getAuthData();

		if (!$authData['status']) {
			$this->setSessionNotification('Необходимо авторизоваться!', 'error');
			$this->redirect('/');
			exit;
		}

		$userData = User::read('id', $authData['user_id']);
		if (!$userData) {
			$this->setSessionNotification('Произошла ошибка при получении данных', 'error');
			$this->redirect('/');
			exit;
		}

		if ($userData['status'] == User::STATUSES['STATUS_BLOCK']) {

			$_SESSION['user_id'] = null;
			$_SESSION['username'] = null;

			$this->setSessionNotification('К сожалению, ваш аккаунт заблокирован', 'error');
			$this->redirect('/');
			exit;
		}

		return $userData;
	}
}