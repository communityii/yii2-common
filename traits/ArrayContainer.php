<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, communityii, 2014 - 2015
 * @package communityii/yii2-common
 * @version 1.0.0
 *
 * @author derekisbusy https://github.com/derekisbusy
 * @author kartik-v https://github.com/kartik-v
 */

namespace comyii\common\traits;

/**
 * Trait ArrayContainer implements ArrayAccess using `container` property.
 * 
 * @package comyii\common\traits
 */
trait ArrayContainer
{
    /**
     * @var array the container property name
     */
    protected $_containerName = 'container';
    
    public function mergeConfig($config = array()) {
        if (isset($config[$this->_containerName])) {
            $config[$this->_containerName] = array_replace_recursive($this->getDefaults(), $config[$this->_containerName]);
        } else {
            $config[$this->_containerName] = $this->getDefaults();
        }
        return $config;
    }
    
    /**
     * Get the defaults
     * 
     * @return array
     */
    public function getDefaults()
    {
        return [];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->{$this->_containerName}[] = $value;
        } else {
            $this->{$this->_containerName}[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->{$this->_containerName}[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->{$this->_containerName}[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->{$this->_containerName}[$offset]) ? $this->{$this->_containerName}[$offset] : null;
    }
}