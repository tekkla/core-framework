<?php
namespace Core\Framework\Amvc\App;

/**
 * Post.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Post
{

    /**
     *
     * @var array
     */
    public $data = [];

    /**
     *
     * @var AbstractApp
     */
    private $app;

    /**
     * Constructor
     *
     * @param AbstractApp $app
     */
    public function __construct(AbstractApp $app)
    {
        $this->app = $app;
    }

    /**
     * Sets data
     *
     * @param array $data
     */
    public function set(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns data
     *
     * @return array
     */
    public function get(): array
    {
        return $this->data;
    }

    /**
     * Checks for empty data
     */
    public function empty()
    {
        return empty($this->data);
    }

    /**
     * Cleans all data and unsets related data in $_POST
     */
    public function clean()
    {
        $this->data = [];
        unset($_POST[$this->app->getName(true)]);
    }
}

