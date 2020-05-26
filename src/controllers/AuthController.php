<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 SS0 alongside the core Craft login.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0\controllers;

use angellco\auth0\Auth0 as Auth0Plugin;

use angellco\auth0\models\Settings;
use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\ApiException;
use Auth0\SDK\Exception\CoreException;
use Craft;
use craft\elements\User;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use craft\web\Controller;
use craft\helpers\User as UserHelper;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * @author    Angell & Co
 * @package   Auth0
 * @since     1.0.0
 */
class AuthController extends Controller
{
    // Private Properties
    // =========================================================================
    /**
     * @var Auth0
     */
    private $_auth0;

    /**
     * @var bool|Settings|null
     */
    private $_settings;

    // Protected Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * AuthController constructor.
     *
     * @param       $id
     * @param       $module
     * @param array $config
     *
     * @throws CoreException
     */
    public function __construct($id, $module, $config = [])
    {
        $this->_settings = Auth0Plugin::$plugin->getSettings();

        $this->_auth0 = new Auth0([
            'domain' => $this->_settings->domain,
            'client_id' => $this->_settings->clientId,
            'client_secret' => $this->_settings->clientSecret,
            'redirect_uri' => $this->_settings->callbackUrl,
            'scope' => 'openid profile email',
        ]);

        parent::__construct($id, $module, $config);
    }

    /**
     * Redirects to the Auth0 login page.
     */
    public function actionLogin()
    {
        $this->_auth0->login();
    }

    /**
     * Handles the Auth0 callback with either a successfully authenticated
     * user session or not.
     *
     * @return Response|null
     * @throws ApiException
     * @throws CoreException
     * @throws \Throwable
     * @throws ElementNotFoundException
     * @throws MissingComponentException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionCallback()
    {
        $auth0UserInfo = $this->_auth0->getUser();

        $users = Craft::$app->getUsers();
        $userSession = Craft::$app->getUser();
        $session = Craft::$app->getSession();

        // Check if we don’t have any user info from Auth0 and bail if not
        if (!$auth0UserInfo) {

            $session->setError(UserHelper::getLoginFailureMessage());

            return null;

        } else {

            // Get the Craft user if we can
            $user = $users->getUserByUsernameOrEmail($auth0UserInfo['email']);

            // There isn’t one, so create it first
            if (!$user) {

                $user = new User();
                $user->email = $auth0UserInfo['email'];
                $user->username = $user->email;

                // TODO: set this from the metadata via config params
                $nameParts = explode(' ', $auth0UserInfo['name']);
                if (count($nameParts) >= 2) {
                    $user->firstName = $nameParts[0];
                    $user->lastName = $nameParts[1];
                }

                // TODO: Raise event so plugins can modify the user before its created

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

            // TODO: check we have a user after user creation, throw hard error if not

            // Log them in
            $generalConfig = Craft::$app->getConfig()->getGeneral();
            Craft::$app->getUser()->login($user, $generalConfig->userSessionDuration);

            // Get the return URL
            $returnUrl = $userSession->getReturnUrl();

            // Clear it out
            $userSession->removeReturnUrl();

            // Set the logged in notice and redirect
            $session->setNotice(Craft::t('app', 'Logged in.'));
            return $this->redirectToPostedUrl($userSession->getIdentity(), $returnUrl);
        }

    }

    /**
     * TODO
     */
    public function actionLogout()
    {
        $userSession = Craft::$app->getUser();
        $userSession->logout(true);

        $this->_auth0->logout();
        // TODO: env in config - or same as craft core one
        $return_to = 'https://' . $_SERVER['HTTP_HOST'].'/auth0-test';
        // TODO: env in config
        $logout_url = sprintf('https://%s/v2/logout?client_id=%s&returnTo=%s', getenv('AUTH0_DOMAIN'), getenv('AUTH0_CLIENT_ID'), $return_to);
        header('Location: ' . $logout_url);
        exit;
    }
}
