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

class Section extends Model
{
    protected $_table = 'Section';

    public function site()
    {
        return $this->hasOne(__NAMESPACE__ . NS .'Site', 'id', 'site');
    }

    public function parent()
    {
        return $this->hasOne(__CLASS__, 'id', 'parent');
    }

    public function parents()
    {
        $paths = explode('/', $this->path);
        array_pop($paths);
        $where = array(array_shift($paths));
        foreach ($paths as $path) {
            array_push($where, end($where) .'/'. $path);
        }
        return $this->hasMany(__CLASS__, 'site', 'site')->whereIn('path', $where);
    }

    public function children()
    {
        return $this->hasMany(__CLASS__, 'parent', 'id');
    }

    public function childrens()
    {
        return $this->hasMany(__CLASS__, 'site', 'site')->whereNotIn('id', (array) $this->id)->whereLike('path', $this->path .'%');
    }
}
