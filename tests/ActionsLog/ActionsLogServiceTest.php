<?php

declare(strict_types=1);

namespace ZxArt\Tests\ActionsLog;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use PHPUnit\Framework\TestCase;
use zxProdElement;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\ActionsLog\Dto\ActionsLogRecordDto;
use ZxArt\ActionsLog\Repositories\ActionsLogRepository;
use ZxArt\Shared\StructureType;

final class ActionsLogServiceTest extends TestCase
{
    public function testRecordsActionOfCurrentUserOnElement(): void
    {
        $element = $this->createStub(zxProdElement::class);
        $element->method('getId')->willReturn(42);
        $element->method('getStructureName')->willReturn('exolon');
        $user = self::getStubBuilder(CurrentUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__destruct', 'writeStorage'])
            ->getStub();
        $user->id = 7;
        $user->userName = 'moroz';
        $user->IP = '10.0.0.1';
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user);

        $recorded = null;
        $repository = $this->createMock(ActionsLogRepository::class);
        $repository->expects($this->once())
            ->method('add')
            ->willReturnCallback(function (ActionsLogRecordDto $record) use (&$recorded): void {
                $recorded = $record;
            });

        $before = time();
        (new ActionsLogService($repository, $currentUserService))->log($element, StructureType::ZxProd, 'publicDelete');

        self::assertInstanceOf(ActionsLogRecordDto::class, $recorded);
        self::assertSame(42, $recorded->elementId);
        self::assertSame('zxProd', $recorded->elementType);
        self::assertSame('exolon', $recorded->elementName);
        self::assertSame('publicDelete', $recorded->action);
        self::assertSame(7, $recorded->userId);
        self::assertSame('moroz', $recorded->userName);
        self::assertSame('10.0.0.1', $recorded->userIp);
        self::assertGreaterThanOrEqual($before, $recorded->date);
        self::assertLessThanOrEqual(time(), $recorded->date);
    }
}
