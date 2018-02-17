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
            'title' => 'Идентификатор группы',
            'field' => 'group',
            'type' => 'bigint(20) unsigned',
            'null' => 'yes',
            'key' => 'mul',
            'group' => 'permission'
        ),
        array(
            'title' => 'Логин',
            'field' => 'login',
            'type' => 'varchar(255)',
            'key' => 'uni',
            'group' => 'main'
        ),
        array(
            'title' => 'Имя пользователя',
            'field' => 'name',
            'type' => 'varchar(255)',
            'group' => 'main'
        ),
        array(
            'title' => 'Электронный адрес',
            'field' => 'email',
            'type' => 'varchar(255)',
            'key' => 'uni',
            'group' => 'main'
        ),
        array(
            'title' => 'Хеш пароля',
            'field' => 'password',
            'type' => 'varchar(255)',
            'group' => 'main'
        ),
        array(
            'title' => 'Описание',
            'field' => 'description',
            'type' => 'text',
            'null' => 'yes',
            'group' => 'main'
        )
    );

    public function group()
    {
        return $this->hasOne(__NAMESPACE__ . NS .'Group', 'id', 'group');
    }

    public function save()
    {
        // Проверка логина на корректность
        if (!preg_match("/^[[:alnum:]-_.]+$/iu", $this->login) || preg_match("/^[\d-_.]+$/", $this->login)) {
            return false;
        }
        // Проверка уникальности логина и email
        $clause = '(`login` LIKE ? OR `email` LIKE ?)';
        $parameters = array($this->get('login'), $this->get('email'));
        $instance = $this->factory(__CLASS__)->whereRaw($clause, $parameters);
        if (false === $this->isNew()) {
            $instance->whereNotIn('id', (array) $this->get('id'));
        }
        if ($instance->findOne()) {
            return false;
        }
        // Шифрование пароля
        if ($this->isDirty('password') && ($password = $this->get('password'))) {
            $this->set('password', password_hash($password, PASSWORD_DEFAULT));
        } else {
            $this->set('password', $this->password);
        }
        return parent::save();
    }

    protected function _create_model_instance($orm) {
        $data = $this->asArray();
        if (isset($data['password'])) {
            $this->password = $data['password'];
        }
        $this->orm->hydrate($data);
    }
}
