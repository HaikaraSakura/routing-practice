<?php

declare(strict_types=1);

ini_set('display_errors', 'On');
error_reporting(E_ALL);

include './vendor/autoload.php';

// 任意のディレクトリ名に変更すること
const BASE_ROUTE = '/routing_practice';

// Requestオブジェクトを生成
$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();

$router = new \League\Route\Router();

// ルーティングを登録
$router->map('GET', BASE_ROUTE . '/', new \App\IndexAction($response))->setName('IndexAction');

$router->map('GET', BASE_ROUTE . '/products[/]', new \App\IndexAction($response))->setName('ProductsAction');

// URL末尾にスラッシュがあったら、スラッシュのないURLにリダイレクト
$router->middleware($container->get(\App\RemoveLastSlash::class));

// Responseオブジェクトを生成
$response = $router->dispatch($request);

// ResponseオブジェクトからHTTPレスポンスを出力
(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
