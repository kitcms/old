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

    public function section()
    {
        return $this->hasMany(__NAMESPACE__ . NS .'Section', 'site');
    }
}
