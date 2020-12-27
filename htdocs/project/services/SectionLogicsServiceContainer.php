<?php

class SectionLogicsServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new SectionLogics();
    }

    public function makeInjections($instance)
    {
        $sectionLogics = $instance;
        if ($structureManager = $this->getOption('structureManager')) {
            $sectionLogics->setStructureManager($structureManager);
        } else {
            $sectionLogics->setStructureManager($this->registry->getService('structureManager'));
        }
        if ($languagesManager = $this->getOption('LanguagesManager')) {
            $sectionLogics->setLanguagesManager($languagesManager);
        } else {
            $sectionLogics->setLanguagesManager($this->registry->getService('LanguagesManager'));
        }
        return $sectionLogics;
    }
}