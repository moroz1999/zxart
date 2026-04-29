<?php

interface MetadataProviderInterface
{
    public function getMetaTitle();

    public function getMetaDescription(): string;

    public function getMetaKeywords();

    public function getCanonicalUrl();

    public function getMetaDenyIndex();
}