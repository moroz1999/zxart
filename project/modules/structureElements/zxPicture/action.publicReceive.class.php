<?php

use App\Paths\PathsManager;
use App\Users\CurrentUserService;
use ZxArt\Authors\Constants;
use ZxArt\Pictures\Validation\NativePictureFileValidator;

class publicReceiveZxPicture extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxPictureElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $nativePictureFileValidator = $this->getService(NativePictureFileValidator::class);
            $imageChunk = $structureElement->getDataChunk('image');
            if ($imageChunk instanceof imageDataChunk && $imageChunk->originalName !== null) {
                $cachePath = $this->getService(PathsManager::class)->getPath('uploadsCache');
                $filePath = $cachePath . $imageChunk->temporaryName;
                $error = $nativePictureFileValidator->validate(
                    (string)$structureElement->type,
                    (string)$imageChunk->originalName,
                    $filePath,
                );
                if ($error !== null) {
                    $this->respondNativeFileRejected((string)$structureElement->type, $nativePictureFileValidator);
                    return;
                }
            }

            $structureElement->prepareActualData();

            if (trim($structureElement->title) === '') {
                $prod = $structureElement->getReleaseElement();
                if ($prod !== null) {
                    $structureElement->title = $prod->title;
                } elseif ($structureElement->getDataChunk("image")->originalName !== null) {
                    $info = pathinfo($structureElement->getDataChunk("image")->originalName);
                    $structureElement->title = $info['filename'];
                    $structureElement->title = ucfirst($structureElement->title);
                    $structureElement->title = str_replace('_', ' ', ucfirst($structureElement->title));
                }
            }
            $structureElement->dateAdded = $structureElement->getCreatedTimestamp();
            if ($structureElement->getDataChunk("image")->originalName !== null) {
                $structureElement->image = $structureElement->getId();
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }
            if ($structureElement->getDataChunk("inspired")->originalName !== null) {
                $structureElement->inspired = $structureElement->getId() . '_inspired';
                $structureElement->inspiredName = $structureElement->getDataChunk("inspired")->originalName;
            }
            if ($structureElement->getDataChunk("inspired2")->originalName !== null) {
                $structureElement->inspired2 = $structureElement->getId() . '_inspired2';
                $structureElement->inspired2Name = $structureElement->getDataChunk("inspired2")->originalName;
            }
            if ($structureElement->getDataChunk("exeFile")->originalName !== null) {
                $structureElement->exeFile = $structureElement->getId() . '_exe';
                $structureElement->exeFileName = $structureElement->getDataChunk("exeFile")->originalName;
            }
            if ($structureElement->getDataChunk("sequence")->originalName !== null) {
                $structureElement->sequence = $structureElement->getId() . '_sequence';
                $structureElement->sequenceName = $structureElement->getDataChunk("sequence")->originalName;
            }
            $structureElement->structureName = $structureElement->title;

            if (!$structureElement->userId) {
                $currentUserService = $this->getService(CurrentUserService::class);
                $structureElement->userId = $currentUserService->getCurrentUser()->id;
            }

            //if no author is selected, select automatically Unknown author
            if (!$structureElement->author) {
                $structureElement->author = [Constants::UNKNOWN_ID];
            }

            $structureElement->renewPartyLink();
            $structureElement->renewAuthorLink();
            $structureElement->renewOriginalAuthorLink();
            $structureElement->updateProdLink();
            $structureElement->updateTagsInfo();
            $structureElement->updateYear();
            $structureElement->checkGameTag();

            $structureElement->persistElementData();
            $structureElement->updateMd5($this->getService(PathsManager::class)->getPath('uploads') . $structureElement->image, $structureElement->originalName);

            $structureElement->deleteCachedImage();

            $this->respondFormSaved($controller, $structureElement); return;
        } else {
            $structureElement->setViewName('form');
        }
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'border',
            'prod',
            'compo',
            'author',
            'originalAuthor',
            'year',
            'party',
            'partyplace',
            'type',
            'image',
            'inspired',
            'inspired2',
            'exeFile',
            'sequence',
            'tags',
            'description',
            'rotation',
            'denyVoting',
            'denyComments',
            'palette',
        ];
    }

    public function setValidators(&$validators): void
    {
    }

    private function respondNativeFileRejected(
        string $format,
        NativePictureFileValidator $nativePictureFileValidator,
    ): void {
        $rule = $nativePictureFileValidator->getRules()[$format] ?? [
            'extensions' => [],
            'sizes' => [],
        ];
        $renderer = $this->getService(renderer::class);
        $renderer->assign('body', [
            'id' => 0,
            'errorMessage' => $rule['sizes'] === []
                ? 'picture-form.error-native-file-variable'
                : 'picture-form.error-native-file',
            'errorParameters' => $rule,
        ]);
    }
}




