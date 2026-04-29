<?php

abstract class StructuredDataResponseConverter extends DataResponseConverter implements PresetDataResponseConverterInterface
{
    protected $preset;
    protected $defaultPreset;

    protected abstract function getRelationStructure();

    public function convert($data)
    {
        $result = [];
        foreach ($data as $element) {
            $info = [];
            if ($relationStructure = $this->getRelationStructure()) {
                if (isset($this->preset)) {
                    $responseStructure = $this->getPresetStructure($this->preset);
                } else {
                    $responseStructure = array_keys($relationStructure);
                }
                foreach ($responseStructure as $key) {
                    if (isset($relationStructure[$key])) {
                        $value = $relationStructure[$key];
                        if ($value instanceof Closure) {
                            if ($endValue = $value($element, $this)) {
                                $info[$key] = $endValue;
                            }
                        } elseif (method_exists($element, $value)) {
                            if ($endValue = $element->$value()) {
                                $info[$key] = $endValue;
                            }
                        } elseif ($endValue = $element->$value) {
                            $info[$key] = $element->$value;
                        }
                    }
                }
            }
            $result[] = $info;
        }

        return $result;
    }

    public function setPreset($preset)
    {
        if ($this->getPresetStructure($preset)) {
            $this->preset = $preset;
        } elseif ($this->defaultPreset) {
            $this->preset = $this->defaultPreset;
        }
    }

    protected function getPresetStructure($preset)
    {
        if (isset($preset)) {
            if ($presets = $this->getPresetsStructure()) {
                if (isset($presets[$preset])) {
                    return $presets[$preset];
                }
            }
        }
        return false;
    }

    abstract protected function getPresetsStructure();
}