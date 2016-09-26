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
     *
     * @param array $permissions
     */
    public function set(array $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     *
     * @param string $permission
     */
    public function add(string $permission)
    {
        $this->permissions[$permission] = $permission;
    }

    /**
     *
     * @param string $permissiom
     *
     * @return bool
     */
    public function exists(string $permissiom): bool
    {
        return isset($this->permissions[$permissiom]);
    }
}

