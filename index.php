<?php

declare(strict_types=1);

ini_set('display_errors', 'On');
error_reporting(E_ALL);

include './vendor/autoload.php';

use \League\Route\Router;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;

// 任意のディレクトリ名に変更すること
const BASE_ROUTE = '/routing_practice';

// Requestオブジェクトを生成
$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();

$router = new Router();

// ルーティングを登録
$router->map('GET', BASE_ROUTE . '/', function (ServerRequestInterface $request): ResponseInterface {
    // URLパラメータからidの値を取得
    $query_params = $request->getQueryParams();
    $id = filter_var($query_params['id'] ?? null, FILTER_VALIDATE_INT);

    // Responseオブジェクトを生成
    $response = new \Laminas\Diactoros\Response;
    $response->getBody()->write(<<< HTML
        <h1>Index</h1>
        <p>ID:{$id}</p>
    HTML);

    // Responseオブジェクトを返却
    return $response;
})->setName('Index');

// Responseオブジェクトを生成
$response = $router->dispatch($request);

// ResponseオブジェクトからHTTPレスポンスを出力
(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
