<?php

use Sobak\Scrawler\Block\Matcher\CssSelectorAttributeMatcher;
use Sobak\Scrawler\Block\Matcher\CssSelectorHtmlMatcher;
use Sobak\Scrawler\Block\Matcher\CssSelectorListMatcher;
use Sobak\Scrawler\Block\ResultWriter\FilenameProvider\EntityPropertyFilenameProvider;
use Sobak\Scrawler\Block\ResultWriter\JsonFileResultWriter;
use Sobak\Scrawler\Block\UrlListProvider\EmptyUrlListProvider;
use Sobak\Scrawler\Configuration\Configuration;
use Sobak\Scrawler\Configuration\ObjectConfiguration;

include_once('ImageEntity.php');
include_once('project/core/CssSelectorAttributeMatcher.php');
$scrawler = new Configuration();

$scrawler
    ->setOperationName('dmd')
    ->setBaseUrl('https://demodulation.retroscene.org/competition/?competition_id=25')
    ->addUrlListProvider(new EmptyUrlListProvider())
    ->addObjectDefinition('image', new CssSelectorListMatcher('div.b-work'), function (ObjectConfiguration $object) {
        $object
            ->addFieldDefinition('name', new CssSelectorHtmlMatcher('span.b-work__name'))
            ->addFieldDefinition('author', new CssSelectorHtmlMatcher('span.b-work__author'))
            ->addFieldDefinition('image', (new CssSelectorAttributeMatcher('a.b-work__file-href'))->attribute('href'))
            ->addFieldDefinition('place', (new CssSelectorHtmlMatcher('span.b-work__num')))
            ->addEntityMapping(ImageEntity::class)
            ->addResultWriter(ImageEntity::class, new JsonFileResultWriter([
                'directory' => 'images/',
                'filename' => new EntityPropertyFilenameProvider([
                    'property' => 'slug',
                ]),
            ]));
    });

return $scrawler;