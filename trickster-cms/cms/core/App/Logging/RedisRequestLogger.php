<?php
declare(strict_types=1);

namespace App\Logging;

use Redis;
use RedisException;

/**
 * Class for logging request information to Redis with automatic expiration and retrieval.
 */
readonly class RedisRequestLogger
{

    /**
     * RequestLogger constructor.
     *
     * @param Redis $redis Instance of Redis client.
     * @param int $ttl Time to live for log records in seconds.
     */
    public function __construct(
        private bool  $enabled,
        private Redis $redis,
        private int   $ttl
    )
    {
    }

    /**
     * Logs initial request information with automatic expiration.
     *
     * @param LogRequestDto $dto DTO with request information.
     * @return LogRecordDto|null DTO with log record information including requestId and startTime.
     */
    public function logRequest(LogRequestDto $dto): ?LogRecordDto
    {
        if (!$this->enabled) {
            return null;
        }
        $requestId = uniqid('reqlog:', true);

        $data = [
            'ip' => $dto->ip,
            'url' => $dto->url,
            'start_time' => $dto->startTime,
            'user_agent' => $dto->userAgent,
            'duration' => 0,
            'completed' => false,
        ];

        $this->redis->hMSet($requestId, $data);
        $this->redis->expire($requestId, $this->ttl);


        return new LogRecordDto(
            $requestId,
            $dto->ip,
            $dto->url,
            $dto->userAgent,
            $dto->startTime,
            0,
            false
        );
    }

    /**
     * Updates the duration of the request execution.
     *
     * @param LogRecordUpdateDto $logRecordUpdateDto DTO with log record update information including requestId, startTime, and endTime.
     * @throws RedisException
     */
    public function updateDuration(LogRecordUpdateDto $logRecordUpdateDto): void
    {
        $duration = $logRecordUpdateDto->endTime - $logRecordUpdateDto->startTime;
        if (!$this->enabled) {
            return;
        }
        $this->redis->hSet($logRecordUpdateDto->requestId, 'duration', $duration);
        $this->redis->hSet($logRecordUpdateDto->requestId, 'completed', $logRecordUpdateDto->completed);
    }

    /**
     * Retrieves all log records from Redis, sorted by startTime in descending order, and returns them as a set of DTOs.
     *
     * @return LogRecordDto[] An array of LogRecordDTO objects.
     * @throws RedisException
     */
    public function getAllLogs(): array
    {
        $keys = $this->redis->keys('reqlog:*');
        $logs = [];
        if (!$this->enabled) {
            return $logs;
        }
        foreach ($keys as $key) {
            $logData = $this->redis->hGetAll($key);
            if ($logData) {

                if ($logData['duration'] <= 0) {
                    $logData['duration'] = (new \DateTime())->getTimestamp() - ($logData['start_time'] ?? 0);
                }

                $logs[] = new LogRecordDto(
                    $key,
                    $logData['ip'] ?? '',
                    $logData['url'] ?? '',
                    $logData['user_agent'] ?? '',
                    (float)($logData['start_time'] ?? 0),
                    (float)($logData['duration'] ?? 0),
                    (bool)($logData['completed'] ?? false),
                );
            }
        }

        // Sort logs by startTime in descending order
        usort($logs, static function ($log1, $log2) {
            return $log2->startTime - $log1->startTime;
        });

        return $logs;
    }
}
