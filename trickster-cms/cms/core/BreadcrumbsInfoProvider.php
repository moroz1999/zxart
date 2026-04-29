<?php

interface BreadcrumbsInfoProvider
{
    public function isBreadCrumb(): bool;
    public function getBreadcrumbsTitle(): string;
    public function getBreadcrumbsUrl(): string;
}