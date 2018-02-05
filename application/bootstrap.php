<?php
/*
 * Фронт-контроллер системы
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

// Определение константы, содержащей разделитель директорий
define('DS', DIRECTORY_SEPARATOR);
// Определение константы, содержащей разделитель пространства имен
define('NS', '\\');

// Определение корневой директории
$dir['root'] = realpath(str_replace(basename(getcwd()), null, getcwd()));
// Определение публичной директории
$dir['public'] = realpath(getcwd());
// Определение системной директории
$dir['application'] = dirname(__FILE__);

// Изменение текущего каталога PHP на системную директорию
chdir($dir['application']);

// Директория с файлами сторонних библиотек
$dir['vendor'] = $dir['application'] . DS .'Vendors';

// Нахождение ключевых функций, конфигурационных файлов и их подключение
$files = glob($dir['application'] .'/*/*.{function,config}.php', GLOB_BRACE);
foreach ($files as $file) {
    if (is_file($file)) {
        // Определение директории с файлами функций
        if (!isset($dir['function']) && preg_match('/function.php$/', $file)) {
            $dir['function'] = dirname($file);
        }
        // Определение директории с конфигурационными файлами
        if (!isset($dir['config']) && preg_match('/config.php$/', $file)) {
            $dir['config'] = dirname($file);
        }
        require_once $file;
    }
}
