<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\TransferProtocol\HyperText;

use Classes\Storage\Container;

class Server extends Container
{
    protected $parameters;

    public function __construct($parameters = array())
    {
        parent::__construct($parameters);
    }

    public function getHeaders()
    {
        $headers = array();
        foreach ($this->parameters as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
        }
        return $headers;
    }
}
