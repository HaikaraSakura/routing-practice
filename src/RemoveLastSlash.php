<?php

declare(strict_types=1);

namespace App;

use \Laminas\Diactoros\Response\RedirectResponse;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;

class RemoveLastSlash implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        // ベースルートへのアクセスではなく、末尾にスラッシュがついていたら
        if ($path !== BASE_ROUTE . '/' && str_ends_with($path, '/')) {
            $uri = $uri->withPath(rtrim($path, '/'));
            $response = ($request->getMethod() === 'GET')
                ? new RedirectResponse((string)$uri)
                : $handler->handle($request->withUri($uri));
        } else {
            $response = $handler->handle($request);
        }

        return $response;
    }
}
