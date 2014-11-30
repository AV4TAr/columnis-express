<?php

namespace Columnis\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\ViewEvent;

class PageController extends AbstractActionController {

    public function indexAction() {
        $page = 'home-1.html';
        $pagePath = getcwd() . DIRECTORY_SEPARATOR . 'public/pages' . DIRECTORY_SEPARATOR . $page;        
        $template = 'home';
        $templatePath = $template . DIRECTORY_SEPARATOR . 'main.tpl';
        
        $serviceManager = $this->getServiceLocator();
        $eventManager = $serviceManager->get('View')->getEventManager();
        $eventManager->attach(ViewEvent::EVENT_RESPONSE, function($e) use ($pagePath) {
            file_put_contents($pagePath, $e->getResult());
        });
        $viewVariables = array(
            'prueba' => 'prueba loca funca'
        );
        $view = new ViewModel();
        $view->setTemplate($templatePath);
        $view->setVariables($viewVariables);
        $view->setTerminal(true);

        return $view;
    }

}
