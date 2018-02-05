<?php
/*
 * Системный шаблонизатор
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\Template;

use Fenom;

class Engine extends Fenom
{
    use Fenom\StorageTrait;

    const ACCESSOR_CHAIN = 'Classes\Template\Accessor::parserChain';
}
