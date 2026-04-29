<?php

class passwordEmailDispatchmentType extends designThemeEmailDispatchmentType
{
    protected $displayUnsubscribeLink = false;
    protected $displayWebLink = false;
    protected $trackLinks = false;

    public function initialize()
    {
        $this->cssThemeFilesStructure = [
            'default' => ['all_variables.less'],
            'public' => ['reset.less', 'component.forms.less'],
            'project' => ['all_variables.less', 'variables.less', 'colors_variables.less', 'shared.less'],
            'email' => ['main.less', 'userData.less'],
            'projectEmail' => ['main.less', 'userData.less'],
        ];
        $this->imagesThemeName = 'project';
        $this->emailTemplateThemeName = 'projectEmail';
        $this->emailTemplateName = 'standardLayout.tpl';
        $this->contentTemplateThemeName = 'projectDocument';
        $this->contentTemplateName = 'content.password.tpl';
    }
}