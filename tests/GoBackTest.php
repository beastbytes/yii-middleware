<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Middleware\Tests;

use BeastBytes\Yii\Middleware\GoBack;
use BeastBytes\Yii\Middleware\Tests\Support\TestCase;
use HttpSoft\Message\Uri;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Session\SessionInterface;

class GoBackTest extends TestCase
{
    private const IGNORED_ROUTE = 'ignoredRoute';
    private const NOT_IGNORED_ROUTE = 'notIgnoredRoute';
    private const NOT_IGNORED_URI = 'https://example.com/not-ignored';

    private array $session = [];

    public function testRouteIsIgnoredRoute(): void
    {
        $currentRoute = $this
            ->createCurrentRoute(self::IGNORED_ROUTE)
        ;
        $session = $this
            ->createSession()
        ;

        $goBack = new GoBack($currentRoute, $session);
        $goBack = $goBack->withIgnoredRoute(self::IGNORED_ROUTE);

        $goBack->process($this->createRequest(), $this->createHandler());

        $this->assertEmpty($session->get(GoBack::URL_PARAM));
    }

    public function testRouteIsNotIgnoredRoute(): void
    {
        $currentRoute = $this
            ->createCurrentRoute(self::NOT_IGNORED_ROUTE, self::NOT_IGNORED_URI)
        ;
        $session = $this
            ->createSession()
        ;

        $goBack = new GoBack($currentRoute, $session);
        $goBack = $goBack->withIgnoredRoute(self::IGNORED_ROUTE);

        $goBack->process($this->createRequest(), $this->createHandler());

        $this->assertNotEmpty($session->get(GoBack::URL_PARAM));
        $this->assertSame(self::NOT_IGNORED_URI, $session->get(GoBack::URL_PARAM));
    }

    private function createCurrentRoute(string $route, string $uri = ''): CurrentRoute
    {
        $currentRoute = $this->createMock(CurrentRoute::class);
        $currentRoute
            ->method('getName')
            ->willReturn($route);
        $currentRoute
            ->method('getUri')
            ->willReturn(new Uri($uri));

        return $currentRoute;
    }

    private function createSession(): SessionInterface
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('set')
            ->willReturnCallback(function ($name, $value) {
                $this->session[$name] = $value;
            })
        ;

        $session
            ->method('get')
            ->willReturnCallback(fn ($name) => $this->session[$name])
        ;

        return $session;
    }
}
