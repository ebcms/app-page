<?php

use App\Ebcms\Page\Middleware\Page;
use Ebcms\App;
use Ebcms\RequestHandler;

App::getInstance()->execute(function (
    RequestHandler $requestHandler
) {
    $requestHandler->lazyPrependMiddleware(Page::class);
});
