<?php
/*
 * Настройка параметров локализации
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

// Задание кодировки
if ($locale = setlocale(LC_ALL, "ru_RU.UTF-8")) {
    $encoding = pathinfo($locale, PATHINFO_EXTENSION);

    // Установка внутренней кодировки
    mb_internal_encoding($encoding);
    // Установка кодировки для многобайтовых регулярных выражений
    mb_regex_encoding($encoding);
    // Установка кодировки символов HTTP вывода
    mb_http_output($encoding);
}
