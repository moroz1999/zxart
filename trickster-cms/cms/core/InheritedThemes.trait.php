<?php

trait InheritedThemesTrait
{
    protected function generateInheritedThemesNames($code)
    {
        $this->inheritedThemes = [$code];
        $controller = controller::getInstance();
        if ($pluginNames = $controller->getEnabledPlugins()) {
            foreach ($pluginNames as &$pluginName) {
                $pluginThemeName = $pluginName . ucfirst($code);
                if ($pluginThemeName !== $this->code) {
                    $this->inheritedThemes[] = $pluginThemeName;
                }
            }
        }
        $this->inheritedThemes = array_reverse($this->inheritedThemes);
    }
}