<?php
/*
 * Подключение библиотеки Idiorm
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

$location = $dir['vendor'] . DS .'j4mie'. DS .'idiorm';

$classLoader->addSymlinks(array(
    'ORM' => $location . DS .'idiorm.php',
    'IdiormString' => $location . DS .'idiorm.php',
    'IdiormResultSet' => $location . DS .'idiorm.php',
    'IdiormStringException' => $location . DS .'idiorm.php',
    'IdiormMethodMissingException' => $location . DS .'idiorm.php'
));
