<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 SS0 alongside the core Craft login.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0\events;

use craft\elements\User;
use yii\base\Event;

/**
 * BeforeUserLoginEvent class.
 *
 * @author    Angell & Co
 * @package   Auth0
 * @since     1.0.0
 */
class BeforeUserLoginEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var User|null The user associated with the event
     */
    public $user;

    /**
     * @var array|null The Auth0 User Info array
     */
    public $auth0UserInfo;
}