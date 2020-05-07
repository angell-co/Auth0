<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 SS0 alongside the core Craft login.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0\services;

use angellco\auth0\Auth0;

use Craft;
use craft\base\Component;

/**
 * @author    Angell & Co
 * @package   Auth0
 * @since     1.0.0
 */
class Users extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (Auth0::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }
}
