<?php
declare(strict_types=1);


namespace ZxArt\Hardware\Services;

use JsonException;
use ZxArt\Hardware\Dto\HardwareDto;
use ZxArt\Hardware\Dbal\StorageDbal;

final class HardwareService
{
    public function __construct(
        private StorageDbal $storageDbal,
    )
    {

    }

    /**
     * @throws JsonException
     */
    public function storeHardwareData(array $data, int $articleId): void
    {
        $dto = new HardwareDTO(
            articleId: $articleId,
            hardwareId: $data['name'],
            json: json_encode($data, JSON_THROW_ON_ERROR)
        );
        $this->storageDbal->store($dto);
    }
}