<?php
declare(strict_types=1);

use App\Logging\LogRecordDto;
use App\Logging\LogRecordUpdateDto;
use App\Logging\LogRequestDto;
use App\Logging\RedisRequestLogger;

trait RequestsLogger
{
    protected ?LogRecordDto $loggedRequestDto = null;
    private RedisRequestLogger $requestLogger;

    private function logRequest(): void
    {
        try {
            $requestLogger = $this->getRequestLogger();
            $requestDto = new LogRequestDto(
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['REQUEST_URI'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                microtime(true),
            );
            $this->loggedRequestDto = $requestLogger->logRequest($requestDto);
        } catch (Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    private function logRequestDuration(): void
    {
        if ($this->loggedRequestDto) {
            try {
                $requestLogger = $this->getRequestLogger();
                $updateDto = new LogRecordUpdateDto(
                    $this->loggedRequestDto->requestId,
                    $this->loggedRequestDto->startTime,
                    microtime(true),
                    true,
                );
                $requestLogger->updateDuration($updateDto);
            } catch (Exception $e) {
                $this->logError($e->getMessage());
            }
        }
    }

    private function getRequestLogger(): RedisRequestLogger
    {
        return $this->getService(RedisRequestLogger::class);
    }
}