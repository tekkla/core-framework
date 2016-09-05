<?php
namespace Core\Framework;

use Core\Framework\Amvc\Controller\Redirect;
use Core\Framework\Amvc\Controller\RedirectInterface;
use Core\Http\Header\HeaderHandler;
use Core\Toolbox\Strings\CamelCase;
use Core\Framework\Amvc\App\AbstractApp;
use Core\Framework\Amvc\App\Post;

/**
 * Dispatcher.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
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
     * @param HeaderHandler $header
     */
    public function __construct(Core $core)
    {
        $this->core = $core;
    }

    /**
     *
     * @param bool $ajax
     */
    public function setAjax(bool $ajax)
    {
        $this->ajax = $ajax;
    }

    /**
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
     *
     * @param boolean $match_url
     *            Optional boolean flag to supress route matching. This is important for situations where the Run()
     *            method of an app, maybe altered the values for app, controller and/or action, has been called.
     *            (Default: true)
     */
    public function dispatch()
    {
        $this->managePost();

        // Send 404 error when no app name is defined in router
        if (empty($this->app)) {
            return $this->send404('AppName');
        }

        // We need this toll for some following string conversions
        $string = new CamelCase($this->app);

        // The apps classname is camelcased so the name from router match need to be converted.
        $app_name = $string->camelize();

        // Get app instance from app handler
        $app = $this->core->apps->getAppInstance($app_name);

        // Send 404 error when there is no app instance
        if (empty($app)) {
            return $this->send404('AppObject');
        }

        // Call app event: Run()
        $event_result = $this->callAppEvent($app, 'Run');

        // Redirect from event?
        if (!empty($event_result) && $event_result != $app_name) {

            $redirect = new Redirect();
            $redirect->setApp($event_result);

            return $this->handleRedirect($redirect);
        }

        // Load controller object
        $string->setString($this->controller);
        $controller_name = $string->camelize();

        $controller = $app->getController($controller_name);

        // Send 404 when controller could not be loaded
        if ($controller == false) {
            return $this->send404('Controller::' . $controller_name);
        }

        // Handle controller action
        $string->setString($this->action);
        $action = $string->camelize();

        if (!method_exists($controller, $action)) {
            return $this->send404('Action::' . $action);
        }

        // Prepare controller object
        $controller->setAction($action);
        $controller->setParams($this->params);
        $controller->setRoute($this->core->router->getCurrentRoute());

        if ($this->ajax) {

            // Controller needs to know the output format
            $this->format = 'json';

            $this->core->http->header->contentType('application/json', 'utf-8');
            $this->core->http->header->noCache();

            // Run the controller action as ajax command and get the result
            $result = $controller->ajax();

            // is the result an redirect?
            if ($result instanceof RedirectInterface) {

                // Returns the redirect
                return $this->handleRedirect($result);
            }

            // No redirect, so we are going to process all ajax commands and return the processed JSON as result

            /* @var $ajax \Core\Ajax\Ajax */
            $ajax = $this->core->di->get('core.ajax');

            // Handle messages
            $messages = $this->core->di->get('core.message.default')->getAll();

            if (!empty($messages)) {

                /* @var $msg \Core\Message\Message */
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
                    if (!empty($msg->getId())) {
                        $alert->html->setId($msg->getId());
                    }

                    // At least append the message content
                    $alert->setContent($msg->getMessage());

                    $ajax->addCommand(new \Core\Ajax\Commands\Dom\DomCommand($msg->getTarget(), $msg->getDisplayFunction(), $alert->build()));
                }
            }

            // @TODO Process possible asset js files to load!
            $js_objects = $this->core->di->get('core.asset')
                ->getAssetHandler('js')
                ->getObjects();

            if (!empty($js_objects)) {
                foreach ($js_objects as $js) {
                    if ($js->getType() == 'file') {
                        $ajax->addCommand(new \Core\Ajax\Commands\Act\JQueryGetScriptCommand($js->getContent()));
                    }
                }
            }

            // Run ajax processor
            $result = $ajax->process();
        }
        else {

            $result = $controller->run();

            // is the result an redirect?
            if ($result instanceof RedirectInterface) {

                // Returns the redirect
                return $this->handleRedirect($result);
            }
        }

        $this->core->router->setFormat($controller->getFormat());

        return $result;
    }

    /**
     * Handles a redirect by checking the redirects settings, runs an own Dispatcher with them and returns it's result.
     *
     * @param RedirectInterface $redirect
     *
     * @return mixed
     */
    private function handleRedirect(RedirectInterface $redirect)
    {
        if ($redirect->getClearPost()) {

            $_POST = [];

            $apps = $this->core->apps->getLoadedApps();

            foreach ($apps as $app) {
                if ($app->post instanceof Post) {
                    $app->post->clean();
                }
            }
        }

        $app = $redirect->getApp() ?? $this->app;
        $controller = $redirect->getController() ?? $this->controller;
        $action = $redirect->getAction() ?? $this->action;
        $params = array_merge($redirect->getParams(), $this->params);

        $dispatcher = new Dispatcher($this->core);
        $dispatcher->setApp($app);

        var_dump($controller);

        $dispatcher->setController($controller);
        $dispatcher->setAction($action);
        $dispatcher->setParams($params);
        $dispatcher->setAjax($this->ajax);

        return $dispatcher->dispatch();
    }

    private function send404($stage = 'not set')
    {
        $msg = $stage . ' - Page not found';

        if ($this->getAjax()) {

            $cmd = new \Core\Ajax\Commands\Dom\HtmlCommand('#content', $msg);

            $ajax = $this->core->di->get('core.ajax');
            $ajax->addCommand($cmd);

            $result = $ajax->process();

            return $result;
        }

        $this->core->http->header->sendHttpError(404);

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

        if (!$this->core->di->exists('core.http.post')) {
            $this->core->di->mapService('core.http.post', '\Core\Http\Post\Post');
        }

        /* @var $post \Core\Http\Post\Post */
        $post = $this->core->di->get('core.http.post');

        // Setting the name of the session token that has to/gets sent with a form
        $post->setTokenName($this->core->config->get('Core', 'security.form.token'));

        // Validate posted token with session token
        if (!$post->validateCompareWithPostToken($this->core->di->get('core.security.form.token'))) {
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

