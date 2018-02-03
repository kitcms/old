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
        $request = TransferProtocol\HyperText\Request::fromGlobals();
        $model = new Database\Model();

        $views = new Template\Engine(new Template\Provider('Views'));
        $views->setCompileDir('Storages/Compile');

        $views->addBlockFunction('var', '', 'Classes\Template\Compiler::setOpen', 'Classes\Template\Compiler::setClose');

        $views->addCompiler('exit', function() { return 'return;'; });
        $views->addBlockCompiler('php', 'Classes\Template\Compiler::nope', function ($tokens, $tag) {
            return $tag->cutContent();
        });

        $views->addAccessorSmart("model", "(new Classes\Database\Model())");
        $views->addAccessorSmart("schema", "(new Classes\Database\Schema())");
        $views->addAccessorSmart("root", "'". $request->getBasePath() ."'");

        $views->addAccessorSmart("user", "user", Template\Engine::ACCESSOR_CHAIN);
        $views->user = $model->factory('User')->findOne((isset($_SESSION['user']) ? $_SESSION['user'] : 0));

        require 'Components/Dashboard/bootstrap.php';
    }
}
