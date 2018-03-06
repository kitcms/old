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

    protected $_accessors = array(
        'get'     => 'Fenom\Accessor::getVar',
        'env'     => 'Fenom\Accessor::getVar',
        'post'    => 'Fenom\Accessor::getVar',
        'request' => 'Fenom\Accessor::getVar',
        'cookie'  => 'Fenom\Accessor::getVar',
        'globals' => 'Fenom\Accessor::getVar',
        'server'  => 'Fenom\Accessor::getVar',
        'session' => 'Fenom\Accessor::getVar',
        'files'   => 'Fenom\Accessor::getVar',
        'tpl'     => 'Fenom\Accessor::tpl',
        'version' => 'Fenom\Accessor::version',
        'const'   => 'Fenom\Accessor::constant',
        'php'     => 'Fenom\Accessor::call',
        'call'    => 'Fenom\Accessor::call',
        'tag'     => 'Fenom\Accessor::Tag',
        'fetch'   => 'Fenom\Accessor::fetch',
        'block'   => 'Fenom\Accessor::block',
        'compile'   => 'Classes\Template\Accessor::compile'
    );

    const ACCESSOR_CHAIN = 'Classes\Template\Accessor::parserChain';

    /**
     * Return empty template
     *
     * @return Fenom\Template
     */
    public function getRawTemplate(Fenom\Template $parent = null)
    {
        return new Template($this, $this->_options, $parent);
    }

    /**
     * Get template by name
     *
     * @param string $template template name with schema
     * @param int $options additional options and flags
     * @return Fenom\Template
     */
    public function getTemplate($template, $options = 0)
    {
        $options |= $this->_options;
        if (is_array($template)) {
            $key = $options . "@" . implode(",", $template);
        } else {
            $key = $options . "@" . $template;
        }
        if (isset($this->_storage[$key])) {
            /** @var Fenom\Template $tpl */
            $tpl = $this->_storage[$key];
            if (($this->_options & self::AUTO_RELOAD) && !$tpl->isValid()) {
                return $this->_storage[$key] = $this->compile($template, true, $options);
            } else {
                return $tpl;
            }
        } elseif ($this->_options & (self::FORCE_COMPILE |  self::DISABLE_CACHE)) {
            return $this->compile($template, !($this->_options & self::DISABLE_CACHE), $options);
        } else {
            return $this->_storage[$key] = $this->_load($template, $options);
        }
    }
}
