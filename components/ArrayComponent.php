<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, communityii, 2014 - 2015
 * @package communityii/yii2-common
 * @version 1.0.0
 *
 * @author derekisbusy https://github.com/derekisbusy
 * @author kartik-v https://github.com/kartik-v
 */

namespace comyii\common\components;

use yii\base\Component;
use comyii\common\traits\ArrayContainer;

/**
 * Class ArrayComponent base class for components that implement `ArrayAccess`. 
 * Classes extended from this class should implement a container property and set `private $_containerName`
 * to the name of the container property.
 * 
 * @package comyii\user\components
 */
class ArrayComponent extends Component implements ArrayAccess
{
    use ArrayContainer;
    
    /**
     * Construct the ArrayComponent
     * 
     * @param array $config
     */
    public function __construct($config = array()) {
        $config = $this->mergeConfig($config);
        parent::__construct($config);
    }
}