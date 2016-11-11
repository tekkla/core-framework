<?php
namespace Apps\Core\Controller;

use Apps\Core\CoreException;

/**
 * CreateEditboxTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
trait CreateEditboxTrait {

    protected function getEditbox($form, $caption, $actions)
    {

        /* @var $editbox \Core\Html\Controls\Editbox\Editbox */
        $editbox = $this->html->create('Controls\Editbox\Editbox');
        $editbox->setForm($form);
        $editbox->setCaption($caption);

        $this->createEditboxActions($editbox, $actions);

        return $editbox;
    }

    /**
     *
     * @param \Core\Html\Controls\Editbox\Editbox $editbox
     * @param array $actions
     */
    protected function createEditboxActions($editbox, array $actions, bool $context = false)
    {
        foreach ($actions as $settings) {

            if (empty($settings['type'])) {
                Throw new CoreException('No type set for Editbox action.');
            }

            $action = $editbox->createAction($settings['type'], $settings['text']);

            if (!empty($settings['url'])) {
                $action->setHref($settings['url']);
            }

            if (!empty($settings['icon'])) {
                $icon = $this->html->create('Fontawesome\Icon');
                $icon->setIcon($settings['icon']);

                $action->setIcon($icon);
            }

            if (!empty($settings['confirm'])) {
                $action->setConfirm($settings['confirm']);
            }

            if (!empty($settings['ajax'])) {
                $action->setAjax($settings['ajax']);
            }
        }
    }
}

