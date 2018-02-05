<?php
/*
 * Установщик
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes;

$model = new Model\Site();
$schema = $model->schema();
if (false === $schema->findOne()) {
    $data = array(
        'name' => 'Site',
        'engine' => 'MyISAM',
        'collation' => 'utf8_general_ci',
        'comment' => 'Сайты'
    );
    $schema->create($data)->save();
}

$model = new Model\Section();
$schema = $model->schema();
if (false === $schema->findOne()) {
    $data = array(
        'name' => 'Section',
        'engine' => 'MyISAM',
        'collation' => 'utf8_general_ci',
        'comment' => 'Разделы сайтов'
    );
    $schema->create($data)->save();
}

$model = new Model\Template();
$schema = $model->schema();
if (false === $schema->findOne()) {
    $data = array(
        'name' => 'Template',
        'engine' => 'MyISAM',
        'collation' => 'utf8_general_ci',
        'comment' => 'Макеты дизайна'
    );
    $schema->create($data)->save();
}

$model = new Model\Group();
$schema = $model->schema();
if (false === $schema->findOne()) {
    $data = array(
        'name' => 'Group',
        'engine' => 'MyISAM',
        'collation' => 'utf8_general_ci',
        'comment' => 'Группы пользователей'
    );
    if ($schema->create($data)->save()) {
        $model->create(array('title' => 'Администраторы'))->save();
        $model->create(array('title' => 'Модераторы'))->save();
        $model->create(array('title' => 'Пользователи'))->save();
    }
}

$model = new Model\User();
$schema = $model->schema();
if (false === $schema->findOne()) {
    $data = array(
        'name' => 'User',
        'engine' => 'MyISAM',
        'collation' => 'utf8_general_ci',
        'comment' => 'Пользователи'
    );
    if ($schema->create($data)->save()) {
        $data = array(
            'group' => 1,
            'login' => 'user',
            'name' => 'Администратор',
            'email' => 'email@domain.tld',
            'password' => 'user',
            'description' => 'Тестовый пользователь'
        );
        $model->create($data)->save();
    }
}
