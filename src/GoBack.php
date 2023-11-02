<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Session\SessionInterface;

class GoBack implements MiddlewareInterface
{
    public const URL_PARAM = '__goBack';

    private array $ignoredRoutes = [];

    public function __construct(
        private CurrentRoute $currentRoute,
        private SessionInterface $session
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array(
            $this
                ->currentRoute
                ->getName(),
            $this->ignoredRoutes,
            true)
        ) {
            $this
                ->session
                ->set(
                    self::URL_PARAM,
                    (string) $this
                        ->currentRoute
                        ->getUri()
                )
            ;
        }

        return $handler->handle($request);
    }

    /**
     * @param string ...$ignoredRoute Route name(s) to ignore
     * @return $this
     */
    public function withIgnoredRoute(string ...$ignoredRoute): self
    {
        $new = clone $this;
        $new->ignoredRoutes = $ignoredRoute;
        return $new;
    }
}
