<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, communityii, 2014 - 2015
 * @package communityii/yii2-user
 * @version 1.0.0
 *
 * @author derekisbusy https://github.com/derekisbusy
 * @author kartik-v https://github.com/kartik-v
 */

namespace comyii\common\traits;

use yii\base\InvalidConfigException;

trait ControllerBehaviorsTrait
{
    /**
     * @var array default behaviors for the controllers. Behaviors defined here will be applied to all the controllers
     * except install and default.
     */
    public $defaultControllerBehaviors = [];

    /**
     * @var array behaviors for the controllers with the key set to the controller id and value as an array of
     *     behaviors.
     */
    public $controllerBehaviors = [];

    /**
     * Gets the controller behaviors configuration
     *
     * @param string $id the controller identifier
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getControllerBehaviors($id)
    {
        if (isset($this->controllerBehaviors[$id])) {
            $behaviors = $this->controllerBehaviors[$id];
            if (!is_array($behaviors)) {
                throw new InvalidConfigException("Controller behaviors must be an array");
            }
            return self::mergeDefault($behaviors, $this->defaultControllerBehaviors);
        }
        return $this->defaultControllerBehaviors;
    }
}