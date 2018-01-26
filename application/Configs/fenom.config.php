<?php
/*
 * Подключение библиотеки Fenom template
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

$locations = array(
    $dir['vendor'] . DS .'fenom'. DS .'fenom'. DS .'src',
    $dir['vendor'] . DS .'fenom'. DS .'storage'. DS .'src'
);

$classLoader->addFallbacks($locations);
