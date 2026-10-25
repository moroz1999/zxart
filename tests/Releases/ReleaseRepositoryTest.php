<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use zxProdElement;
use zxReleaseElement;

final class ReleaseRepositoryTest extends TestCase
{
    public function testGetAnswersTheRelease(): void
    {
        $release = $this->createStub(zxReleaseElement::class);

        self::assertSame($release, $this->repository($this->managerFinding($release))->get(42));
    }

    public function testGetWithoutAnIdIsABadRequest(): void
    {
        $this->assertStatus(400, fn() => $this->repository($this->managerFinding(null))->get(0));
    }

    public function testGetOfAMissingOrAnotherElementIsNotFound(): void
    {
        $this->assertStatus(404, fn() => $this->repository($this->managerFinding(null))->get(42));
        $this->assertStatus(404, fn() => $this->repository($this->managerFinding($this->createStub(zxProdElement::class)))->get(42));
    }

    public function testCreateMakesTheElementUnderTheProduction(): void
    {
        $release = $this->createStub(zxReleaseElement::class);
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects(self::once())->method('createElement')
            ->with('zxRelease', 'show', 7)
            ->willReturn($release);

        self::assertSame($release, $this->repository($structureManager)->create(7));
    }

    public function testCreateWithoutAProductionIsABadRequest(): void
    {
        $this->assertStatus(400, fn() => $this->repository($this->createStub(structureManager::class))->create(0));
    }

    public function testAFailedCreationIsAnInternalError(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('createElement')->willReturn(null);

        $this->assertStatus(500, fn() => $this->repository($structureManager)->create(7));
    }

    public function testSaveTouchAndDeleteGoToTheElement(): void
    {
        $release = $this->createMock(zxReleaseElement::class);
        $release->expects(self::once())->method('persistElementData');
        $release->expects(self::once())->method('persistModificationTime');
        $release->expects(self::once())->method('deleteElementData');
        $repository = $this->repository($this->createStub(structureManager::class));

        $repository->save($release);
        $repository->touch($release);
        $repository->delete($release);
    }

    private function managerFinding(?object $element): structureManager
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturn($element);

        return $structureManager;
    }

    private function repository(structureManager $structureManager): ReleaseRepository
    {
        return new ReleaseRepository($structureManager);
    }

    private function assertStatus(int $status, callable $call): void
    {
        try {
            $call();
            self::fail("Expected a $status");
        } catch (ReleaseDataException $exception) {
            self::assertSame($status, $exception->getStatusCode());
        }
    }
}
