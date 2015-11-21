<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, communityii, 2014 - 2015
 * @package communityii/yii2-user
 * @version 1.0.0
 *
 * @author derekisbusy https://github.com/derekisbusy
 * @author kartik-v https://github.com/kartik-v
 */

namespace comyii\common\base;

use Closure;
use comyii\common\traits\ArrayContainer;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class ArrayModule implements array access.
 * 
 * @package comyii\common\base
 */
class ArrayModule extends Module
{

    use ArrayContainer;
    
    
    /**
     * Returns the component instance with the specified ID.
     *
     * @param string $id component ID (e.g. `db`).
     * @param boolean $throwException whether to throw an exception if `$id` is not registered with the locator before.
     * @return object|null the component of the specified ID. If `$throwException` is false and `$id`
     * is not registered before, null will be returned.
     * @throws InvalidConfigException if `$id` refers to a nonexistent component ID
     * @see has()
     * @see set()
     */
    public function get($id, $throwException = true, $key = null)
    {
        if ($key !== null) {
            if (is_object($this->{$this->_containerName}[$key][$id]) && !$this->{$this->_containerName}[$key][$id] instanceof Closure) {
                return $this->{$this->_containerName}[$key][$id];
            } elseif ($key == $this->getCurrent() && isset($this->_components[$id])) {
                return isset($this->_components[$id]);
            } else {
                $config = [];
                // default definitions
                if (isset($this->_defaults[$id])) {
                    $config = $this->_defaults[$id];
                }
                // config components
                if (isset($this->_config[$id])) {
                    $config = array_replace_recursive($config, $this->_config[$id]);
                }
                // module types
                if (isset($this->_{$this->_containerName}[$key][$id])) {
                    $config = array_replace_recursive($config, $this->{$this->_containerName}[$key][$id]);
                }
                if (!empty($config)) {
                    $this->set($id, $config);
//                    if (isset($this->yii[$id])) {
//                        return $this->{$this->_containerName}[$key][$id] = Yii::$app->get($id);
//                    }
                    return $this->{$this->_containerName}[$key][$id] = self::createObject($config);
                }
            }
        }
        Module::get($id, $throwException);
    }

    public function offsetGet($offset)
    {
        if (isset($this->{$this->_containerName}[$offset])) {
            if (is_object($this->{$this->_containerName}[$offset])) {
                return $this->{$this->_containerName}[$offset];
            }
            return new ArrayKey($this, $offset);
        } else {
            throw new InvalidConfigException('offset not found in '.__CLASS__);
        }
    }

    protected function getConfig($name)
    {
        $config = Module::getConfig($name);
        // module container
        if (isset($this->_containerName) && property_exists($this, $this->_containerName)) {
            $current = $this->getCurrent();
            if ($current && isset($this->{$this->_containerName}[$current][$name])) {
                $config = array_replace_recursive($config, $this->{$this->_containerName}[$current][$name]);
            }
        }
        return $config;
    }

    public function hasStatic($name)
    {
        return isset($this->_static[$name]);
    }

    public function getStatic($name)
    {
        return isset($this->_static[$name]) ? $this->_static[$name] : null;
    }

    private $static;

    public function setStatic($value)
    {
        $this->_static;
    }

}

class ArrayKey
{

    private $_parent;
    private $_key;

    public function __construct($parent, $key)
    {
        $this->_parent = $parent;
        $this->_key = $key;
    }

    public function __get($name)
    {
        return $this->_parent->get($name, true, $this->_key);
    }

    public function setParent($parent)
    {
        $this->_parent = $parent;
    }

    public function setKey($key)
    {
        $this->_key = $key;
    }

}
