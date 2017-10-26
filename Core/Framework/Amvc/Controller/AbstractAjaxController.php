<?php
namespace Core\Framework\Amvc\Controller;

use Core\Ajax\Commands\Dom\DomCommand;
use Core\Ajax\Commands\Dom\DomCommandInterface;

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
     * Adds a ajax command to the ajax handler commandstack
     *
     * @param DomCommandInterface $command
     */
    protected function addCommand(DomCommandInterface $command)
    {
        $this->app->core->di->get('core.ajax')->addCommand($command);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Framework\Amvc\Controller\AbstractController::run()
     */
    public function run(bool $only_content = true)
    {

        // Prepare a fresh ajax command object
        $this->ajax = new DomCommand($this->selector, 'html', '--empty--');

        $content = parent::run();

        if ($only_content) {
            return $content;
        }

        if ($content !== false) {
            $this->ajax->setArgs($content);
            $this->addCommand($this->ajax);
        }
    }
}
