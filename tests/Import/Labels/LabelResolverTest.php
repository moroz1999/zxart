<?php
declare(strict_types=1);

namespace Tests\Import\Labels;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Resolver;
use ZxArt\Authors\Repositories\AuthorsRepository;
use ZxArt\Authors\Repositories\AuthorAliasesRepository;
use ZxArt\Groups\Repositories\GroupsRepository;
use ZxArt\Groups\Repositories\GroupAliasesRepository;

final class LabelResolverTest extends TestCase
{
    private function createResolver(
        AuthorsRepository $authorsRepository,
        AuthorAliasesRepository $authorAliasesRepository,
        GroupsRepository $groupsRepository,
        GroupAliasesRepository $groupAliasesRepository,
        \structureManager $structureManager,
        Resolver $resolver,
    ): LabelResolver {
        return new LabelResolver(
            $authorsRepository,
            $authorAliasesRepository,
            $groupsRepository,
            $groupAliasesRepository,
            $structureManager,
            new \ZxArt\Import\Authors\AuthorSufficiencyChecker(),
            $resolver,
        );
    }

    public function testResolveAuthorByNameAndLocation(): void
    {
        $authorsRepository = $this->createMock(AuthorsRepository::class);
        $authorAliasesRepository = $this->createMock(AuthorAliasesRepository::class);
        $groupsRepository = $this->createMock(GroupsRepository::class);
        $groupAliasesRepository = $this->createMock(GroupAliasesRepository::class);
        $structureManager = $this->createMock(\structureManager::class);
        $resolver = $this->createMock(Resolver::class);

        $label = new PersonLabel(
            name: 'Gasman',
            realName: 'Matt Westcott',
            cityName: 'London',
            countryName: 'UK',
            isAlias: null,
            groupLabels: [new GroupLabel(name: 'Raww')]
        );

        // IDs for entities and aliases
        $authorsRepository->expects($this->once())
            ->method('findAuthorIdsByName')
            ->with('Gasman', 'Matt Westcott')
            ->willReturn([101]);
        $authorAliasesRepository->expects($this->once())
            ->method('findAliasIdsByName')
            ->with('Gasman')
            ->willReturn([201]);

        // Prepare author entity
        $author = $this->createMock(\authorElement::class);
        $author->method('getGroupsList')->willReturn([$this->makeGroupWithTitle('Raww')]);
        $author->method('getCountryTitle')->willReturn('UK');
        $author->method('getCityTitle')->willReturn('London');
        $author->method('matchesCountry')->with('UK')->willReturn(true);
        $author->method('matchesCity')->with('London')->willReturn(true);
        $author->method('getTitle')->willReturn('Some Other');
        $author->realName = 'Matt Westcott';

        // Alias pointing to author
        $alias = $this->createMock(\authorAliasElement::class);
        $alias->method('getAuthorElement')->willReturn($author);
        $alias->method('getTitle')->willReturn('Gasman');

        // structureManager returns either author or alias by id
        $structureManager->method('getElementById')
            ->willReturnCallback(function (int $id) use ($author, $alias) {
                return match ($id) {
                    101 => $author,
                    201 => $alias,
                    default => null,
                };
            });

        // Resolver scoring helpers
        $resolver->method('valueMatches')->willReturnCallback(function (?string $a, ?string $b) {
            return $a !== null && $b !== null && strcasecmp($a, $b) === 0;
        });
        $resolver->method('alphanumericValueMatches')->willReturnCallback(function (?string $a, ?string $b) {
            if ($a === null || $b === null) {
                return false;
            }
            $aa = preg_replace('~[^a-z0-9]+~i', '', $a);
            $bb = preg_replace('~[^a-z0-9]+~i', '', $b);
            return strcasecmp($aa, $bb) === 0;
        });
        $resolver->method('valueStartsWith')->willReturnCallback(function (?string $a, ?string $b) {
            if ($a === null || $b === null) {
                return false;
            }
            return str_starts_with(strtolower($a), strtolower($b));
        });

        $resolverInstance = $this->createResolver(
            $authorsRepository,
            $authorAliasesRepository,
            $groupsRepository,
            $groupAliasesRepository,
            $structureManager,
            $resolver,
        );

        $result = $resolverInstance->resolve($label);

        // Should resolve to alias (as resolveEntity returns alias element when alias participates)
        $this->assertInstanceOf(\authorAliasElement::class, $result);
        $this->assertSame($alias, $result);
    }

    public function testResolveAuthorExcludedOnCountryMismatch(): void
    {
        $authorsRepository = $this->createMock(AuthorsRepository::class);
        $authorAliasesRepository = $this->createMock(AuthorAliasesRepository::class);
        $groupsRepository = $this->createMock(GroupsRepository::class);
        $groupAliasesRepository = $this->createMock(GroupAliasesRepository::class);
        $structureManager = $this->createMock(\structureManager::class);
        $resolver = $this->createMock(Resolver::class);

        $label = new PersonLabel(
            name: 'Artist',
            realName: 'John Smith',
            countryName: 'USA',
        );

        $authorsRepository->method('findAuthorIdsByName')->willReturn([1]);
        $authorAliasesRepository->method('findAliasIdsByName')->willReturn([]);

        $author = $this->createMock(\authorElement::class);
        $author->method('getGroupsList')->willReturn([]);
        $author->method('getCountryTitle')->willReturn('UK');
        $author->method('matchesCountry')->with('USA')->willReturn(false);
        $author->realName = 'John Smith';

        $structureManager->method('getElementById')->willReturn($author);

        $resolver->method('valueMatches')->willReturn(false);
        $resolver->method('alphanumericValueMatches')->willReturn(false);
        $resolver->method('valueStartsWith')->willReturn(false);

        $resolverInstance = $this->createResolver(
            $authorsRepository,
            $authorAliasesRepository,
            $groupsRepository,
            $groupAliasesRepository,
            $structureManager,
            $resolver,
        );

        $result = $resolverInstance->resolve($label);
        $this->assertNull($result);
    }

    public function testResolveGroupScoresMembersAndLocation(): void
    {
        $authorsRepository = $this->createMock(AuthorsRepository::class);
        $authorAliasesRepository = $this->createMock(AuthorAliasesRepository::class);
        $groupsRepository = $this->createMock(GroupsRepository::class);
        $groupAliasesRepository = $this->createMock(GroupAliasesRepository::class);
        $structureManager = $this->createMock(\structureManager::class);
        $resolver = $this->createMock(Resolver::class);

        $label = new GroupLabel(
            name: 'The Coders',
            cityName: 'Berlin',
            countryName: 'DE',
            memberNames: ['Alice', 'Bob']
        );

        $groupsRepository->method('findGroupIdsByName')->willReturn([10]);
        $groupAliasesRepository->method('findAliasIdsByName')->willReturn([]);

        $group = $this->createMock(\groupElement::class);
        $group->method('getTitle')->willReturn('Coders');
        $group->method('getCountryTitle')->willReturn('DE');
        $group->method('matchesCountry')->with('DE')->willReturn(true);
        $group->method('matchesCity')->with('Berlin')->willReturn(true);

        $authorAlice = $this->createMock(\authorElement::class);
        $authorAlice->method('gatherAuthorNames')->willReturn(['Alice Wonderland']);
        $authorBob = $this->createMock(\authorElement::class);
        $authorBob->method('gatherAuthorNames')->willReturn(['Robert "Bob"']);

        $group->method('getAuthorsInfo')->willReturn([
            ['authorElement' => $authorAlice],
            ['authorElement' => $authorBob],
        ]);

        $structureManager->method('getElementById')->willReturn($group);

        $resolver->method('valueMatches')->willReturn(false);
        $resolver->method('alphanumericValueMatches')->willReturn(true); // Coders vs The Coders
        $resolver->method('valueStartsWith')->willReturn(false);

        $resolverInstance = $this->createResolver(
            $authorsRepository,
            $authorAliasesRepository,
            $groupsRepository,
            $groupAliasesRepository,
            $structureManager,
            $resolver,
        );

        $result = $resolverInstance->resolve($label);
        $this->assertInstanceOf(\groupElement::class, $result);
        $this->assertSame($group, $result);
    }

    public function testResolveAuthorRequiresGroupIntersectionWhenBothHaveGroups(): void
    {
        $authorsRepository = $this->createMock(AuthorsRepository::class);
        $authorAliasesRepository = $this->createMock(AuthorAliasesRepository::class);
        $groupsRepository = $this->createMock(GroupsRepository::class);
        $groupAliasesRepository = $this->createMock(GroupAliasesRepository::class);
        $structureManager = $this->createMock(\structureManager::class);
        $resolver = $this->createMock(Resolver::class);

        $label = new PersonLabel(
            name: 'Coder',
            realName: 'John Doe',
            groupLabels: [new GroupLabel(name: 'Group A')]
        );

        $authorsRepository->method('findAuthorIdsByName')->willReturn([77]);
        $authorAliasesRepository->method('findAliasIdsByName')->willReturn([]);

        $groupB = $this->makeGroupWithTitle('Group B');

        $author = $this->createMock(\authorElement::class);
        $author->method('getGroupsList')->willReturn([$groupB]);
        $author->method('getCountryTitle')->willReturn(null);
        $author->method('getCityTitle')->willReturn(null);
        $author->realName = 'John Doe';

        $structureManager->method('getElementById')->willReturn($author);

        $resolver->method('valueMatches')->willReturn(true); // name matches
        $resolver->method('alphanumericValueMatches')->willReturn(false);
        $resolver->method('valueStartsWith')->willReturn(false);

        $resolverInstance = $this->createResolver(
            $authorsRepository,
            $authorAliasesRepository,
            $groupsRepository,
            $groupAliasesRepository,
            $structureManager,
            $resolver,
        );

        $result = $resolverInstance->resolve($label);
        // No group intersection, should be excluded -> null
        $this->assertNull($result);
    }

    private function makeGroupWithTitle(string $title): object
    {
        $group = $this->createMock(\groupElement::class);
        $group->method('getTitle')->willReturn($title);
        return $group;
    }
}
