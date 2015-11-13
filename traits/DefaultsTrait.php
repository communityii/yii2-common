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

trait DefaultsTrait
{
    /**
     * Merge class configurations
     *
     * @param array $config
     * @param array $defaults
     *
     * @return array the merged array
     */
    public static function mergeDefault($config, $defaults)
    {
        foreach ($defaults as $key => $default) {
            if (!isset($config[$key])) {
                $config[$key] = $default;
            } elseif (!isset($config[$key]['class'])) {
                $config[$key] = array_replace_recursive($config[$key], $default);
            }
        }
        return $config;
    }
}