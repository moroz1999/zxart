<?php

class partyDataResponseConverter extends dataResponseConverter
{
    public function convert($data)
    {
        $result = [];
        foreach ($data as $element) {
            $info = [];
            $info['id'] = $element->id;
            $info['structureType'] = $element->structureType;
            $info['dateCreated'] = $element->dateCreated;
            $info['dateModified'] = $element->dateModified;
            $info['title'] = $element->title;
            $info['url'] = $element->URL;
            $info['city'] = $element->getCityTitle();
            $info['country'] = $element->getCountryTitle();
            $info['year'] = $element->getYear();
            $result[] = $info;
        }

        return $result;
    }
}