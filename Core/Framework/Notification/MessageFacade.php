<?php
namespace Core\Framework\Notification;

use Core\Message\MessageHandlerInterface;
use Core\Message\MessageInterface;

/**
 * MessageFacade.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class MessageFacade
{

    /**
     *
     * @var MessageHandlerInterface
     */
    private $handler;

    /**
     * Constructor
     *
     * @param MessageHandlerInterface $handler
     */
    public function __construct(MessageHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Creates a by $type parameter specified message
     *
     * @param string $type
     * @param string $message
     *
     * @return Notification
     */
    private function createMessageObject(string $type, string $message): Notification
    {
        $msg = new Notification();

        $msg->setType($type);
        $msg->setMessage($message);

        $this->handler->add($msg);

        return $msg;
    }

    /**
     * Creates a clear message
     *
     * @param string $message
     *
     * @return Notification
     */
    public function clear(string $message): Notification
    {
        return $this->createMessageObject(Notification::TYPE_CLEAR, $message);
    }

    /**
     * Creates an info message
     *
     * @param string $message
     *
     * @return Notification
     */
    public function info(string $message): Notification
    {
        return $this->createMessageObject(Notification::TYPE_INFO, $message);
    }

    /**
     * Creates a danger message
     *
     * @param string $message
     *
     * @return Notification
     */
    public function danger(string $message): Notification
    {
        return $this->createMessageObject(Notification::TYPE_DANGER, $message);
    }

    /**
     * Creates a primary message
     *
     * @param string $message
     *
     * @return Notification
     */
    public function primary(string $message): Notification
    {
        return $this->createMessageObject(Notification::TYPE_DANGER, $message);
    }

    /**
     * Creates a success message
     *
     * @param string $message
     *
     * @return Notification
     */
    public function success(string $message): Notification
    {
        return $this->createMessageObject(Notification::TYPE_SUCCESS, $message);
    }

    /**
     * Creates a warning message
     *
     * @param string $message
     *
     * @return Notification
     */
    public function warning(string $message): Notification
    {
        return $this->createMessageObject(Notification::TYPE_WARNING, $message);
    }

    /**
     * Adds a message to the messages stack
     *
     * @param MessageInterface $msg
     */
    public function add(MessageInterface $msg)
    {
        $this->handler->add($msg);
    }

    /**
     * Resets the message stack
     */
    public function reset()
    {
        $this->handler->clear();
    }
}

