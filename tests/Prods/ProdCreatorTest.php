<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

require_once __DIR__ . '/../Doubles/Elements/ZxProdElementSpy.php';

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdCreator;
use ZxArt\Prods\Services\ProdFieldsWriter;
use ZxArt\Tests\Doubles\Elements\ZxProdElementSpy;
use ZxArt\ZxProdCategories\CategoryIds;

final class ProdCreatorTest extends TestCase
{
    public function testCreatesTheProdInItsFirstCategoryAndWritesItsFields(): void
    {
        $prod = new ZxProdElementSpy(elementId: 42, createdTimestamp: 1700000000);
        $fields = new ProdFieldsDto(title: 'Dizzy', categoryIds: [3, 4]);
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with('zxProd', 'show', 3)
            ->willReturn($prod);
        $writer = $this->createMock(ProdFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($prod, $fields);

        $created = $this->createCreator($structureManager, $writer)->create($fields);

        self::assertSame($prod, $created);
        self::assertSame(1700000000, $prod->values['dateAdded']);
        self::assertSame(7, $prod->values['userId']);
        self::assertContains('logCreation', $prod->calls);
    }

    public function testProdWithoutCategoriesIsCreatedInMisc(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with('zxProd', 'show', CategoryIds::MISC->value)
            ->willReturn(new ZxProdElementSpy());

        $this->createCreator($structureManager)->create(new ProdFieldsDto(title: 'Dizzy'));
    }

    public function testCreatorReceivesThePrivilegesOverTheNewProd(): void
    {
        $granted = [];
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('setPrivilege')->willReturnCallback(
            function (int $userId, int $elementId, string $type, string $action, string $kind) use (&$granted): void {
                $granted[] = "$userId:$elementId:$type.$action:$kind";
            },
        );

        $this->createCreator(privilegesManager: $privilegesManager)->create(new ProdFieldsDto(title: 'Dizzy'));

        self::assertContains('7:42:zxProd.publicReceive:allow', $granted);
        self::assertContains('7:42:zxProd.publicDelete:allow', $granted);
        self::assertContains('7:42:zxRelease.publicReceive:allow', $granted);
        self::assertContains('7:42:file.delete:allow', $granted);
    }

    public function testFailedElementCreationIsAnInternalError(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('createElement')->willReturn(null);
        $creator = $this->createCreator($structureManager);

        try {
            $creator->create(new ProdFieldsDto(title: 'Dizzy'));
            self::fail('ProdDataException was not thrown');
        } catch (ProdDataException $exception) {
            self::assertSame(500, $exception->getStatusCode());
        }
    }

    private function createCreator(
        ?structureManager $structureManager = null,
        ?ProdFieldsWriter $writer = null,
        ?privilegesManager $privilegesManager = null,
    ): ProdCreator {
        if ($structureManager === null) {
            $structureManager = $this->createStub(structureManager::class);
            $structureManager->method('createElement')->willReturn(new ZxProdElementSpy());
        }
        $user = $this->createStub(CurrentUser::class);
        $user->id = 7;
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user);

        return new ProdCreator(
            new ProdRepository($structureManager),
            $writer ?? $this->createStub(ProdFieldsWriter::class),
            new CreatorPrivilegesGranter($privilegesManager ?? $this->createStub(privilegesManager::class), $currentUserService),
            $currentUserService,
        );
    }
}
