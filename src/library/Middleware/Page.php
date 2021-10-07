<?php

declare(strict_types=1);

namespace App\Ebcms\Page\Middleware;

use Ebcms\App;
use Ebcms\ResponseFactory;
use Ebcms\Template;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Page implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        if ($response->getStatusCode() == 404) {
            if (isset($request->getServerParams()['REQUEST_URI'])) {

                $page = (function () {
                    $tmp_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                    $url_path = implode('/', array_filter(explode('/', $tmp_path)));
                    $url_path = $url_path ? '/' . $url_path : '';
                    $script_name = '/' . implode('/', array_filter(explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_NAME'])));
                    if ($url_path === $script_name) {
                        $path = $url_path . '/';
                    } else
                    if (substr($tmp_path, -1) === '/') {
                        $path = $url_path . '/';
                    } else {
                        $path = $url_path;
                    }
                    if (substr($path, -1) == '/') {
                        $path .= 'index';
                    }
                    $script_name = '/' . implode('/', array_filter(explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_NAME'])));
                    if (strpos($path, $script_name) === 0) {
                        $prefix = $script_name;
                    } else {
                        $prefix = strlen(dirname($script_name)) > 1 ? dirname($script_name) : '';
                    }
                    return substr($path, strlen($prefix));
                })();
                $page = urldecode($page);
                if (strpos($page, '@') === false && strpos($page, '..') === false) {
                    if ($res = App::getInstance()->execute(function (
                        ResponseFactory $responseFactory,
                        Template $template
                    ) use ($page) {
                        $tpl = '/page/' . $page . '@ebcms/page';
                        if ($template->getTplFile($tpl)) {
                            return $responseFactory->createGeneralResponse(200, [], $template->renderFromFile($tpl));
                        }
                    })) {
                        return $res;
                    }
                }
            }
        }
        return $response;
    }
}
