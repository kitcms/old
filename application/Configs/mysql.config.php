<?php
/*
 * Настройка драйвера для базы данных MySql
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

use Classes\Database\ORM;

// Конфигурация соединения с базой данных по умолчанию
ORM::configure(array(
    'connection_string' => 'mysql:host=localhost;port=3306;dbname=database',
    'username' => 'username',
    'password' => 'password',
    'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'),
    'error_mode' => PDO::ERRMODE_WARNING, // ERRMODE_SILENT, ERRMODE_EXCEPTION
    'logging' => false,
    'caching' => true,
    'caching_auto_clear' => true
), null, 'default');
