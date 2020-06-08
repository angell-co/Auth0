<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 with Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0\services;

use angellco\auth0\Auth0;
use angellco\auth0\events\BeforeUserCreatedEvent;
use angellco\auth0\events\BeforeUserLoginEvent;
use angellco\auth0\models\Settings;
use Auth0\SDK\Auth0 as Auth0SDK;
use Auth0\SDK\Exception\ApiException;
use Auth0\SDK\Exception\CoreException;
use Craft;
use craft\base\Component;
use craft\elements\User;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use craft\helpers\ArrayHelper;
use craft\helpers\Stringy;
use craft\helpers\UrlHelper;
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
     * @throws CoreException|MissingComponentException
     */
    public function __construct($config = [])
    {
        // Cache the plugin settings
        $this->_settings = Auth0::$plugin->getSettings();

        // Instantiate the base Auth0 SDK
        $this->_auth0 = new Auth0SDK([
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
        // Normalise the params
        if ($referrerWhitelist !== null && is_string($referrerWhitelist)) {
            $referrerWhitelist = [$referrerWhitelist];
        }

        if ($queryParamWhitelist !== null && ArrayHelper::isAssociative($queryParamWhitelist)) {
            $queryParamWhitelist = [$queryParamWhitelist];
        }

        // Check if we already have a session, and if the callback validates
        if ($this->getUser() && $this->handleCallback()) {
            // If we got this far we can redirect properly
            $userSession = Craft::$app->getUser();
            $session = Craft::$app->getSession();

            // Get the return URL
            $returnUrl = $userSession->getReturnUrl();

            // Clear it out
            $userSession->removeReturnUrl();

            // Set the logged in notice and redirect
            $session->setNotice(Craft::t('app', 'Logged in.'));
            Craft::$app->getResponse()->redirect($returnUrl);
        }

        $request = Craft::$app->getRequest();

        // If we have a referer, then check the actual referer passes the whitelist
        // of passed in values and if so, force Auth0 login
        if (is_array($referrerWhitelist)) {
            foreach ($referrerWhitelist as $referrer) {
                if (Stringy::create($request->referrer)->contains($referrer, false)) {
                    $this->_auth0->login();
                    break;
                }
            }
        }

        // If we got this far, then we have no referrer, so check the query params
        if (is_array($queryParamWhitelist)) {
            foreach ($queryParamWhitelist as $queryParamSet) {
                $passedParams = 0;
                foreach ($queryParamSet as $queryParamKey => $queryParamValue) {
                    if ($request->getParam($queryParamKey) === $queryParamValue) {
                        $passedParams++;
                    }
                }
                if ($passedParams === count($queryParamSet)) {
                    $this->_auth0->login();
                }
            }
        }
    }
}
