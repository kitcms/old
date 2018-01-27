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

class Site extends Model
{
    protected $_table = 'Site';

    protected $fields = array(
        array(
            'field' => 'host',
            'type' => 'varchar(255)',
            'key' => 'uni',
            'comment' => 'Сайт'
        ),
        array(
            'field' => 'alias',
            'type' => 'varchar(255)',
            'null' => 'yes',
            'comment' => 'Зеркала сайта'
        ),
        array(
            'field' => 'template',
            'type' => 'int(10) unsigned',
            'null' => 'yes',
            'key' => 'mul',
            'comment' => 'Идентификатор макета дизайна'
        ),
        array(
            'field' => 'dashboard',
            'type' => 'varchar(255)',
            'default' => 'admin',
            'comment' => 'Адрес административной части сайта'
        ),
        array(
            'field' => 'title',
            'type' => 'varchar(255)',
            'comment' => 'Название сайта'
        ),
        array(
            'field' => 'description',
            'type' => 'text',
            'null' => 'yes',
            'comment' => 'Описание'
        ),
        array(
            'field' => 'meta',
            'type' => 'longtext',
            'null' => 'yes',
            'comment' => 'Метаинформация'
        )
    );

    public function section()
    {
        return $this->hasMany(__NAMESPACE__ . NS .'Section', 'site');
    }
}
