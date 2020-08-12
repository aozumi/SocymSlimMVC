<?php
use SocymSlim\MVC\exceptions\CustomErrorRenderer;

# ミドルウェアの登録
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->registerErrorRenderer('text/html', CustomErrorRenderer::class);
