<?php

trait AjaxFormTrait
{
    protected $ajaxFormSuccess = false;
    protected $ajaxFormSuccessRedirection = false;
    protected $ajaxFormSuccessReload = false;

    public function sendAjaxFormResponse(&$structureElement, $resetForm = true)
    {
        $controller = controller::getInstance();
        $response = [];
        foreach ($structureElement->getFormErrors() as $key => $error) {
            if ($error) {
                $response['errors'][] = $key;
            }
        }
        if (method_exists($structureElement, 'getDynamicFieldErrors')) {
            foreach ($structureElement->getDynamicFieldErrors() as $key => $error) {
                if ($error) {
                    $response['dynamicErrors'][] = $key;
                }
            }
        }
        if (!$this->ajaxFormSuccess) {
            $response['error_message'] = $structureElement->errorMessage;
        } else {
            $response['success_message'] = $structureElement->resultMessage;
        }
        $response['resetForm'] = $resetForm;

        if ($this->ajaxFormSuccessRedirection) {
            $response['redirect'] = $this->ajaxFormSuccessRedirection;
        } elseif (!empty($controller->getParameter('redirect'))) {
            $response['redirect'] = $controller->getParameter('redirect');
        }
        if ($this->ajaxFormSuccessReload || !empty($controller->getParameter('reload'))) {
            $response['reload'] = true;
            /**
             * @var ServerSessionManager $serverSessionManager
             */
            $serverSessionManager = $this->getService(ServerSessionManager::class);
            $serverSessionManager->set('showSuccessMessage' . $structureElement->id, true);
        }
        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof RendererPluginAppendInterface) {
            $renderer->assignResponseData('form' . $structureElement->id . $this->actionName, $response);
        }
    }

    protected function validateAjaxRequest()
    {
        $controller = controller::getInstance();
        if (($check = (int)$controller->getParameter('check')) && (time() - $check < 60)) {
            return true;
        }

        return false;
    }
}