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
use kartik\base\Module as BaseModule;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;
use yii\di\Container;

/**
 * Class Module the base module used in communityii packages.
 * 
 * @package comyii\user\components
 */
class Module extends BaseModule
{

    /**
     * Use magic settings for __set() method. Uses `static` property to
     * store properties that do not exist in the module.
     */
    const MAGIC_PROPERTIES = false;

    /**
     * @var array|null list of components to initial on app bootstrap.
     */
    public $bootstrap;

    /**
     * @var array|null list of components to initialize before module controller.
     * These components are only loaded when the controller is under the user module.
     */
    public $init;

    /**
     * @var array default component definitions indexed by their IDs
     */
    protected $_defaults = [];

    /**
     * @var array|null settings defined in the configs. These settings are merged with
     * defaults.
     */
    protected $_config;

    /**
     * @var array|null custom settings defined in the config. These settings are used if
     * the settings do not exist in the module class. For example in module config:
     * 
     * ```php
     *      [
     *          'components' => [],
     *          'static' => [
     *              'myCustomVar' => 'test',
     *          ],
     *      ]
     * ```
     * 
     * The custom property can now be accessed as `Yii::$app->getModule('user')->myCustomVar`
     * 
     * This is similar to components except that static properties are set by reference and
     * don't have to implement a class or exist in the module.
     */
    protected $_static;

    /**
     * @var Container the dependency injection (DI) container used by [[createObject()]].
     * You may use [[Container::set()]] to set up the needed dependencies of classes and
     * their initial property values.
     * @see createObject()
     * @see Container
     */
    public static $container;

    /**
     * @var array|null Components listed here will be passed to `Yii::$container` and use
     * the main applications dependency injection (DI) container.
     *  
     */
    public $yii;
    /**
     * Initialize the module
     */
    public function init()
    {
        static::$container = new Container();
        $this->_msgCat = 'user';
        
        parent::init();
//        $this->setConfig();
    }

    /**
     * This method is overridden to support accessing components and custom settings like reading properties.
     * 
     * @param string $name component, property or static name
     * @return mixed
     */
    public function __get($name)
    {
        if (($has = $this->getProperty($name))) {
            return $has;
        }
        if (($config = $this->getConfig($name)) && !empty($config)) {
            var_dump($config);
            $this->set($name, $config);
            return $this->get($name);
        }
        return Component::__get($name);
    }
    
    protected function getConfig($name)
    {
        $config = [];
        // default definitions
        if (isset($this->_defaults[$name])) {
            $config = $this->_defaults[$name];
        }
        // config components
        if (isset($this->_config[$name])) {
            $config = array_replace_recursive($config, $this->_config[$name]);
        }
        return $config;
    }
    
    public function getProperty($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        if ($this->has($name, true)) {
            return $this->get($name);
        }
        if ($this->hasStatic($name)) {
            return $this->getStatic($name);
        }
    }

    /**
     * Sets the value of a component property.
     * This method will check in the following order and act accordingly:
     *
     *  - a property defined by a setter: set the property value
     *  - an event in the format of "on xyz": attach the handler to the event "xyz"
     *  - a behavior in the format of "as xyz": attach the behavior named as "xyz"
     *  - a property of a behavior: set the behavior property value
     *  - a element defined in `_static` property array
     *  - if `static::MAGIC_PROPERTIES` is `true`, add item to `_static` property array
     * 
     * @param type $name
     * @param type $value
     * @throws UnknownPropertyException
     */
    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (UnknownPropertyException $e) {
            if (isset($this->_static[$name]) || static::MAGIC_PROPERTIES) {
                if (static::MAGIC_PROPERTIES) {
                    Yii::trace('magic property set (' . $name .'=' .$value .')', __CLASS__);
                }
                $this->_static[$name] = $value;
            } else {
                throw $e;
            }
        }
    }

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
    public function get($id, $throwException = true)
    {
        if (isset($this->_components[$id])) {
            return $this->_components[$id];
        }

        if (isset($this->_definitions[$id])) {
            $definition = $this->_definitions[$id];
            if (is_object($definition) && !$definition instanceof Closure) {
                return $this->_components[$id] = $definition;
            } else {
                if (isset($this->yii[$id])) {
                    return $this->_components[$id] = Yii::$app->get($id);
                }
                return $this->_components[$id] = self::createObject($definition);
            }
        } elseif ($throwException) {
            throw new InvalidConfigException("Unknown component ID: $id");
        } else {
            return null;
        }
    }

    public function hasStatic($name)
    {
        return isset($this->_static[$name]);
    }

    public function getStatic($name)
    {
        return isset($this->_static[$name]) ? $this->_static[$name] : null;
    }

    public function setStatic($value)
    {
        $this->_static = &$value;
    }

    /**
     * Sets the component settings from the config file to the `_config`.property.
     * 
     * This module only sets the definitions for each component when they are loaded
     * unless defined in the `bootstrap` or `init` property.
     * 
     * @param array $components
     */
    public function setComponents($components)
    {
        $this->_config = $components;
    }
 
    /**
     * Creates a new object using the given configuration.
     *
     * You may view this method as an enhanced version of the `new` operator.
     * The method supports creating an object based on a class name, a configuration array or
     * an anonymous function.
     *
     * Below are some usage examples:
     *
     * ```php
     * // create an object using a class name
     * $object = Yii::createObject('yii\db\Connection');
     *
     * // create an object using a configuration array
     * $object = Yii::createObject([
     *     'class' => 'yii\db\Connection',
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // create an object with two constructor parameters
     * $object = \Yii::createObject('MyClass', [$param1, $param2]);
     * ```
     *
     * Using [[\yii\di\Container|dependency injection container]], this method can also identify
     * dependent objects, instantiate them and inject them into the newly created object.
     *
     * @param string|array|callable $type the object type. This can be specified in one of the following forms:
     *
     * - a string: representing the class name of the object to be created
     * - a configuration array: the array must contain a `class` element which is treated as the object class,
     *   and the rest of the name-value pairs will be used to initialize the corresponding object properties
     * - a PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
     *   The callable should return a new instance of the object being created.
     *
     * @param array $params the constructor parameters
     * @return object the created object
     * @throws InvalidConfigException if the configuration is invalid.
     * @see Container
     */
    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return static::$container->get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return static::$container->get($class, $params, $type);
        } elseif (is_callable($type, true)) {
            return call_user_func($type, $params);
        } elseif (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        } else {
            throw new InvalidConfigException('Unsupported configuration type: ' . gettype($type));
        }
    }

}
