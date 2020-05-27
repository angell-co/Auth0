<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 with Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0\events;

use craft\elements\User;
use yii\base\Event;

/**
 * BeforeUserCreatedEvent class.
 *
 * @author    Angell & Co
 * @package   Auth0
 * @since     1.0.0
 */
class BeforeUserCreatedEvent extends Event
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