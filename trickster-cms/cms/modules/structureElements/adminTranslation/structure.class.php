<?php

class adminTranslationElement extends TranslationStructureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_translation';
    protected $allowedTypes = [];
    public $defaultActionName = 'showForm';
    public $role = 'content';
}