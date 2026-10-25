<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use App\Users\CurrentUserService;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;
use ZxArt\ZxProdCategories\CategoryIds;
use zxProdElement;

/**
 * Creates a production on behalf of the current user: the element under its
 * first category (a production's categories are its structural parents), its
 * fields, and the privileges that let its creator keep editing it and its
 * releases. Checks no privilege of its own — the caller does.
 *
 * The caller refreshes the user's privileges once it has created everything it
 * is going to; a batch creates many productions in a row.
 *
 * @psalm-api
 */
final readonly class ProdCreator
{
    /** @var list<array{string, string}> structure type, action */
    private const array CREATOR_PRIVILEGES = [
        ['zxProd', 'showPublicForm'],
        ['zxProd', 'publicReceive'],
        ['zxProd', 'publicDelete'],
        ['zxProd', 'deleteFile'],
        ['zxProd', 'deleteAuthor'],
        ['zxProd', 'submitTags'],
        ['zxProd', 'receiveFiles'],
        ['zxRelease', 'showPublicForm'],
        ['zxRelease', 'publicReceive'],
        ['zxRelease', 'publicDelete'],
        ['zxRelease', 'deleteFile'],
        ['zxRelease', 'deleteAuthor'],
        ['zxRelease', 'submitTags'],
        ['zxRelease', 'clone'],
        ['file', 'delete'],
        ['file', 'receive'],
    ];

    public function __construct(
        private ProdRepository $prodRepository,
        private ProdFieldsWriter $prodFieldsWriter,
        private CreatorPrivilegesGranter $creatorPrivilegesGranter,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function create(ProdFieldsDto $fields): zxProdElement
    {
        $prod = $this->prodRepository->create($fields->categoryIds[0] ?? CategoryIds::MISC->value);
        $userId = (int)$this->currentUserService->getCurrentUser()->id;
        // persisting the structure record is what gives the element its creation time
        $prodId = $prod->getPersistedId();
        $prod->dateAdded = $prod->getCreatedTimestamp();
        $prod->userId = $userId;

        $this->prodFieldsWriter->apply($prod, $fields);
        $this->creatorPrivilegesGranter->grant($prodId, self::CREATOR_PRIVILEGES);
        $prod->logCreation();

        return $prod;
    }
}
