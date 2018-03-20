<?php
/*
 * Фронт-контроллер системы
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

use Classes\Application;

// Определение системных загрузчиков
$files = glob('{.,..}/*/bootstrap*.php', GLOB_BRACE);
foreach ($files as $file) {
    // Не допускается использование загрузчиков в одной директории с фронт-контроллером системы
    $file = realpath(__DIR__ .'/'. $file);
    if (__DIR__ !== pathinfo($file, PATHINFO_DIRNAME) && is_file($file)) {
        require_once $file;
    }
}

// Инициализация и запуск приложения
$app = new Application();
$app->run();
