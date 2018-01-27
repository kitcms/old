<?php
/*
 * Базовый класс системы
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes;

class Application
{
    const VERSION  = '0.1.0';
    const CODENAME = 'Black whale';

    public function run()
    {
        $views = new Template\Engine(new Template\Provider('Views'));
        $views->setCompileDir('Storages/Compile');

        $request = TransferProtocol\HyperText\Request::fromGlobals();
        $model = new Database\Model();

        require 'Components/Dashboard/bootstrap.php';
    }
}
