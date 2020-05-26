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

use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\ApiException;
use Auth0\SDK\Exception\CoreException;
use Craft;
use craft\web\Controller;
use craft\web\View;

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
     * @var bool|\craft\base\Model|null
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
            // TODO: envs in config
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
     * @throws ApiException
     * @throws CoreException
     * @throws \yii\base\ExitException
     */
    public function actionCallback()
    {
        $auth0UserInfo = $this->_auth0->getUser();

        $return = [];

        if (!$auth0UserInfo) {
            // We have no user info
            $return['error'] = 'Failed to log in';
        } else {
            // User is authenticated with Auth0
            $return['success'] = 'Logged in.';
            $return['auth0User'] = $auth0UserInfo;

            // Get the Craft user if we can
            $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($auth0UserInfo['email']);

            // There isn’t one, so create it
            if (!$user) {
                $return['user'] = false;
            } else {
                // There is one, so log them in

                // Get the session duration
                $generalConfig = Craft::$app->getConfig()->getGeneral();
                $duration = $generalConfig->userSessionDuration;
                // TODO - session

                $userSession = Craft::$app->getUser();
                $userSession->loginByUserId($user->id);
                $userSession->removeReturnUrl();

                $return['user'] = $user;
            }
        }

        return $this->renderTemplate('auth0-test.html', ['authdata'=>$return], View::TEMPLATE_MODE_SITE);
    }

    /**
     * @throws \yii\base\ExitException
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
