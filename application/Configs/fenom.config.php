<?php
/*
 * Подключение библиотеки Fenom template
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

$location = $dir['vendor'] . DS .'fenom'. DS .'fenom'. DS .'src';

$classLoader->addSymlinks(array(
    'Fenom' => $location . DS .'Fenom.php'
));

Fenom::registerAutoload();
