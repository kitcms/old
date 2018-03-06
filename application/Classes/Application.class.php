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
    const VERSION  = '0.3.0';
    const CODENAME = 'Black whale';

    public function run()
    {
        $request = TransferProtocol\HyperText\Request::fromGlobals();
        $path = trim($request->getPath(), '/');

        $model = new Database\Model();

        $views = new Template\Engine(new Template\Provider('Views'));
        $views->setOptions(array('auto_reload' => true, 'force_include' => false));

        $views->addProvider("template", new Template\Provider('Views/Template'));
        $views->addProvider("section", new Template\Provider('Views/Section'));

        $views->setCompileDir('Storages/Compile');

        $views->addBlockFunction('var', '', 'Classes\Template\Compiler::setOpen', 'Classes\Template\Compiler::setClose');

        $views->addCompiler('exit', function() { return 'return;'; });
        $views->addBlockCompiler('php', 'Classes\Template\Compiler::nope', function ($tokens, $tag) {
            return $tag->cutContent();
        });

        $views->addAccessorSmart("codename", "'". self::CODENAME ."'");
        $views->addAccessorSmart("version", "'". self::VERSION ."'");
        $views->addAccessorSmart("model", "(new Classes\Database\Model())");
        $views->addAccessorSmart("schema", "(new Classes\Database\Schema())");
        $views->addAccessorSmart("mailer", "(new PHPMailer\PHPMailer\PHPMailer())");
        $views->addAccessorSmart("root", "'". $request->getBasePath() ."'");
        $views->addAccessorSmart("site", "site", Template\Engine::ACCESSOR_CHAIN);
        $views->addAccessorSmart("section", "section", Template\Engine::ACCESSOR_CHAIN);
        $views->addAccessorSmart("infobox", "infobox", Template\Engine::ACCESSOR_CHAIN);
        $views->addAccessorSmart("parents", "parents", Template\Engine::ACCESSOR_CHAIN);
        $views->addAccessorSmart("user", "user", Template\Engine::ACCESSOR_CHAIN);
        $views->addAccessorSmart("meta", "meta", Template\Engine::ACCESSOR_CHAIN);

        // Определение текущего пользователя
        $views->user = $model->factory('User')->findOne((isset($_SESSION['user']) ? $_SESSION['user'] : 0));

        // Определение текущего сайта
        $instance = $model->factory('Site')->whereHostIn(array($request->getHost()));
        if (false === ($views->site = $instance->findOne()) ||
            !isset($views->site->config['dashboard']) ||
            preg_match('/^'. $views->site->config['dashboard'] .'\\//', $path .'/')) {
            // Запрос к компоненту администрирования
            require 'Components/Dashboard/bootstrap.php';
        } else {
            // Определение текущего раздела
            $instance = $model->factory('Section')->where('site', (string) $views->site)
                ->whereAnyIs(array(
                    array('path' => trim($path .'/'. $request->getBaseName(), '/')),
                    array('path' => $path),
                    array('type' => intval(empty($path)))
                ))->orderByAsc('type');
            if ($views->section = $instance->findOne()) {
                $container = new Storage\Container();
                // Определение родительских разделов
                $views->parents = $views->section->parents()->orderByAsc('path')->findMany();

                // Определение используемых инфобоксов
                if (false === ($views->infobox = $views->section->infobox())) {
                    if (false !== ($parent = $views->section->parents()->whereNotNull('model')->orderByDesc('path')->findOne())) {
                        $views->infobox = $parent->infobox();
                    }
                }

                // Определение дополнительных параметров
                if ($views->infobox && !$views->section->extension) {
                    if (!$pattern = $views->section->infobox['pattern']) {
                        // /(?J)(?P<name>\w+)[_-](?P<digit>\w+)/
                        $pattern = '(?J)(?P<id>\d+)|\w+[_-](?P<offset>\d+)';
                    }
                    preg_match('/'. $pattern .'/', $request->getFileName(), $matches);
                    $matches = array_diff($matches, array(''));
                    array_shift($matches);
                    if (isset($matches['id'])) {
                        $views->infobox->where('id', $matches['id']);
                    }
                    if (isset($matches['offset'])) {
                        $views->infobox->offset((int) $matches['offset']);
                    }
                    $_ENV = array_replace($_ENV, array_unique($matches));
                }

                // Определение идентификатора макета дизайна
                if (false !== $views->section && (null !== ($template = $views->section->template))) {
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

                // Формирование цепочки зависимых макетов
                if (false !== $template = $model->factory('Template')->where('id', $template)->findOne()) {
                    $container->set('templates', "template:{$template}.tpl");
                    $parents = $template->parents()->orderByDesc('path')->findMany();
                    foreach ($parents as $parent) {
                        $container->append('templates', "template:{$parent}.tpl");
                    }
                    $container->prepend('templates', "section:{$views->section->id}.tpl");
                } else {
                    $container->set('templates', "section:{$views->section->id}.tpl");
                }

                // Формирование метаинформации
                $views->meta = array('title' => '', 'description' => '', 'keywords' => '');
                $dependences = array_merge(array($views->site), (array) $views->parents, array($views->section));
                foreach ($dependences as $dependence) {
                    foreach ((array) $dependence->meta as $key => $value) {
                        if (!empty($value)) {
                            $views->meta[$key] = implode(',', (array) $value);
                        }
                    }
                }

                // Отображение макетов
                $views->display($container->get('templates'));
            }
        }
    }
}
