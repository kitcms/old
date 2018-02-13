<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\Template;

use Fenom;

class Template extends Fenom\Template
{
    /**
     * Parse call-chunks: $var->func()->func()->prop->func()->...
     * @param Tokenizer $tokens
     * @param string $code start point (it is $var)
     * @return string
     */
    public function parseChain(Fenom\Tokenizer $tokens, $code)
    {
        do {
            if ($tokens->is('(')) {
                $code .= $this->parseArgs($tokens);
            }
            if ($tokens->is(T_OBJECT_OPERATOR) && $tokens->isNext(T_STRING)) {
                $code .= '->' . $tokens->next()->getAndNext();
            }
            if ($tokens->is('.')) {
                $code = $this->parseVariable($tokens, $code);
            }
        } while ($tokens->is('(', T_OBJECT_OPERATOR));
        return $code;
    }
}
