<?php

/**
 * Feedback form page. Renders a fixed name/email/message form (Angular <zx-feedback-form>).
 * Submissions are handled by the ZxArt\Controllers\Feedback REST endpoint and sent by email only.
 *
 * @property string $title
 * @property string $destination intro recipient email address
 * @property string $content intro HTML shown above the form
 */
class feedbackElement extends menuDependantStructureElement
{
    public $dataResourceName = 'module_feedback';
    public $defaultActionName = 'show';
    public $role = 'content';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['destination'] = 'text';
        $moduleStructure['content'] = 'html';
    }

    /** Recipient email address; empty string when not configured. */
    public function getDestination(): string
    {
        return (string)$this->destination;
    }
}
