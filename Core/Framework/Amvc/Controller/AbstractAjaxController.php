<?php
namespace Core\Framework\Amvc\Controller;

use Core\Ajax\Commands\CommandInterface;
use Core\Ajax\Commands\Dom\DomCommand;

/**
 * AbstractAjaxController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractAjaxController extends AbstractController
{

    /**
     * DomCommand of controller
     *
     * This will be used by controller while running the controller action.
     * Access this property to alter command properties.
     * Note: Args will be replaced by run() method!
     *
     * @var \Core\Ajax\Commands\Dom\AbstractDomCommand
     */
    protected $ajax;

    /**
     *
     * @var string
     */
    private $selector = '#content';

    /**
     *
     * @var array
     */
    private $before_commands = [];

    /**
     *
     * @var array
     */
    private $after_commands = [];

    /**
     * Sets default selector
     *
     * @param string $selector
     *            Selector as it schould be used in $(selector).method(); when none is set in controllerfunction
     *
     * @throws AjaxControllerException
     */
    protected function setDefaultSelector(string $selector)
    {
        if (empty($selector)) {
            Throw new AjaxControllerException('Empty default selectors as nott permitted');
        }

        $this->selector = $selector;
    }

    /**
     * Returns the default selector
     *
     * @return string
     */
    protected function getDefaultSelector(): string
    {
        return $this->selector;
    }

    /**
     * Adds a new before ajax command
     *
     * Before will be added to the ajax command stack before the content of the controller function is added.
     *
     * @param CommandInterface $command
     */
    protected function addBeforeCommand(CommandInterface $command)
    {
        $this->before_commands[] = $command;
    }

    /**
     * Adds a new after ajax command
     *
     * After commands will be added to the ajax command stack after the content of the controller function is added.
     *
     * @param CommandInterface $command
     */
    protected function addAfterCommand(CommandInterface $command)
    {
        $this->after_commands[] = $command;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\Amvc\Controller\AbstractController::run()
     */
    public function run(bool $only_content = true)
    {
        $ajax = $this->app->core->di->get('core.ajax');

        $cmd = new DomCommand($this->selector, 'html', '--empty--');

        // Prepare a fresh ajax command object
        $this->ajax = $cmd;

        $content = parent::run();

        if ($only_content) {
            return $content;
        }

        if ($content !== false) {

            $this->ajax->setArgs($content);
            $this->ajax->setId(get_called_class() . '::' . $this->action);

            $this->before_commands[] = $this->ajax;
        }

        $commands = array_merge($this->before_commands, $this->after_commands);

        // Add all other controller created commanda to ajax command stack
        foreach ($commands as $cmd) {
            $ajax->addCommand($cmd);
        }
    }
}

