<?php
namespace Core\Framework;

use Core\Framework\Amvc\Controller\Redirect;
use Core\Framework\Amvc\Controller\RedirectInterface;
use Core\Toolbox\Strings\CamelCase;
use Core\Framework\Amvc\App\AbstractApp;

/**
 * Dispatcher.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016-2017
 * @license MIT
 */
class Dispatcher extends AbstractAcap
{

    /**
     *
     * @var bool
     */
    private $ajax;

    /**
     *
     * @var string
     */
    private $format;

    /**
     *
     * @var Core
     */
    private $core;

    /**
     * Constructor
     *
     * @param Core $core
     */
    public function __construct(Core $core)
    {
        $this->core = $core;
    }

    /**
     * Sets ajax flag
     *
     * @param bool $ajax
     */
    public function setAjax(bool $ajax)
    {
        $this->ajax = $ajax;
    }

    /**
     * Returns ajax flag state
     *
     * @return bool
     */
    public function getAjax(): bool
    {
        return $this->ajax ?? false;
    }

    /**
     * Returns the result format
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format ?? 'html';
    }

    /**
     * General dispatcher
     */
    public function dispatch()
    {
        $this->managePost();
        
        // Make sure there is a route and abort request if not
        $route = $this->core->router->getCurrentRoute();
        
        if (empty($route)) {
            $this->core->logger->warning(sprintf('The requested url "%s" could not be resolved to a route.', $this->core->router->getRequestUrl()));
            return $this->returnRequestError();
        }
        
        // Send 404 error when no app name is defined in router
        if (empty($this->app)) {
            $this->core->logger->warning(sprintf('No app name for "%s" request', $this->core->router->getRequestUrl()));
            return $this->returnRequestError();
        }
        
        // We need this toll for some following string conversions
        $string = new CamelCase($this->app);
        
        // The apps classname is camelcased so the name from router match need to be converted.
        $this->app = $string->camelize();
        
        // Get app instance from app handler
        $app = $this->core->apps->getAppInstance($this->app);
        
        // Send 404 error when there is no app instance
        if (empty($app)) {
            $this->core->logger->warning(sprintf('No app name for "%s" request', $this->core->router->getRequestUrl()));
            return $this->returnRequestError();
        }
        
        // Call app event: Run()
        $event_result = $this->callAppEvent($app, 'Run');
        
        // Redirect from event?
        if (! empty($event_result)) {
            
            switch (true) {
                
                case ($event_result instanceof RedirectInterface):
                    return $this->handleRedirect($event_result);
                
                case ($event_result != $this->app):
                    
                    $redirect = new Redirect();
                    $redirect->setApp($event_result);
                    
                    return $this->handleRedirect($redirect);
            }
        }
        
        // Send 404 error when no app name is defined in router
        if (empty($this->controller)) {
            return $this->returnRequestError('ControllerName');
        }
        
        // Load controller object
        $string->setString($this->controller);
        $this->controller = $string->camelize();
        
        $controller = $app->getController($this->controller);
        
        // Send 404 when controller could not be loaded
        if ($controller == false) {
            return $this->returnRequestError();
        }
        
        // Send 404 error when no app name is defined in router
        if (empty($this->action)) {
            return $this->returnRequestError();
        }
        
        // Handle controller action
        $string->setString($this->action);
        $this->action = $string->camelize();
        
        if (! method_exists($controller, $this->action)) {
            return $this->returnRequestError();
        }
        
        // Prepare controller object
        $controller->setAction($this->action);
        $controller->setParams($this->params);
        
        // Set matched route to controller so it can be used for url creation inside controller
        $controller->setRoute($route);
        
        if ($this->ajax) {
            
            // Controller needs to know the output format
            $this->format = 'json';
            
            $this->core->http->header->contentType('application/json', 'utf-8');
            $this->core->http->header->noCache();
            
            // Run the controller action as ajax command and get the result
            $controller->run(false);
            
            // Controller flagged as redirect?
            $redirect = $controller->getRedirect();
            
            if (isset($redirect) && $redirect instanceof RedirectInterface) {
                
                $controller->clearRedirect();
                
                // Returns the redirect
                $this->handleRedirect($redirect);
            }
            
            // No redirect, so we are going to process all ajax commands and return the processed JSON as result
            
            /* @var $ajax \Core\Ajax\Ajax */
            $ajax = $this->core->di->get('core.ajax');
            
            // Handle messages
            $messages = $this->core->di->get('core.message.default')->getAll();
            
            if (! empty($messages)) {
                
                /* @var $msg \Core\Framework\Notification\Notification */
                foreach ($messages as $msg) {
                    
                    // Each message gets its own alert
                    
                    /* @var $alert \Core\Html\Bootstrap\Alert\Alert */
                    $alert = $this->core->di->get('core.html.factory')->create('Bootstrap\Alert\Alert');
                    $alert->setContext($msg->getType());
                    $alert->setDismissable($msg->getDismissable());
                    
                    // Fadeout message?
                    if ($this->core->config->get('Core', 'js.style.fadeout_time') > 0 && $msg->getFadeout()) {
                        $alert->html->addCss('fadeout');
                    }
                    
                    // Has this message an id which we can use as id for the alerts html element?
                    if (! empty($msg->getId())) {
                        $alert->html->setId($msg->getId());
                    }
                    
                    // At least append the message content
                    $alert->setContent($msg->getMessage());
                    
                    $ajax->addCommand(new \Core\Ajax\Commands\Dom\DomCommand($msg->getTarget(), $msg->getDisplayFunction(), $alert->build()));
                }
                
                // Reset messages stack because the got handled by the ajax processor
                $this->core->message->reset();
            }
            
            // @TODO Process possible asset js files to load!
            $js_objects = $this->core->di->get('core.asset')
                ->getAssetHandler('js')
                ->getObjects();
            
            if (! empty($js_objects)) {
                foreach ($js_objects as $js) {
                    if ($js->getType() == 'file') {
                        $ajax->addCommand(new \Core\Ajax\Commands\Act\GetScriptCommand($js->getContent()));
                    }
                }
            }
            
            // Run ajax processor
            $result = $ajax->process();
        } else {
            
            $result = $controller->run();
            
            $redirect = $controller->getRedirect();
            
            // is the result an redirect?
            if (isset($redirect) && $redirect instanceof RedirectInterface) {
                
                $controller->clearRedirect();
                
                // Returns the redirect
                $result = $this->handleRedirect($redirect);
            }
            
            $this->format = $controller->getFormat();
        }
        
        return $result;
    }

    /**
     * Handles a redirect by checking the redirect settings, runs an own Dispatcher with them and returns it's result.
     *
     * @param RedirectInterface $redirect
     *
     * @return mixed
     */
    private function handleRedirect(RedirectInterface $redirect)
    {
        // Do we have to clear POST data?
        if ($redirect->getClearPost()) {
            
            unset($_POST);
            
            $apps = $this->core->apps->getLoadedApps();
            
            foreach ($apps as $app) {
                
                $instance = $this->core->apps->getAppInstance($app);
                
                if (isset($instance->post)) {
                    $instance->post->clean();
                }
            }
        }
        
        // Preparing the redirect settings with default values when not set
        $app = $redirect->getApp() ?? $this->app;
        $controller = $redirect->getController() ?? $this->controller;
        $action = $redirect->getAction() ?? $this->action;
        $params = array_merge($redirect->getParams(), $this->params);
        
        // Redirecting dispatcher run
        $dispatcher = new Dispatcher($this->core);
        
        $dispatcher->setParams($params);
        $dispatcher->setApp($app);
        $dispatcher->setController($controller);
        $dispatcher->setAction($action);
        
        $dispatcher->setAjax($this->ajax);
        
        return $dispatcher->dispatch();
    }

    /**
     * Creates a language based error message according to the given status code
     *
     * Know about ajax requests and returns JSON encoded error command.
     * Sends given status code as http header.
     *
     * @param int $status_code
     *
     * @return string
     */
    private function returnRequestError(int $status_code = 404): string
    {
        $msg = $this->core->apps->getAppInstance('Core')->language->get('error.' . $status_code) . ' (' . $status_code . ')';
        
        if ($this->getAjax()) {
            
            $cmd = new \Core\Ajax\Commands\Dom\HtmlCommand('#content', $msg);
            
            $ajax = $this->core->di->get('core.ajax');
            $ajax->addCommand($cmd);
            
            $result = $ajax->process();
            
            return $result;
        }
        
        $this->core->http->header->sendHttpError($status_code);
        
        return $msg;
    }

    /**
     * Calls an existing event method of an app
     *
     * @param AbstractApp $app
     * @param string $event
     */
    private function callAppEvent(AbstractApp $app, string $event)
    {
        if (method_exists($app, $event)) {
            return call_user_func([
                $app,
                $event
            ]);
        }
    }

    /**
     * Mangaes and handles $_POST data by checking $_POST for sent session token and apply
     *
     * @todo Should emptied $_POST from redirect cause a reset of
     */
    private function managePost()
    {
        // Do only react on POST requests
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST)) {
            return;
        }
        
        if (! $this->core->di->exists('core.http.post')) {
            $this->core->di->mapService('core.http.post', '\Core\Http\Post\Post');
        }
        
        /* @var $post \Core\Http\Post\Post */
        $post = $this->core->di->get('core.http.post');
        
        // Setting the name of the session token that has to/gets sent with a form
        $post->setTokenName($this->core->config->get('Core', 'security.form.token'));
        
        // Validate posted token with session token
        if (! $post->validateCompareWithPostToken($this->core->di->get('core.security.form.token'))) {
            return;
        }
        
        // Some data cleanup
        $post->trimData();
        
        // Assingn app related post data to the corresponding app
        $post_data = $post->get();
        
        foreach ($post_data as $name => $data) {
            $app = $this->core->apps->getAppInstance($name);
            $app->post->set($data);
        }
    }
}
