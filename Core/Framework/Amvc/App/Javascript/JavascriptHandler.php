<?php
namespace Core\Framework\Amvc\App\Javascript;

use Core\Asset\Javascript\JavascriptObject;
use Core\Asset\Javascript\JavascriptObjectInterface;

/**
 * JavascriptHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016-2017
 * @license MIT
 */
class JavascriptHandler implements JavascriptHandlerInterface, \IteratorAggregate
{

    /**
     *
     * @var array
     */
    private $objects = [];

    /**
     *
     * @var array
     */
    private static $used_files = [];

    /**
     *
     * @var int
     */
    private static $filecounter = 0;

    /**
     *
     * {@inheritdoc}
     *
     * @see JavascriptHandlerInterface::add($js)
     */
    public function add(JavascriptObjectInterface $js)
    {
        $this->objects[] = $js;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see JavascriptHandlerInterface::get()
     */
    public function get(): array
    {
        return $this->objects;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see JavascriptHandlerInterface::getType($type)
     */
    public function getType(string $type): array
    {
        return $this->objects[$type] ?? [];
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see JavascriptHandlerInterface::removeType($type)
     */
    public function removeType(string $type): bool
    {
        if (isset($this->objects[$type])) {
            unset($this->objects[$type]);
            return true;
        }

        return false;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see JavascriptHandlerInterface::file($url, $defer, $external)
     */
    public function file(string $url, bool $defer = true, bool $external = false): JavascriptObjectInterface
    {
        // Do not add files already added
        if (in_array($url, self::$used_files)) {
            Throw new JavascriptException(sprintf('Url "%s" is already set as included js file.', $url));
        }

        $dt = debug_backtrace();

        self::$used_files[self::$filecounter . '-' . $dt[1]['function']] = $url;
        self::$filecounter++;

        $object = new JavascriptObject();

        $object->setType($object::TYPE_FILE);
        $object->setContent($url);
        $object->setExternal($external);
        $object->setDefer($defer);

        $this->add($object);

        return $object;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see JavascriptHandlerInterface::script($script, $defer)
     */
    public function script(string $script, bool $defer = true): JavascriptObjectInterface
    {
        $object = new JavascriptObject();

        $object->setType($object::TYPE_SCRIPT);
        $object->setContent($script);
        $object->setDefer($defer);

        $this->add($object);

        return $object;
    }

    /**
     * Creats a ready javascript object
     *
     * @param string $script
     * @param bool $defer
     *
     * @return JavascriptObjectInterface
     */
    public function ready(string $script, bool $defer = true): JavascriptObjectInterface
    {
        $object = new JavascriptObject();

        $object->setType($script::TYPE_READY);
        $object->setContent($script);
        $object->setDefer($defer);

        $this->add($object);

        return $object;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see JavascriptHandlerInterface::block($script, $defer)
     */
    public function block(string $script, bool $defer = true): JavascriptObjectInterface
    {
        $object = new JavascriptObject();

        $object->setType($object::TYPE_BLOCK);
        $object->setContent($object);
        $object->setDefer($defer);

        $this->add($object);

        return $object;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see JavascriptHandlerInterface::variable($name, $value, $is_string, $defer)
     */
    public function variable(string $name, $value, bool $is_string = false, bool $defer = true): JavascriptObjectInterface
    {
        if ($is_string == true) {
            $value = '"' . $value . '"';
        }

        $object = new JavascriptObject();

        $object->setType($object::TYPE_VAR);
        $object->setContent([
            $name,
            $value
        ]);
        $object->setDefer($defer);

        $this->add($object);

        return $object;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }
}
