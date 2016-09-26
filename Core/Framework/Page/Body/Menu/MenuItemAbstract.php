<?php
namespace Core\Framework\Page\Body\Menu;

/**
 * MenuItemAbstract.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class MenuItemAbstract
{

    /**
     * Items childs.
     *
     * @var array
     */
    private $items = [];

    /**
     * Method to add a menu item as child
     *
     * @param MenuItem $menu_item
     *
     * @return MenuItem Reference to the added menu item.
     */
    public function &addItem(MenuItem $menu_item)
    {
        $this->items[$menu_item->getName()] = $menu_item;

        return $this->items[$menu_item->getName()];
    }

    /**
     * Creates new menuitem and adds it to items list
     *
     * @param string $name
     *            Internal name of item
     * @param string $text
     *            Text to show
     * @param string $url
     *            Optional url for linking
     *
     * @return MenuItem Reference to the created child item.
     */
    public function &createItem($name, $text, $url = '')
    {
        $menu_item = new MenuItem();

        $menu_item->setName($name);
        $menu_item->setText($text);

        if ($url) {
            $menu_item->setUrl($url);
        }

        $this->items[$name] = $menu_item;

        return $this->items[$name];
    }

    /**
     * Checks for exisitng childs and returns a boolean result.
     *
     * @return boolean
     */
    public function isParent()
    {
        return ! empty($this->items);
    }

    /**
     * Returns one or all child items
     *
     * @return array
     */
    public function getItems($name = '')
    {
        if (empty($name)) {
            return $this->items;
        }

        if (! isset($this->items[$name])) {
            return false;
        }

        return $this->items[$name];
    }
}
