<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Users\Dto\MonthlyCountDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\Rest\MonthlyCountRestDto;
use ZxArt\Users\Services\UserManagementPrivileges;
use ZxArt\Users\UsersStatsService;

/**
 * The figures of the users dashboard (`/users-stats-data/`), for whoever holds
 * `root.manageUsers` on the public root.
 *
 * @psalm-api
 */
final class UsersStatsData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly UsersStatsService $usersStatsService,
        private readonly UserManagementPrivileges $privileges,
        private readonly ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller): void
    {
        try {
            $this->privileges->assertMayManageUsers();
            $stats = $this->usersStatsService->getStats();
            $this->renderer->assign('body', [
                'total' => $stats->total,
                'newLastWeek' => $stats->newLastWeek,
                'newLastMonth' => $stats->newLastMonth,
                'banned' => $stats->banned,
                'unverified' => $stats->unverified,
                'registrationsByMonth' => array_map(
                    fn(MonthlyCountDto $month): MonthlyCountRestDto => $this->objectMapper->map($month, MonthlyCountRestDto::class),
                    $stats->registrationsByMonth,
                ),
            ]);
        } catch (UserDataException $exception) {
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('UsersStatsData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
