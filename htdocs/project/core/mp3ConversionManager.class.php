<?php

class mp3ConversionManager extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    const CONVERSION_SERVER_ADDRESS = 'http://converter.dev.artweb.ee/index.php';

    protected $collection;

    public function __construct()
    {
        $this->collection = persistableCollection::getInstance('conversion_queue');
    }

    public function addToConversionQueue($elementId)
    {
        if (!$this->checkInQueue($elementId)) {
            $this->saveToQueue($elementId);
        } else {
            $this->updateInQueue($elementId);
        }
    }

    protected function checkInQueue($elementId)
    {
        $conditions = [
            ['elementId', '=', $elementId],
        ];
        if ($records = $this->collection->conditionalLoad('elementId', $conditions)) {
            return true;
        } else {
            return false;
        }
    }

    protected function saveToQueue($elementId)
    {
        $object = $this->collection->getEmptyObject();
        $object->status = 'awaiting';
        $object->dateAdded = time();
        $object->elementId = $elementId;
        $object->persist();
    }

    protected function updateInQueue($elementId)
    {
        $db = $this->getService('db');
        $db->table('conversion_queue')->where('elementId', $elementId)->update(
            [
                'status' => 'awaiting',
                'dateAttempted' => time(),
            ]
        );
    }

    protected function updateQueueItemStatus(persistableObject $item, $status, $extra = '')
    {
        $item->status = $status;
        $item->dateAttempted = time();
        $item->persist();

        echo date('d.m.Y H:i:s') . ' ' . $item->elementId . ': ' . $status . ' ' . $extra . '<br/>';
        flush();
    }

    public function convertQueueItems()
    {
        $linksManager = $this->getService('linksManager');
        $structureManager = $this->getService('structureManager');

        $timeLimit = 5 * 60;
        while ($timeLimit > 0 && $item = $this->getNextQueueItem()) {
            $start = microtime(true);
            if ($connectedIds = $linksManager->getConnectedIdList($item->elementId, 'ayTrack', 'child')) {
                $this->updateQueueItemStatus($item, 'secondarytrack_skipped', $item->elementId);
                continue;
            }

            $this->updateQueueItemStatus($item, 'searching_element', $item->elementId);
            /**
             * @var zxMusicElement $element
             */
            if ($element = $structureManager->getElementById($item->elementId)) {
                $this->updateQueueItemStatus($item, 'loading_file', $element->title);
                if ($file = $element->getOriginalFilePath()) {
                    $this->updateQueueItemStatus($item, 'sending_request');


                    if ($data = $this->sendConversionRequest(
                        $element->id,
                        $element->generateConvertedBaseName(),
                        $file,
                        $element->getChannelsType(),
                        $element->getChipType(),
                        $element->getFrequency(),
                        $element->getIntFrequency()
                    )
                    ) {
                        $this->updateQueueItemStatus($item, 'info_decoding');
                        if ($this->applyData($data, $element, $item)) {
                            $this->updateQueueItemStatus($item, 'success');
                        } else {
                            $this->updateQueueItemStatus($item, 'data_notapplied');
                        }
                    } else {
                        $this->updateQueueItemStatus($item, 'request_fail');
                    }
                } else {
                    $this->updateQueueItemStatus($item, 'no_original_file');
                }
            } else {
                $this->updateQueueItemStatus($item, 'element_not_found');
            }
            $end = microtime(true);
            $timeLimit = $timeLimit - ($end - $start);
        }
    }

    protected function getNextQueueItem()
    {
        $conditions = ['status' => 'awaiting'];
        if ($records = $this->collection->load($conditions, [], false, [0, 1])) {
            return reset($records);
        }
        return false;
    }

    protected function sendConversionRequest(
        $id,
        $baseName,
        $originalFilePath,
        $channelsType,
        $chipType,
        $frequency,
        $intFrequency
    ) {
        $channelsIndex = [
            'ABC' => 0,
            'ACB' => 1,
            'BAC' => 2,
            'BCA' => 3,
            'CBA' => 4,
            'CAB' => 5,
            'mono' => 6,
        ];
        if ($chipType == 'ym') {
            $chipType = 1;
        } else {
            $chipType = 0;
        }

        $result = false;
        if (is_file($originalFilePath)) {
            $target_url = self::CONVERSION_SERVER_ADDRESS;
            $post = [
                'action' => 'upload',
                'channels' => $channelsIndex[$channelsType],
                'chipType' => $chipType,
                'id' => $id,
                'baseName' => $baseName,
            ];
            if ($frequency) {
                $post['frequency'] = $frequency;
            }
            if ($intFrequency) {
                $post['frameDuration'] = round(1000000 / $intFrequency);
            }
            if (class_exists('CurlFile')) {
                $post['original'] = new CurlFile($originalFilePath, 'application/octet-stream', 'original');
            } else {
                $post['original'] = '@' . $originalFilePath;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $target_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
            $response = curl_exec($ch);
            if (!$response) {
                $result = false;
                $this->logError('mp3Conversion request failed: ' . curl_error($ch));
            } else {
                $result = $response;
            }
            curl_close($ch);
        } else {
            $this->logError('mp3Conversion original file is not a file: ' . $originalFilePath);
        }
        return $result;
    }

    protected function applyData($data, $element, $item)
    {
        $result = false;
        if ($infoList = json_decode($data)) {
            $result = true;
            $linksManager = $this->getService('linksManager');
            $structureManager = $this->getService('structureManager');

            $trackElements = [$element];
            if ($connectedIds = $linksManager->getConnectedIdList($element->id, 'ayTrack')) {
                foreach ($connectedIds as $connectedId) {
                    if ($connectedElement = $structureManager->getElementById($connectedId)) {
                        $trackElements[] = $connectedElement;
                    }
                }
            }

            foreach ($infoList as $key => &$info) {
                if (!($trackElement = array_shift($trackElements))) {
                    if ($catalogueElement = $structureManager->getElementByMarker('musicCatalogue')) {
                        if ($copyInfo = $structureManager->copyElements(
                            [$element->id],
                            $catalogueElement->id
                        )
                        ) {
                            $firstCopyInfo = reset($copyInfo);
                            $trackElement = $structureManager->getElementById($firstCopyInfo);
                            $linksManager->linkElements($element->id, $trackElement->id, 'ayTrack');
                        }
                    }
                }

                if ($trackElement) {
                    if (count($infoList) > 1) {
                        $extraText = $info->title . ' ' . ($key + 1);
                        $trackElement->title = $extraText;
                        $trackElement->structureName = $trackElement->title;
                    }

                    if (!$this->updateElement($info, $trackElement, $item)) {
                        $result = false;
                        break;
                    }
                }
            }
        } else {
            $this->updateQueueItemStatus($item, 'json_error');
        }
        return $result;
    }

    protected function updateElement($info, zxMusicElement $element, $item)
    {
        $this->updateQueueItemStatus($item, 'updateelement_start', $info->id);

        $element->type = $info->type;
        //todo: temporary fix until conversion server gets fixed
        if (stripos($info->container, 'Program:') === false) {
            $element->container = $info->container;
        }
        $element->program = $info->program;

        $element->internalTitle = preg_replace('/[[:^print:]]/', '', $info->title);
        $element->internalAuthor = preg_replace('/[[:^print:]]/', '', $info->author);
        $element->time = $info->time;
        $element->channels = $info->channels;
        $element->conversionChannelsType = $element->getChannelsType();
        $element->conversionChipType = $element->getChipType();
        $element->conversionFrequency = $element->getChipType();
        $element->conversionIntFrequency = $element->getIntFrequency();
        $element->converterVersion = "4440";
        $element->mp3Name = $info->mp3Name;

        if ($info->type == 'TS' || ($info->type == 'PT3' && $info->channels == 6)) {
            $element->formatGroup = 'ts';
        } elseif ($info->type == 'COP') {
            $element->formatGroup = 'saa';
        } elseif ($info->type == 'DMM' || $info->type == 'SQD' || $info->type == 'DST' || $info->type == 'STR' || $info->type == 'ET1' || $info->type == 'CHI' || $info->type == 'PDT') {
            $element->formatGroup = 'digitalay';
        } elseif ($info->type == 'TFC' || $info->type == 'TD0' || $info->type == 'TF0' || $info->type == 'TFD' || $info->type == 'TFE') {
            $element->formatGroup = 'fm';
        }

        $element->persistElementData();

        return true;
    }
}
