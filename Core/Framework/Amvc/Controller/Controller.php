<?php
namespace Core\Framework\Amvc\Controller;

use Core\Framework\Amvc\AbstractMvc;
use Core\Framework\Amvc\View\View;
use Core\Message\Message;
use Core\Html\FormDesigner\FormDesigner;
use Core\Ajax\Ajax;
use Core\Ajax\Commands\Dom\DomCommand;
use Core\Toolbox\Strings\CamelCase;

/**
 * Controller.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Controller extends AbstractMvc
{

    /**
     *
     * @var View
     */
    private $view;

    /**
     * Var storage to be used in view
     *
     * @var array
     */
    private $vars = [];

    /**
     * Type of class
     *
     * @var String
     */
    protected $type = 'Controller';

    /**
     * Signals that the corresponding view will be rendered
     *
     * @var Boolean
     */
    protected $render = true;

    /**
     * Stores the name of the route that lead to this controller
     *
     * @var string
     */
    protected $route;

    /**
     * Storage for access rights
     *
     * @var array
     */
    protected $access = [];

    /**
     * Redirection definition
     *
     * @var Redirect
     */
    protected $redirect;

    /**
     * Controls the output format
     *
     * @var string
     */
    protected $format = 'html';

    /**
     * Stores the controller bound Model object.
     * Is false when controller has no model.
     *
     * @var Model
     */
    public $model = false;

    /**
     * Sets the name of the route that lead to this controller
     *
     * @param string $route
     */
    public function setRoute(string $route)
    {
        $this->route = $route;
    }

    /**
     * Returns the name of the route that lead to this controller
     *
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Sets the output format
     *
     * @param string $format
     */
    public function setFormat(string $format)
    {
        // Control rendering by requested output format
        $no_render_format = [
            'json',
            'xml',
            'css',
            'js',
            'file'
        ];

        if (in_array($format, $no_render_format)) {
            $this->render = false;
        }

        $this->format = $format;
    }

    /**
     * Returns the output format
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Returns a set Redirect object
     *
     * @return \Core\Framework\Amvc\Controller\Redirect
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Deletes a set RedirectObject
     */
    public function clearRedirect()
    {
        unset($this->redirect);
    }

    /**
     * Runs the requested controller action
     *
     * This is THE run method for each controller in each app. When used to resolve
     * requests, no action or parameter need to be set. Both will be autodiscovered
     * by requesting the needed data from the requesthandler. The method can also be
     * used to get a partial result when using a controller from within a controller.
     * In this case you should provide the action an parameters needed to get the
     * wanted result.
     *
     * @throws ControllerException
     *
     * @return boolean bool|string
     */
    public function run()
    {
        // If accesscheck failed => stop here and return false!
        if ($this->checkControllerAccess() === false) {
            $this->app->core->logger->warning(sprintf('Missing permission for ressource %s.%s.%s', $this->app->getName(), $this->getName(), $this->action));
            Throw new ControllerException($this->app->language->get('access.missing_userrights'));
            return false;
        }

        // Init return var with boolean false as default value. This default
        // prevents from running the views render() method when the controller
        // action is stopped manually by using return.
        $return = false;

        // a little bit of reflection magic to pass request param into controller func
        $return = $this->di->invokeMethod($this, $this->action, $this->params);

        // Do we have a result?
        if (isset($return)) {

            // Boolean false result means to stop work for controller
            if ($return == false) {
                return false;
            }
        }

        // Render the view and return the result
        if ($this->render === true) {

            // Create view instance if not alredy done
            if (!$this->view instanceof View) {
                $this->view = $this->app->getView($this->name);
            }

            $this->view->setAction($this->action);
            $this->view->setParams($this->params);
            $this->view->setVars($this->vars);

            if (isset($this->logger)) {
                $this->view->setLogger($this->logger);
            }

            // Check if there still no view object
            if (empty($this->view)) {
                Throw new ControllerException(sprintf('A result has to be rendered but "%sView" does not exist.', $this->name));
            }

            // Render
            $content = $this->view->render();

            // Run possible onEmpty event of app on no render result
            if (empty($content) && method_exists($this->app, 'onEmpty')) {
                $content = $this->app->onEmpty();
            }

            return $content;
        }
        else {

            // Without view rendering we return the return value send from called controller action
            return $return;
        }
    }

    /**
     * Ajax method to send the result of an action as ajax html command
     *
     * This works similiar to the run() method and even uses it. The difference is that the renderesult is wrapped by an
     * ajax command object. This ajax command can be controlled by setting the wanted parameters via $this->ajax->...
     *
     * @param string $selector
     *            Optional jQuery selector to html() the result.
     *            Can be overridden by setAjaxTarget() method.
     *            Default: '#content'
     */
    public function ajax($selector = '#content')
    {
        $this->ajax = $this->app->core->di->get('core.ajax');

        $cmd = new DomCommand($selector, 'html', '--empty--');

        // Prepare a fresh ajax command object
        $this->ajax_cmd = $cmd;

        // Get content from Controller::run()
        $content = $this->run();

        if ($content !== false) {

            $this->ajax_cmd->setArgs($content);
            $this->ajax_cmd->setId(get_called_class() . '::' . $this->action);

            if (empty($this->ajax_cmd->getSelector()) && !empty($selector)) {
                $this->ajax_cmd->setSelector($selector);
            }

            $this->ajax->addCommand($this->ajax_cmd);
        }
    }

    /**
     * Redirects from one action to another
     *
     * @param string|array $target
     *            Name of redirectaction to call within this controller or an array aof app, controller and action name
     *            which will be called as redirect.
     * @param array $params
     *            Optional key => value array of params to pass into redirect action (Default: empty array)
     * @param bool $clear_post
     *            Optional flag to control emptying posted data (Default: true)
     */
    protected function redirect($app = null, $controller = null, $action = null, array $params = [], bool $clear_post = true)
    {
        // No params mean to use the current
        if (empty($params)) {
            $params = $this->params;
        }

        $redirect = new Redirect();

        // Set params at first because the params gets scanned for ACA data which would override manually set ACA data
        $redirect->setParams($params);

        // Now set manual ACA data
        $redirect->setApp(empty($app) ? $this->app->getName() : $app);
        $redirect->setController(empty($controller) ? $this->getName() : $controller);
        $redirect->setAction(empty($action) ? $this->getAction() : $action);

        // Clear $_POST data before redirect?
        $redirect->setClearPost($clear_post);

        $this->redirect = $redirect;
    }

    /**
     * Does an urlredirect but cares about what kind (ajax?) of request was send.
     *
     * @param Url|string $url
     */
    protected function doRefresh($url)
    {
        if ($this->app->core->router->isAjax()) {
            $cmd = $this->ajax->createActCommand();

            $cmd->setFunction('refresh');
            $cmd->setArgs($url);

            $this->ajax->addCommand($cmd);
        }
        else {
            $this->redirectExit($url);
        }
    }

    /**
     * Checks the controller access of the user
     *
     * This accesscheck works on serveral levels.
     *
     * Level 0 - App: Tries to check access on possible app wide access function.
     * Level 1 - Controller: Tries to check access by looking for access setting in the controller itself.
     *
     * @param bool $force
     *            Set this to true if you want to force a brutal stop
     *
     * @return boolean
     */
    protected function checkControllerAccess($force = false): bool
    {
        // Is there an global access method in the app main class to call?
        if (method_exists($this->app, 'Access') && $this->app->Access() === false) {
            return false;
        }

        // No ACL
        if (empty($this->access)) {
            return true;
        }

        $perm = [];

        // Global access for all actions?
        if (array_key_exists('*', $this->access)) {
            if (!is_array($this->access['*'])) {
                $this->access['*'] = (array) $this->access['*'];
            }

            $perm += $this->access['*'];
        }
        else {

            // ACL exists but action not in it? This means to grant access.
            if (!array_key_exists($this->action, $this->access)) {
                return true;
            }
        }

        // Actions access set?
        if (isset($this->access[$this->action])) {
            if (!is_array($this->access[$this->action])) {
                $this->access[$this->action] = (array) $this->access[$this->action];
            }

            $perm += $this->access[$this->action];
        }

        // Check the permissions against the current user
        if ($perm) {
            return $this->checkAccess($perm);
        }

        return false;
    }

    /**
     * Publish a value to the view
     *
     * @param string|array $arg1
     *            Name of var or list of vars in an array
     * @param mixed $arg2
     *            Optional value to be ste when $arg1 is the name of a var
     *
     * @throws ControllerException
     *
     * @return Controller
     */
    protected function setVar($arg1, $arg2 = null)
    {
        // One argument has to be an assoc array
        if (!isset($arg2) && is_array($arg1)) {
            foreach ($arg1 as $var => $value) {
                $this->vars[$var] = $this->varHandleObject($value);
            }
        }
        elseif (isset($arg2)) {
            $this->vars[$arg1] = $this->varHandleObject($arg2);
        }
        else {
            Throw new ControllerException('The vars to set are not correct.', 1001);
        }

        return $this;
    }

    private function varHandleObject($val)
    {
        // Handle objects
        if (is_object($val)) {

            switch (true) {

                // Handle buildable objects
                case method_exists($val, 'build'):
                    $val = $val->build();
                    break;

                // Handle all other objects
                default:
                    $val = get_object_vars($val);
                    break;
            }
        }

        return $val;
    }

    /**
     * Shorthand method for a FormDesigner instance with auto attached model
     *
     * @return FormDesigner
     */
    protected function getFormDesigner($id = '')
    {
        /* @var $fd \Core\Html\FormDesigner\FormDesigner */
        $fd = new FormDesigner($this->app->getName(true));

        // Generate form id when id is not provided
        if (!$id) {

            $pieces = [];

            $string = new CamelCase($this->app->getName());
            $pieces[] = $string->uncamelize();

            $string->setString($this->name);
            $pieces[] = $string->uncamelize();

            // get calling method name
            $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

            if (isset($dbt[1]['function'])) {
                $string->setString($dbt[1]['function']);
                $pieces[] = $string->uncamelize();
            }

            $id = implode('-', $pieces);
        }

        if ($id) {
            $fd->setId($id);
        }

        // Create forms eaction url
        if (isset($this->route)) {
            $fd->html->setAction($this->app->url($this->route, $this->params));
        }

        // Set session token
        $fd->setToken($this->di->get('core.security.form.token.name'), $this->di->get('core.security.form.token'));

        return $fd;
    }

    /**
     * Sets the selector name to where the result is ajaxed.
     *
     * @param string $target
     *
     * @return \Core\Framework\Amvc\Controller
     */
    protected function setAjaxTarget($target)
    {
        if (!empty($this->ajax_cmd)) {
            $this->ajax_cmd->setSelector($target);
        }

        return $this;
    }

    /**
     * Sets the function to use when result is returned.
     *
     * @param string $function
     *
     * @return \Core\Framework\Amvc\Controller
     */
    protected function setAjaxFunction($function)
    {
        if (!empty($this->ajax_cmd)) {
            $this->ajax_cmd->setFunction($function);
        }

        return $this;
    }

    /**
     * Returns an empty ajax command object
     *
     * @param string $command_name
     *            Name of command to get. Default: Dom\Html
     *
     * @return \Core\Ajax\AjaxCommand
     */
    public function getAjaxCommand($command_name = 'Dom\Html')
    {
        return $this->ajax->createCommand($command_name);
    }

    /**
     * Redirect function to make sure the browser doesn't come back and repost the form data
     *
     * @param string $location
     *            Location we redirtect to
     * @param bool $permanent
     *            Is this a permanent redirection?
     */
    protected function redirectExit($location = '', $permanent = false)
    {
        // No view rendering!
        $this->render = false;

        if (empty($location)) {
            $location = BASEURL;
        }

        if (preg_match('~^(ftp|http)[s]?://~', $location) == 0 && substr($location, 0, 6) != 'about:') {
            $location = BASEURL . $location;
        }

        $_SESSION['Core']['redirect'] = [
            'location' => $location,
            'permanent' => $permanent
        ];
    }

    /**
     * Dummy method for those who forget to create such method in their controller.
     *
     * @todo Create message in log, that an Index() method should be created in controller and that this is only to
     *       prevent simplest errors
     */
    public function Index()
    {}
}
