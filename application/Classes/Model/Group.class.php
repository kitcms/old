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

class Group extends Model
{
    protected $_table = 'Group';

    protected $fields = array(
        array(
            'field' => 'title',
            'type' => 'varchar(255)',
            'comment' => 'Название группы'
        ),
        array(
            'field' => 'description',
            'type' => 'text',
            'null' => 'yes',
            'comment' => 'Описание'
        )
    );

    public function user()
    {
        return $this->hasMany(__NAMESPACE__ . NS .'User', 'group');
    }
}
