<?php

class formFieldDataResponseConverter extends StructuredDataResponseConverter
{
    use SimpleDataResponseConverter;
    protected $defaultPreset = 'api';
}