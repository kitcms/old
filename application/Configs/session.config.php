<?php
/*
 * Настройка параметров сессии
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

// Автоматическое включение сессии
if (PHP_SESSION_NONE === session_status()) {
    ob_start();
    session_start();
}
