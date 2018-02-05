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
        $path = trim($request->getPath(), '/');

        $model = new Database\Model();

        $views = new Template\Engine(new Template\Provider('Views'));

        $views->addProvider("template", new Template\Provider('Views/Template'));
        $views->addProvider("section", new Template\Provider('Views/Section'));

        $views->setCompileDir('Storages/Compile');

        $views->addBlockFunction('var', '', 'Classes\Template\Compiler::setOpen', 'Classes\Template\Compiler::setClose');

        $views->addCompiler('exit', function() { return 'return;'; });
        $views->addBlockCompiler('php', 'Classes\Template\Compiler::nope', function ($tokens, $tag) {
            return $tag->cutContent();
        });

        $views->addAccessorSmart("model", "(new Classes\Database\Model())");
        $views->addAccessorSmart("schema", "(new Classes\Database\Schema())");
        $views->addAccessorSmart("root", "'". $request->getBasePath() ."'");

        // Определение текущего пользователя
        $views->addAccessorSmart("user", "user", Template\Engine::ACCESSOR_CHAIN);
        $views->user = $model->factory('User')->findOne((isset($_SESSION['user']) ? $_SESSION['user'] : 0));

        // Определение текущего сайта
        $views->addAccessorSmart("site", "site", Template\Engine::ACCESSOR_CHAIN);
        $instance = $model->factory('Site')->whereHostIn(array($request->getHost()));
        if (false === ($views->site = $instance->findOne()) || preg_match('/^'. $views->site->dashboard .'\\//', $path .'/')) {
            // Запрос к компоненту администрирования
            require 'Components/Dashboard/bootstrap.php';
        } else {
            // Определение текущего раздела
            $views->addAccessorSmart("section", "section", Template\Engine::ACCESSOR_CHAIN);
            $instance = $model->factory('Section')->where('site', (string) $views->site)
                ->whereAnyIs(array(
                    array('path' => trim($path .'/'. $request->getBaseName(), '/')),
                    array('path' => $path),
                    array('type' => intval(empty($path)))
                ))->orderByAsc('type');
            if ($views->section = $instance->findOne()) {
                $container = new Storage\Container();

                if (false !== $views->section && (null !== $template = $views->section->template)) {
                    if (false === filter_var((bool) $template, FILTER_VALIDATE_BOOLEAN)) {
                        $parents = array_reverse($views->parents);
                        array_push($parents, $views->site);
                        foreach ($parents as $parent) {
                            if ((null === $template = $parent->template) || true === filter_var((bool) $template, FILTER_VALIDATE_BOOLEAN)) {
                                break;
                            }
                        }
                    }
                }

                if (false !== $template = $model->factory('Template')->findOne($template)) {
                    $container->set('templates', "template:{$template}.tpl");
                    $parents = $template->parents()->orderByDesc('path')->findMany();
                    foreach ($parents as $parent) {
                        $container->append('templates', "template:{$parent}.tpl");
                    }
                }

                $container->prepend('templates', "section:{$views->section->id}.tpl");

                $views->display($container->get('templates'));
            }
        }
    }
}
