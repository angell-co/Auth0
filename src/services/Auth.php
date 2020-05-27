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

use angellco\auth0\Auth0 as Auth0Plugin;
use angellco\auth0\events\BeforeUserCreatedEvent;
use angellco\auth0\events\BeforeUserLoginEvent;
use angellco\auth0\models\Settings;
use Auth0\SDK\Auth0;
use Auth0\SDK\Auth0 as Auth0SDK;
use Auth0\SDK\Exception\ApiException;
use Auth0\SDK\Exception\CoreException;
use Craft;
use craft\base\Component;
use craft\elements\User;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use craft\helpers\User as UserHelper;
use yii\base\Exception;

/**
 * @author    Angell & Co
 * @package   Auth0
 * @since     1.0.0
 *
 * @property null|string|array $user
 */
class Auth extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event BeforeUserCreatedEvent The event that is triggered before a user is created for the first time
     */
    const EVENT_BEFORE_USER_CREATED = 'auth0_beforeUserCreated';

    /**
     * @event BeforeUserLoginEvent The event that is triggered before a user is logged in
     */
    const EVENT_BEFORE_USER_LOGIN = 'auth0_beforeUserLogin';

    // Private Properties
    // =========================================================================
    /**
     * @var Auth0SDK
     */
    private $_auth0;

    /**
     * @var bool|Settings|null
     */
    private $_settings;

    // Public Methods
    // =========================================================================

    /**
     * Auth constructor.
     *
     * @param array $config
     * @throws CoreException
     */
    public function __construct($config = [])
    {
        // Cache the plugin settings
        $this->_settings = Auth0Plugin::$plugin->getSettings();

        // Instantiate the base Auth0 SDK
        $this->_auth0 = new Auth0([
            'domain' => $this->_settings->domain,
            'client_id' => $this->_settings->clientId,
            'client_secret' => $this->_settings->clientSecret,
            'redirect_uri' => $this->_settings->callbackUrl,
            'scope' => 'openid profile email',

            // Needed for Auth0 to access our session storage
            // TODO: test whether this works if we override the core Craft session with something like redis
            'store' => Craft::$app->getSession()
        ]);

        parent::__construct($config);
    }

    /**
     * Redirects to the Auth0 login page.
     */
    public function login()
    {
        $this->_auth0->login();
    }

    /**
     * Logs the user out of the Auth0 session and returns the URL that we should
     * redirect to.
     *
     * TODO handle being logged out of Auth0 but still logged in to Craft, what happens then?
     *
     * @return string
     */
    public function logout()
    {
        $this->_auth0->logout();
        return sprintf('https://%s/v2/logout?client_id=%s&returnTo=%s', $this->_settings->domain, $this->_settings->clientId, $this->_settings->logoutReturnUrl);
    }

    /**
     * Handles the Auth0 callback and either logs the user in if we already
     * have one or creates one and then logs them in.
     *
     * @return bool|null
     * @throws \Throwable
     * @throws ElementNotFoundException
     * @throws MissingComponentException
     * @throws Exception
     */
    public function handleCallback()
    {
        $auth0UserInfo = $this->getUser();

        $users = Craft::$app->getUsers();
        $session = Craft::$app->getSession();

        // Check if we don’t have any user info from Auth0 and bail if not
        if (!$auth0UserInfo) {
            $session->setError(UserHelper::getLoginFailureMessage());
            return null;
        }

        // Get the Craft user if we can
        $user = $users->getUserByUsernameOrEmail($auth0UserInfo['email']);

        // There isn’t one, so create it first
        if (!$user) {

            $user = new User();
            $user->email = $auth0UserInfo['email'];
            $user->username = $user->email;

            // Set some basic details on the user profile, if these need to be different then they can be overridden
            // in the BeforeUserCreated event.
            $nameParts = explode(' ', $auth0UserInfo['name']);
            if (count($nameParts) >= 2) {
                $user->firstName = $nameParts[0];
                $user->lastName = $nameParts[1];
            }

            // Give plugins a chance to modify the user before its created
            $event = new BeforeUserCreatedEvent([
                'user' => $user,
                'auth0UserInfo' => $auth0UserInfo,
            ]);
            $this->trigger(self::EVENT_BEFORE_USER_CREATED, $event);

            // Validate and save it
            if (
                !$user->validate(null, false) ||
                !Craft::$app->getElements()->saveElement($user, false)
            ) {
                $session->setError(Craft::t('app', 'Couldn’t save user.'));
                return null;
            }

            // Manually activate the user
            $users->activateUser($user);

            // Get the user group
            $userGroupHandle = $this->_settings->userGroupHandle;
            $userGroup = null;
            if ($userGroupHandle) {
                $userGroup = Craft::$app->getUserGroups()->getGroupByHandle($userGroupHandle);
            }

            // Assign them to the specified user group or default
            if ($userGroup) {
                $users->assignUserToGroups($user->id, [$userGroup->id]);
            } else {
                $users->assignUserToDefaultGroup($user);
            }
        }

        // There is now a user, so fire an event before we log them in to give plugins
        // a chance to either fail the login or modify the user
        // TODO: allow event to cancel the login
        $event = new BeforeUserLoginEvent([
            'user' => $user,
            'auth0UserInfo' => $auth0UserInfo,
        ]);
        $this->trigger(self::EVENT_BEFORE_USER_LOGIN, $event);

        // Log them in
        // TODO: use session duration from Auth0
        $generalConfig = Craft::$app->getConfig()->getGeneral();
        return Craft::$app->getUser()->login($user, $generalConfig->userSessionDuration);
    }

    /**
     * Returns the User from Auth0 if there is one in the current session.
     *
     * @return array|string|null
     * @throws ApiException
     * @throws CoreException
     */
    public function getUser()
    {
        return $this->_auth0->getUser();
    }

}