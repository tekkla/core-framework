<?php
namespace Core\Framework\Page\Body\Message;

use Core\Framework\Page\Body\AbstractBodyElement;
use Core\Message\MessageInterface;

/**
 * Message.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Message extends AbstractBodyElement
{

    /**
     *
     * @var array
     */
    private $messages = [];

    public function addMessage(MessageInterface $message)
    {
        $this->messages[] = $message;
    }

    public function getAll(): array {
        return $this->messages;
    }
}

