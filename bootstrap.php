<?php
use SocymSlim\MVC\exceptions\CustomErrorRenderer;

$DEV_MODE = $_ENV['DEV_MODE'];
$displayErrorDetails = !!$DEV_MODE;

# ミドルウェアの登録
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);

$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->registerErrorRenderer('text/html', CustomErrorRenderer::class);
