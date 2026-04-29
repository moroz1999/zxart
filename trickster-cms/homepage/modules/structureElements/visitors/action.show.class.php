<?php

class showVisitors extends structureElementAction
{
    protected $actionsLogData;

    /**
     * @param visitorsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $renderer = $this->getService(renderer::class);
            $requestedVisitor = $controller->getParameter('visitor');
            if ($requestedVisitor) {
                $details = $structureElement->getVisitorDetails($requestedVisitor);
                if (!$details) {
                    $renderer->fileNotFound();
                    die;
                }
                $structureElement->setTemplate('visitor.details.tpl');
                $renderer->assign('visitor', $details);
            } else {
                $structureElement->setTemplate('shared.content.tpl');
                $filters = $structureElement->getFilters();
                $structureElement->loadVisitors();
                $renderer->assign('contentSubTemplate', 'visitors.tpl');
                $renderer->assign('visitorsList', $structureElement->visitorsList);
                $renderer->assign('pager', $structureElement->pager);
                $renderer->assign('filters', $filters);
                if ($filters['product']) {
                    $product = $structureManager->getElementById($filters['product']);
                } else {
                    $product = null;
                }
                $renderer->assign('filterProduct', $product);
                $renderer->assign('filterCategory', $filters['category']
                    ? $structureManager->getElementById($filters['category'])
                    : null);
            }
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'elementId',
            'elementType',
            'elementName',
            'periodStart',
            'periodEnd',
            'userId',
            'userIP',
            'action',
        ];
    }
}