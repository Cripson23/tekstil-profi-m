<?php

namespace general;

class Logger
{
    public static function logMessage($message, $logFile = "app.log") {
        // Путь к директории логов
        $logDirectory = "./log";
        // Полный путь к файлу лога
        $logPath = $logDirectory . '/' . $logFile;

        // Проверяем, существует ли директория логов, если нет - создаем
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        }

        // Получаем текущую дату и время
        $currentTime = date('Y-m-d H:i:s');
        // Форматируем строку лога
        $log = $currentTime . ' ' . $message . "\n";
        // Добавляем запись в файл лога
        file_put_contents($logPath, $log, FILE_APPEND);
    }
}