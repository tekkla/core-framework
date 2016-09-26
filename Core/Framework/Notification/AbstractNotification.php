<?php
namespace Core\Framework\Notification;

use Core\Message\MessageInterface;

/**
 * AbstractNotification.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractNotification implements NotificationInterface
{

    /**
     *
     * @var string
     */
    private $message = '';

    /**
     *
     * @var string
     */
    private $type = 'info';

    /**
     *
     * @var boolean
     */
    private $fadeout = true;

    /**
     *
     * @var boolean
     */
    private $dismissable = true;

    /**
     *
     * @var string
     */
    private $target = '#core-message';

    /**
     *
     * @var string
     */
    private $function = 'append';

    /**
     *
     * @var string
     */
    private $id = '';

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Message\MessageInterface::setMessage()
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Message\MessageInterface::getMessage()
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Message\MessageInterface::getId()
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Core\Message\MessageInterface::setId()
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function setType(string $type)
    {
        $types = [
            'primary',
            'success',
            'info',
            'warning',
            'danger',
            'clear'
        ];

        if (!in_array($type, $types)) {
            Throw new NotificationException(sprintf('Type "%s" is a not valid messagetype.', $type));
        }

        $this->type = $type;
    }


    public function getType(): string
    {
        return $this->type;
    }

    public function setFadeout(bool $fadeout)
    {
        $this->fadeout = $fadeout;
    }

    public function getFadeout(): bool
    {
        return $this->fadeout;
    }

    public function setDismissable(bool $dismissable)
    {
        $this->dismissable = $dismissable;
    }

    public function getDismissable(): bool
    {
        return $this->dismissable;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target)
    {
        $this->target = $target;
    }

    public function getDisplayFunction(): string
    {
        return $this->function;
    }

    public function setDisplayFunction(string $function = 'append')
    {
        $this->function = $function;
    }
}
