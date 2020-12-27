<?php

class commentDataResponseConverter extends dataResponseConverter
{
    public function convert($data)
    {
        $result = [];
        foreach ($data as $element) {
            $info = [];
            $info['id'] = $element->id;
            $info['structureType'] = $element->structureType;
            $info['title'] = $element->title;
            $info['url'] = $element->getUrl();
            $result[] = $info;
        }

        return $result;
    }
}