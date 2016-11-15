<?php
namespace Core\Framework\Amvc\App;

/**
 * Permissions.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Permissions
{

    /**
     *
     * @var array
     */
    private $permissions = [];

    /**
     * Sets an array of permissions by replacing already existing permissions
     *
     * @param array $permissions
     */
    public function set(array $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * Adds a permission
     *
     * @param string $permission
     */
    public function add(string $permission)
    {
        $this->permissions[$permission] = $permission;
    }

    /**
     * Checks for existing permission
     *
     * @param string $permissiom
     *
     * @return bool
     */
    public function exists(string $permissiom): bool
    {
        return isset($this->permissions[$permissiom]);
    }

    /**
     * Returns all set app permissions
     *
     * @return array
     */
    public function get(): array
    {
        return $this->permissions;
    }
}
