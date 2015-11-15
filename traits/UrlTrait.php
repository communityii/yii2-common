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

trait UrlTrait
{
    /**
     * @var string The prefix for user module URL.
     *
     * @see [[yii\web\GroupUrlRule::prefix]]
     */
    public $urlPrefix = 'user';

    /**
     * @var array the list of url rules
     */
    public $urlRules = [
        'profile' => 'profile/index',
        'profile/<id:\d+>' => 'profile/view',
        'update' => 'profile/update',
        'avatar-delete/<user:>' => 'profile/avatar-delete',
        'admin' => 'admin/index',
        'admin/<id:\d+>' => 'admin/view',
        'admin/update/<id:\d+>' => 'admin/update',
        'auth/<authclient:>' => 'account/auth',
        'activate/<key:>' => 'account/activate',
        'reset/<key:>' => 'account/reset',
        'newemail/<key:>' => 'account/newemail',
        'register/<type:>' => 'account/register',
        '<action>' => 'account/<action>',
    ];
}
