<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 with Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0\variables;

use angellco\auth0\Auth0;
use Auth0\SDK\Exception\ApiException;
use Auth0\SDK\Exception\CoreException;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use yii\base\Exception;

/**
 * Class Auth0Variable
 *
 * @author    Angell & Co
 * @package   Auth0
 * @since     1.1.0
 */
class Auth0Variable
{
    // Public Methods
    // =========================================================================

    /**
     * Attempts to silently login to Craft if there is already an active Auth0
     * session.
     *
     * @throws ApiException
     * @throws CoreException
     * @throws \Throwable
     * @throws ElementNotFoundException
     * @throws MissingComponentException
     * @throws Exception
     */
    public function silentLogin()
    {
        return Auth0::$plugin->auth->silentLogin();
    }
}