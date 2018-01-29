<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @link      http://kitcms.ru
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Copyright (c) Kit.team
 * @copyright (c) 2013 Ivan Shalganov
 */

namespace Classes\Template;

use Fenom;

class Accessor {
    public static $vars = array(
        'get'     => '$_GET',
        'post'    => '$_POST',
        'session' => '$_SESSION',
        'cookie'  => '$_COOKIE',
        'request' => '$_REQUEST',
        'files'   => '$_FILES',
        'globals' => '$GLOBALS',
        'server'  => '$_SERVER',
        'env'     => '$_ENV'
    );
    
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
