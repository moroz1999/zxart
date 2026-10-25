<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use authorElement;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use zxProdElement;

final class ProdRepositoryTest extends TestCase
{
    public function testGetAnswersTheProduction(): void
    {
        $prod = $this->createStub(zxProdElement::class);

        self::assertSame($prod, $this->repository($this->managerFinding($prod))->get(42));
    }

    public function testGetWithoutAnIdIsABadRequest(): void
    {
        $this->assertStatus(400, fn() => $this->repository($this->managerFinding(null))->get(0));
    }

    public function testGetOfAMissingOrAnotherElementIsNotFound(): void
    {
        $this->assertStatus(404, fn() => $this->repository($this->managerFinding(null))->get(42));
        $this->assertStatus(404, fn() => $this->repository($this->managerFinding($this->createStub(authorElement::class)))->get(42));
    }

    public function testCreateMakesTheElementUnderTheCategory(): void
    {
        $prod = $this->createStub(zxProdElement::class);
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects(self::once())->method('createElement')
            ->with('zxProd', 'show', 3, false, 'zxProdCategory')
            ->willReturn($prod);

        self::assertSame($prod, $this->repository($structureManager)->create(3, 'zxProdCategory'));
    }

    public function testAFailedCreationIsAnInternalError(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('createElement')->willReturn(null);

        $this->assertStatus(500, fn() => $this->repository($structureManager)->create(3));
    }

    public function testSaveTouchAndDeleteGoToTheElement(): void
    {
        $prod = $this->createMock(zxProdElement::class);
        $prod->expects(self::once())->method('persistElementData');
        $prod->expects(self::once())->method('persistModificationTime');
        $prod->expects(self::once())->method('deleteElementData');
        $repository = $this->repository($this->createStub(structureManager::class));

        $repository->save($prod);
        $repository->touch($prod);
        $repository->delete($prod);
    }

    private function managerFinding(?object $element): structureManager
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturn($element);

        return $structureManager;
    }

    private function repository(structureManager $structureManager): ProdRepository
    {
        return new ProdRepository($structureManager);
    }

    private function assertStatus(int $status, callable $call): void
    {
        try {
            $call();
            self::fail("Expected a $status");
        } catch (ProdDataException $exception) {
            self::assertSame($status, $exception->getStatusCode());
        }
    }
}
