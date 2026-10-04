<?php

declare(strict_types=1);

namespace ZxArt\Tests\Users;

use App\Logging\EventsLog;
use App\Users\CurrentUser;
use DI\Container;
use DI\ContainerBuilder;
use Illuminate\Database\Connection;
use Illuminate\Database\MySqlConnection;
use LogicException;
use PHPUnit\Framework\TestCase;
use VisitorsManager;
use function DI\factory;

/**
 * CurrentUser reads the session while it is initialized, so only
 * CurrentUserService may create it. An autowired copy is never initialized
 * and erases the signed-in user from the session when it is destroyed.
 */
final class CurrentUserContainerTest extends TestCase
{
    public function testContainerRefusesToBuildCurrentUser(): void
    {
        $container = $this->buildContainer([]);

        $this->expectException(LogicException::class);
        $container->get(CurrentUser::class);
    }

    public function testEventsLogIsBuiltWithoutCurrentUser(): void
    {
        $container = $this->buildContainer([
            Connection::class => $this->createStub(MySqlConnection::class),
            'statsDb' => $this->createStub(MySqlConnection::class),
            VisitorsManager::class => $this->createStub(VisitorsManager::class),
            CurrentUser::class => factory(static fn() => throw new LogicException('CurrentUser must not be injected')),
        ]);

        self::assertInstanceOf(EventsLog::class, $container->get(EventsLog::class));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function buildContainer(array $overrides): Container
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(include ROOT_PATH . 'trickster-cms/cms/core/di-definitions.php');
        $builder->addDefinitions(include ROOT_PATH . 'project/core/di-definitions.php');
        $builder->addDefinitions($overrides);
        return $builder->build();
    }
}
