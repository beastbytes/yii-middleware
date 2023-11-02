<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Yiisoft\Http\Status;
use Yiisoft\User\CurrentUser;

final class AccessChecker implements MiddlewareInterface
{
    private array $parameters = [];
    private ?string $permission = null;

    public function __construct(
        private CurrentUser $currentUser,
        private ResponseFactoryInterface $responseFactory
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->permission === null) {
            throw new RuntimeException('Permission not set.');
        }

        if (
            $this
                ->currentUser
                ->can($this->permission, $this->parameters)
        ) {
            return $handler->handle($request);
        }

        return $this
            ->responseFactory
            ->createResponse(Status::FORBIDDEN)
        ;
    }

    public function withParameters(array $parameters): self
    {
        $new = clone $this;
        $new->parameters = $parameters;
        return $new;
    }

    public function withPermission(string $permission): self
    {
        $new = clone $this;
        $new->permission = $permission;
        return $new;
    }
}
