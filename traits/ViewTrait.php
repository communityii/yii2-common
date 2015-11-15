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

trait ViewTrait
{
    /**
     * @var array the view to use for each action in the module. The keys will be one of the `self::VIEW_`
     * constants and the value will be the view file name. The view file name can be combined with Yii
     * path aliases (for example `@frontend/views/user/login`).
     *
     * @see `setConfig()` method for the default settings
     */
    public $viewSettings = [];

    /**
     * Gets the layout file for the current view and user type.
     *
     * @param string $view
     *
     * @return string the layout file
     */
    public function getLayout($view)
    {
        $userType = isset($this->layoutSettings[Yii::$app->user->type]) ? $this->layoutSettings[Yii::$app->user->type] : null;
        if ($userType && is_array($userType) && isset($userType[$view])) {
            return $userType[$view];
        }
        if (!isset($this->layoutSettings[$view])) {
            return null;
        }
        return $this->layoutSettings[$view];
    }
}
