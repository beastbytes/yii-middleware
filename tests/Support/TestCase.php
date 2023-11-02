<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Middleware\Tests\Support;

use DG\BypassFinals;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Method;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    protected function createRequest(): ServerRequestInterface {
        return new ServerRequest(
            cookieParams: [],
            queryParams: [],
            method: Method::GET,
            uri: '/',
            headers: [],
        );
    }

    protected function createHandler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response
            {
                return new Response();
            }
        };
    }
}
