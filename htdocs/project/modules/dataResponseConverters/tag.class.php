<?php

class tagDataResponseConverter extends dataResponseConverter
{
    public function convert($data)
    {
        $result = [];
        foreach ($data as $element) {
            $info = [];
            $info['id'] = $element->id;
            $info['structureType'] = $element->structureType;
            $info['title'] = $element->title;
            if ($element->synonym) {
                $info['title'] .= ", " . $element->synonym;
            }
            if ($element->description) {
                $info['title'] .= " (" . $element->description . ")";
            }
            $info['value'] = $element->title;
            $info['synonym'] = $element->synonym;
            $info['description'] = $element->description;
            $info['url'] = $element->URL;
            $result[] = $info;
        }

        return $result;
    }
}