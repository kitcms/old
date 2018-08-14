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
        // Проверка email на корректность
        //if (!preg_match("/^[[:alnum:]-_.]+$/iu", $this->email) || preg_match("/^[\d-_.]+$/", $this->email)) {
        //    return false;
        //}
        // Проверка уникальности email
        $instance = $this->factory(__CLASS__)->where('email', $this->get('email'));
        if (false === $this->isNew()) {
            $instance->whereNotIn('id', (array) $this->get('id'));
        }
        if ($instance->findOne()) {
            return false;
        }
        // Шифрование пароля
        if ($this->isDirty('password') && ($password = $this->get('password'))) {
            $info = password_get_info($password);
            if (false == $info['algo']) {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->set('password', $password);
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
