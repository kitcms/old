<?php
/*
 * Подключение библиотеки Paris
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

$location = $dir['vendor'] . DS .'j4mie'. DS .'paris';

$classLoader->addSymlinks(array(
    'Model' => $location . DS .'paris.php',
    'ORMWrapper' => $location . DS .'paris.php',
    'ParisMethodMissingException' => $location . DS .'paris.php'
));
