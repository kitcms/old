<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 * @version   0.1.0
 */

namespace Classes\Template;

use Fenom;

class Accessor {
    /**
     * @param string $chain template's method name
     * @param Tokenizer $tokens
     * @param Template $tpl
     * @return string
     */
    public static function parserChain($chain, Fenom\Tokenizer $tokens, Fenom\Template $tpl)
    {
        return $tpl->parseChain($tokens, '$tpl->getStorage()->'. $chain);
    }
}
