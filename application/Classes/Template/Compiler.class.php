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
use Fenom\Tokenizer;
use Fenom\Error\CompileException;
use Fenom\Error\InvalidUsageException;
use Fenom\Error\UnexpectedTokenException;

class Compiler extends Fenom\Compiler
{
    /**
     * @param Tokenizer $tokens
     * @param Tag $scope
     * @return string
     */
    public static function setOpen(Fenom\Tokenizer $tokens, Fenom\Tag $scope)
    {
        if($tokens->is(T_VARIABLE)) {
            $var = $scope->tpl->parseVariable($tokens);
        } elseif ($scope->name == 'var') {
            // FIXME
            $curr = $tokens->curr;
            $p = $tokens->p;
            $name = $tokens->next()->skipIf('.')->getAndNext();
            if (!isset(Accessor::$vars[$name])) {
                $tokens->curr = $curr;
                $tokens->p = $p;
                $var = $scope->tpl->parseAccessor($tokens, $is_var);
            } else {
                $var = $scope->tpl->parseVariable($tokens, Accessor::$vars[$name]);
            }
        } elseif($tokens->is('$')) {
            $var = $scope->tpl->parseAccessor($tokens, $is_var);
            if(!$is_var) {
                throw new InvalidUsageException("Accessor is not writable");
            }
        } else {
            throw new InvalidUsageException("{set} and {add} accept only variable");
        }
        $before = $after = "";
        if($scope->name == 'add') {
            $before = "if(!isset($var)) {\n";
            $after = "\n}";
        }
        if ($tokens->is(Tokenizer::MACRO_EQUALS, '[')) { // inline tag {var ...}
            $equal = $tokens->getAndNext();
            if($equal == '[') {
                $tokens->need(']')->next()->need('=')->next();
                $equal = '[]=';
            }
            $scope->close();
            if ($tokens->is("[")) {
                return $before.$var . $equal . $scope->tpl->parseArray($tokens) . ';'.$after;
            } else {
                return $before.$var . $equal . $scope->tpl->parseExpr($tokens) . ';'.$after;
            }
        } else {
            $scope["name"] = $var;
            if ($tokens->is('|')) {
                $scope["value"] = $before . $scope->tpl->parseModifier($tokens, "ob_get_clean()").';'.$after;
            } else {
                $scope["value"] = $before . "ob_get_clean();" . $after;
            }
            return 'ob_start();';
        }
    }

    /**
     * @param Tokenizer $tokens
     * @param Tag $scope
     * @return string
     */
    public static function setClose($tokens, Fenom\Tag $scope)
    {
        return $scope["name"] . '=' . $scope["value"] . ';';
    }
}
