<?php
namespace Core\Framework\Notification;

use Core\Message\MessageInterface;

/**
 * NotificationInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface NotificationInterface extends MessageInterface
{

    /**
     *
     * @var string
     */
    const TYPE_PRIMARY = 'primary';

    /**
     *
     * @var string
     */
    const TYPE_SUCCESS = 'success';

    /**
     *
     * @var string
     */
    const TYPE_INFO = 'info';

    /**
     *
     * @var string
     */
    const TYPE_WARNING = 'warning';

    /**
     *
     * @var strng
     */
    const TYPE_DANGER = 'danger';

    /**
     *
     * @var string
     */
    const TYPE_CLEAR = 'clear';

    /**
     * Sets notification type
     *
     * @param string $type
     *            Type can be primary, success, info, warning, danger, or clear
     */
    public function setType(string $type);

    /**
     * Returns set notification type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Sets flag to fadeout message after some time
     *
     * @param bool $fadeout
     */
    public function setFadeout(bool $fadeout);

    /**
     * Returns set fadout flag
     *
     * @return bool
     */
    public function getFadeout(): bool;

    /**
     * Sets flag that makes message dismissable
     *
     * @param bool $dismissable
     */
    public function setDismissable(bool $dismissable);

    /**
     * Returns set dismissable flag
     *
     * @return bool
     */
    public function getDismissable(): bool;

    /**
     * Sets message target (ajax)
     *
     * @param string $target
     */
    public function setTarget(string $target);

    /**
     * Returns set message target (ajax)
     *
     * @return string
     */
    public function getTarget(): string;

    /**
     * Sets display function (ajax)
     *
     * @param string $function
     */
    public function setDisplayFunction(string $function = 'append');

    /**
     * Returns set display function (ajax)
     *
     * @return string
     */
    public function getDisplayFunction(): string;
}

