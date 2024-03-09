<?php

class DmdManager
{
    /**
     * @var ProdsDownloader
     */
    private $prodsDownloader;
    /**
     * @var structureManager
     */
    private $structureManager;
    /**
     * @var AuthorsManager
     */
    private $authorsManager;
    /**
     * @var PathsManager
     */
    private $pathsManager;
    const PARTY_ID = 413942;
//    const PARTY_ID = 410569;
    const COMPO = 'onlineattr';

    /**
     * @param PathsManager $pathsManager
     */
    public function setPathsManager(PathsManager $pathsManager): void
    {
        $this->pathsManager = $pathsManager;
    }

    /**
     * @param AuthorsManager $authorsManager
     */
    public function setAuthorsManager(authorsManager $authorsManager): void
    {
        $this->authorsManager = $authorsManager;
    }

    /**
     * @param structureManager $structureManager
     */
    public function setStructureManager(structureManager $structureManager): void
    {
        $this->structureManager = $structureManager;
    }

    /**
     * @param ProdsDownloader $prodsDownloader
     */
    public function setProdsDownloader(ProdsDownloader $prodsDownloader): void
    {
        $this->prodsDownloader = $prodsDownloader;
    }

    public function importAll()
    {
        $path = PUBLIC_PATH . "output/dmd/images";
        $directoryIterator = new DirectoryIterator($path);
        $count = 0;
        foreach ($directoryIterator as $file) {
            if ($file->isFile()) {

                $json = json_decode(file_get_contents($file->getRealPath()), true);
                echo $count++ . ' ' . $json['place'] . ' ' . $json['author'] . ' ' . $json['name'] . '<br>';

                $gatheredInfo = [
                    'id' => $json['author'],
                    'title' => $json['author'],
                ];
                $authorElement = $this->authorsManager->importAuthor($gatheredInfo, null);


                /**
                 * @var zxPictureElement $zxPictureElement
                 */
                $zxPictureElement = $this->structureManager->createElement('zxPicture', 'show', $authorElement->getId());

                $zxPictureElement->title = $json['name'];
                $zxPictureElement->structureName = $zxPictureElement->title;
                $zxPictureElement->party = self::PARTY_ID;
                $zxPictureElement->partyplace = $json['place'];
                $zxPictureElement->compo = self::COMPO;

                $info = pathinfo($json['image']);
                $fileName = $info['filename'] . '.' . $info['extension'];

                $zxPictureElement->image = $zxPictureElement->getId();
                $zxPictureElement->originalName = $fileName;
                $zxPictureElement->author = [$authorElement->getId()];
                $zxPictureElement->type = 'attributes';
                $zxPictureElement->dateAdded = $zxPictureElement->dateCreated;

                $zxPictureElement->updateYear();

                $zxPictureElement->renewPartyLink();
                $zxPictureElement->renewAuthorLink();

                $this->prodsDownloader->moveFileContents($zxPictureElement->getOriginalPath(), $json['image']);

                $zxPictureElement->persistElementData();
                $zxPictureElement->updateMd5($this->pathsManager->getPath('uploads') . $zxPictureElement->image, $zxPictureElement->originalName);

            }
        }

    }
}