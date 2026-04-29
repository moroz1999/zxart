<?php


trait JsonDataProviderElement
{
    use DataResponseConverterFactory;

    public function getElementData(?string $preset = null): ?array
    {
        if ($converter = $this->getConverter($this->structureType)) {
            if ($converter instanceof PresetDataResponseConverterInterface) {
                $converter->setPreset($preset);
            }
            if ($data = $converter->convert([$this])) {
                return reset($data);
            }
        }
        return null;
    }

    public function getJsonInfo(?string $preset = null): ?string
    {
        if ($data = $this->getElementData($preset)) {
            return json_encode($data);
        }
        return null;
    }
}