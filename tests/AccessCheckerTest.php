<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Middleware\Tests;

use BeastBytes\Yii\Middleware\AccessChecker;
use BeastBytes\Yii\Middleware\Tests\Support\TestCase;
use HttpSoft\Message\ResponseFactory;
use RuntimeException;
use Yiisoft\Http\Status;
use Yiisoft\User\CurrentUser;

class AccessCheckerTest extends TestCase
{
    private const PERMISSION_ALLOWED = 'allowed';
    private const PERMISSION_DENIED = 'denied';

    public function testPermissionAllowed(): void
    {
        $accessChecker = new AccessChecker($this->createCurrentUser(), new ResponseFactory());
        $accessChecker = $accessChecker->withPermission(self::PERMISSION_ALLOWED);

        $response = $accessChecker->process($this->createRequest(), $this->createHandler());

        $this->assertSame(Status::OK, $response->getStatusCode());
    }

    public function testPermissionDenied(): void
    {
        $accessChecker = new AccessChecker($this->createCurrentUser(), new ResponseFactory());
        $accessChecker = $accessChecker->withPermission(self::PERMISSION_DENIED);

        $response = $accessChecker->process($this->createRequest(), $this->createHandler());

        $this->assertSame(Status::FORBIDDEN, $response->getStatusCode());
    }

    public function testPermissionNotSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AccessChecker::PERMISSION_NOT_SET_EXCEPTION);
        $accessChecker = new AccessChecker($this->createCurrentUser(), new ResponseFactory());
        $accessChecker->process($this->createRequest(), $this->createHandler());
    }

    private function createCurrentUser(): CurrentUser
    {
        $currentUser = $this->createMock(CurrentUser::class);

        $currentUser
            ->method('can')
            ->willReturnCallback(function (string $permission) {
                return $permission === self::PERMISSION_ALLOWED;
            })
        ;

        return $currentUser;
    }
}
