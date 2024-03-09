<?php

class authorClaimResultEmailDispatchmentType extends designThemeEmailDispatchmentType
{
    protected $displayUnsubscribeLink = false;
    protected $displayWebLink = false;

    public function initialize()
    {
        $this->cssThemeFilesStructure = [
            'public' => ['reset.less'],
            'project' => ['shared.less'],
            'email' => ['main.less'],
            'projectEmail' => ['main.less'],
        ];
        $this->imagesThemeName = 'project';
        $this->emailTemplateThemeName = 'projectEmail';
        $this->emailTemplateName = 'standardLayout.tpl';
        $this->contentTemplateThemeName = 'projectDocument';
        $this->contentTemplateName = 'content.authorClaimResult.tpl';
    }

}