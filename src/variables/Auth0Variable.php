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
     * session and if not checks the referrer to see if we should automatically
     * redirect to the Auth0 login. If there is no referrer or its not in the
     * given whitelist then it will fall back to checking the given whitelist of
     * query params.
     *
     * If the latter happens and there is already an active session there then
     * Auth0 will simply redirect back to our callback and then that will
     * redirect back to the current return URL.
     *
     * @param null|string|array $referrerWhitelist The referrer(s) to match against.
     * @param null|string|array $queryParamWhitelist The query param(s) to match against.
     *
     * @throws ApiException
     * @throws CoreException
     * @throws ElementNotFoundException
     * @throws Exception
     * @throws MissingComponentException
     * @throws \Throwable
     */
    public function silentLogin($referrerWhitelist = null, $queryParamWhitelist = null)
    {
        return Auth0::$plugin->auth->silentLogin($referrerWhitelist, $queryParamWhitelist);
    }
}