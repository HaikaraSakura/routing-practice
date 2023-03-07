# ルーティングとHTTPリクエスト/レスポンスの取り扱い

ルーティングと、HTTPリクエスト/レスポンスを取り扱うライブラリについて。  

Composerの利用を前提とする。ローカルに環境がない場合は、  
OCIサーバ上にComposerが入っているので、そちらを利用のこと。  
MAMPは.htaccessでのリライトの挙動がおかしかったりするので非推奨。

## 構成

- [league/route](https://route.thephpleague.com/5.x/)  
  The PHP Leagueがメンテナンスしているルーティングライブラリ。PSR-7の実装を要求する。  
  別の軽量なルーティングライブラリである[nikic/FastRoute](https://github.com/nikic/FastRoute)を拡張したもの。
- [laminas/diactor](https://docs.laminas.dev/laminas-diactoros/)  
  Zend Frameworkの後継であるLaminasプロジェクトのPSR−7実装ライブラリ。  
- [laminas/laminas-httphandlerrunner](https://docs.laminas.dev/laminas-httphandlerrunner/)  
  適切なHTTPレスポンスを生成する。

## プロジェクトの作成とインストール

任意のプロジェクトディレクトリを作成。ここでは`routing_practice`というディレクトリを作成したものとする。  
以下のコマンドを実行し、Composerでのプロジェクト管理をセットアップする。

```bash
cd routing_practice
composer init
```

対話を求められるが、基本はデフォルトでいいので`Enter`を押下。
以下の項目は返答する。

- Author  
  `n`を入力でスキップ
- Package Type  
  `project`を入力。`Enter`押下でも問題はない。

プロジェクトディレクトリ直下にcomposer.jsonが作成されるので、念のため中身を確認しておく。  
以下のようになっていれば成功。

```json
{
    "name": "ubuntu/routing_practice",
    "type": "project",
    "autoload": {
        "psr-4": {
            "Ubuntu\\RoutingPractice\\": "src/"
        }
    },
    "require": {}
}
```

`Ubuntu\\RoutingPractice\\`の部分は`App\\`に変更しておく。

続けて、`composer require`コマンドで必要なパッケージ群をインストールしていく。  
一括でもインストールできるが、今回はひとつずつ入れていく。

```bash
composer require league/route
```

```bash
composer require laminas/laminas-diactoros
```

```bash
composer require laminas/laminas-httphandlerrunner
```

`vendor`ディレクトリが作成され、インストールしたパッケージが入るので確認しておく。

### ライブラリの依存関係について

league/routeをインストール時、依存関係が色々ごそっと入るが、  
ベンダー名が`psr`となっているものはPHP-FIGが定義したPSRのインターフェイスであり、  
それ自体が実装を伴うものではないので問題ない。  

以下がleague/routeの依存ライブラリとなる。

- nikic/fast-route  
  軽量なルーティングライブラリ。Slimもこれに依存している。  
  nikic（Nikita Popov）はPHPのコア開発者だった人物で、昨今のPHPが小うるさい感じに進化している原因。  
  近頃はRustに興味があるらしく、PHP8.1のリリースと同時にコア開発からは離脱したが、  
  それ以降にもRFCを提出しているのが確認できるので、精力的なのは相変わらずの模様。  

- opis/closure  
  これが一番謎。
  > Opis Closureは、すべてのクロージャーをシリアル化できるラッパーを提供することにより、クロージャーのシリアル化に関するPHPの制限を克服することを目的としたライブラリです。

  PHPではClosureをserialize()しようとするとエラーになるが、それをどうにかしているらしい。  
  ルーティングに必要な大量のClosureをシリアル化することで、実行速度の低下を防ぐものと思われる。  
  `opis`は他にも基本的な複数のライブラリを長らくメンテナンスしている開発者団体。

## ルーティングの記述


プロジェクトディレクトリ直下にpublicディレクトリを作成。  
プロジェクトディレクトリ直下に`.htaccess`を作成、以下を記述する。

```txt
RewriteEngine On
RewriteCond %{REQUEST_URI} !(public/)
RewriteRule ^ index.php [QSA,L]
```

プロジェクトディレクトリ直下に`index.php`を作成、以下を記述する。

```PHP
<?php

declare(strict_types=1);

ini_set('display_errors', 'On');
error_reporting(E_ALL);
```

開発段階なのですべてのエラーを出す。  
declare(strict_types=1)は、これがないと関数などの型が曖昧になって挙動がおかしくなるので、  
今回のハンズオンに限らず、あらゆる場面において開発/本番を問わず必ず記述する。

続けて`index.php`に以下を追記し、ブラウザで動作を確認する（下記URLは例）。  
[http://192.168.64.8/routing_practice/?id=1](http://192.168.64.8/routing_practice/?id=1)  

```PHP
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;

// 任意のディレクトリ名に変更すること
const BASE_ROUTE = '/knp/routing_practice';

require_once '../vendor/autoload.php';

// Requestオブジェクトを生成
$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();

$router = new \League\Route\Router();

// ルーティングを登録
$router->map('GET', BASE_ROUTE . '/', function (ServerRequestInterface $request): ResponseInterface {
    // URLパラメータからidの値を取得
    $query_params = $request->getQueryParams();
    $id = filter_var($query_params['id'] ?? null, FILTER_VALIDATE_INT);

    // Responseオブジェクトを生成
    $response = new \Laminas\Diactoros\Response;
    
    // レスポンスボディに書き込み
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
```

全体としては以下の流れになっている。

```txt
Request生成 -> ルーティング登録 -> Response生成 -> HTTPレスポンス返却
```

### Router::mapメソッドの引数

- 第1引数  
  GET、POSTなどのHTTPメソッドを指定する。  
- 第2引数  
  ルーティングパターン。どのようなURLでリクエストがあったときに処理をおこなうのかを指定する。  
- 第3引数  
  ルーティングコールバック。実行したい処理を持つCallableな値を渡す。例ではクロージャを渡している。  
  戻り値として必ずResponseオブジェクトを返さなければならない。  

## コールバックの切り出し

ひとつのファイル内に処理を書いていくと、ルートが増えるにつれて肥大化していくので、  
ルーティングコールバックはAction（Controller）クラスとして、別ファイルに切り出すようにする。

`composer init`時に作成された`src`ディレクトリ内に、`IndexAction.php`を作成。以下を記述する。

```PHP
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

        // レスポンスボディに書き込み
        $this->response->getBody()->write(<<< HTML
            <h1>Index</h1>
            <p>ID:{$id}</p>
        HTML);

        // Responseオブジェクトを返却
        return $this->response;
    }
}
```

ルーティング登録部分を以下のように変更

```PHP
$router = new Router();

// Responseオブジェクトを生成
$response = new \Laminas\Diactoros\Response;

// コールバックとしてIndexActionのインスタンスを渡す
$router->map('GET', BASE_ROUTE . '/', (new \App\IndexAction($response)))->setName('Index');
```

先に生成したResponseオブジェクトを、IndexActionのコンストラクタに注入してインスタンス化。  
そのIndexActionのオブジェクトをルーティングコールバックとして渡すように変更した。  
IndexActionはクラスだが、関数呼び出しがおこなわれると__invokeメソッドが呼ばれるので、変更前と同じように処理が実行される。

---

ルーティングの基本は以上。  
実際はさらにラップや切り分けをおこない、MVCやADRなどのパターンを構築していく。

今回の構成は、マイクロフレームワークのSlimと使用感はほとんど変わらないものになっている。  
Slimのほうが複雑な手続きをラップしてくれている部分が多く、やや手短に書けたり便利な印象はあるが、  
どちらもPSRに乗っかっているだけあって、使い勝手は非常に近い。  
