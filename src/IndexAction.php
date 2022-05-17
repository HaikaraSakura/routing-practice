<?php

declare(strict_types=1);

namespace App;

use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;

class IndexAction
{
    /**
     * インスタンス化の際にResponseオブジェクトを受け取る
     *
     * @param ResponseInterface $response
     */
    public function __construct(private readonly ResponseInterface $response)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        // URLパラメータからidの値を取得
        $query_params = $request->getQueryParams();
        $id = filter_var($query_params['id'] ?? null, FILTER_VALIDATE_INT);

        // Responseオブジェクトを生成
        $response = new \Laminas\Diactoros\Response;
        $response->getBody()->write(<<< HTML
            <h1>products</h1>
            <p>ID:{$id}</p>
        HTML);

        // Responseオブジェクトを返却
        return $response;
    }
}
