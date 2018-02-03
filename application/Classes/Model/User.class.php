<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\Model;

use Classes\Database\Model;

class User extends Model
{
    protected $_table = 'User';
    protected $password;

    protected $fields = array(
        array(
            'field' => 'group',
            'type' => 'int(10) unsigned',
            'null' => 'yes',
            'key' => 'mul',
            'comment' => 'Идентификатор группы'
        ),
        array(
            'field' => 'login',
            'type' => 'varchar(255)',
            'key' => 'uni',
            'comment' => 'Логин'
        ),
        array(
            'field' => 'name',
            'type' => 'varchar(255)',
            'comment' => 'Имя пользователя'
        ),
        array(
            'field' => 'email',
            'type' => 'varchar(255)',
            'key' => 'uni',
            'comment' => 'Электронный адрес'
        ),
        array(
            'field' => 'password',
            'type' => 'varchar(255)',
            'comment' => 'Хеш пароля'
        ),
        array(
            'field' => 'description',
            'type' => 'text',
            'null' => 'yes',
            'comment' => 'Описание'
        )
    );

    public function group()
    {
        return $this->hasOne(__NAMESPACE__ . NS .'Group', 'id', 'group');
    }
}
