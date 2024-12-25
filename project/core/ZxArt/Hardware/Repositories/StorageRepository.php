<?php
declare(strict_types=1);


namespace ZxArt\Hardware\Repositories;

use Illuminate\Database\Connection;
use ZxArt\Hardware\Dto\HardwareDto;

final class StorageRepository
{
    public const TABLE = 'hardware_storage';

    public function __construct(
        private Connection $db,
    )
    {

    }

    public function store(HardwareDto $dto): void
    {
        $this->db->table(self::TABLE)->insert(
            [
                'hardware_id' => $dto->hardwareId,
                'json' => $dto->json,
                'article_id' => $dto->articleId,
            ]
        );
    }
}