<?php

interface specialFieldsElementInterface
{
    public function getSpecialFields();

    public function getSpecialData($targetLanguageId = null);

    public function getSpecialDataByKey($key, $languageId = '');
}