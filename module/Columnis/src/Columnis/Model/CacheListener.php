<?php

namespace Columnis\Model;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

class CacheListener extends AbstractListenerAggregate {

    protected $listeners = array();
    protected $cacheService;

    public function __construct(HtmlCache $cacheService) {
        // We store the cache service generated by Zend\Cache from the service manager
        //$cacheService->setExtension('');
        $this->cacheService = $cacheService;
    }

    public function attach(EventManagerInterface $events) {
        // The AbstractListenerAggregate we are extending from allows us to attach our even listeners
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'getCache'), -1000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'saveCache'), -10000);
    }

    public function getCache(MvcEvent $event) {
        $match = $event->getRouteMatch();

        // is valid route?
        if (!$match) {
            return;
        }

        // does our route have the cache flag set to true?
        if ($match->getParam('cache')) {
            $cacheKey = $this->genCacheName($match);

            // get the cache page for this route
            $data = $this->cacheService->getItem($cacheKey);

            // ensure we have found something valid
            if ($data !== null) {
                $response = $event->getResponse();
                $response->setContent($data);

                return $response;
            }
        }
    }

    public function saveCache(MvcEvent $event) {
        $match = $event->getRouteMatch();

        // is valid route?
        if (!$match) {
            return;
        }

        // does our route have the cache flag set to true?
        if ($match->getParam('cache')) {
            $response = $event->getResponse();
            $data = $response->getContent();

            $this->setCacheDir($match);
            $cacheKey = $this->genCacheName($match);
            $this->cacheService->setItem($cacheKey, $data);
        }
    }

    protected function setCacheDir(RouteMatch $match) {
        $params = $match->getParams();
        $lang = $params['lang'];
        $options = $this->cacheService->getOptions();
        $options->setNamespace($lang);
    }

    protected function genCacheName(RouteMatch $match) {
        $params = $match->getParams();
        $pageId = $params['pageId'];
        return $pageId;
    }

}
