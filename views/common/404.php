<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Страница не найдена</title>
	<link rel="stylesheet" href="/resources/css/404.css">
</head>
<body>
<div class="container">
	<h1>404</h1>
	<p>К сожалению, страница, которую вы искали, не может быть найдена.</p>
	<a href="<?= $_SERVER['HTTP_REFERER'] ?? '/' ?>">Назад</a>
</div>
</body>
</html>