<?php
/*
 * Подключение библиотеки Fenom template
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

 // Регистрация библиотеки для загрузки через SPL
 $phpMailerDir = $dir['vendor'] . DS .'PHPMailer'. DS .'src'. DS;
 $classLoader->addSymlinks(array(
     'POP3' =>  $phpMailerDir .'POP3.php',
     'SMTP' =>  $phpMailerDir .'SMTP.php',
     'PHPMailer' =>  $phpMailerDir .'PHPMailer.php'
 ));
