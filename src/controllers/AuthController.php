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

    // Protected Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected $allowAnonymous = self::ALLOW_ANONYMOUS_LIVE;

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
        $this->_auth0 = new Auth0([
            // TODO: envs in config
            'domain' => getenv('AUTH0_DOMAIN'),
            'client_id' => getenv('AUTH0_CLIENT_ID'),
            'client_secret' => getenv('AUTH0_CLIENT_SECRET'),
            'redirect_uri' => getenv('AUTH0_CALLBACK_URL'),
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

        if (!$auth0UserInfo) {
            // We have no user info
            // See below for how to add a login link
            Craft::dd('FAIL');
        } else {
            // User is authenticated with Auth0

            // Get the Craft user if we can
            $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($auth0UserInfo['email']);

            // There isn’t one, so create it
            if (!$user) {
                Craft::dd(['SUCCESS','No Craft User']);
            } else {
                // There is one, so log them in

                // Get the session duration
                $generalConfig = Craft::$app->getConfig()->getGeneral();
                $duration = $generalConfig->userSessionDuration;

                $userSession = Craft::$app->getUser();
                $userSession->loginByUserId($user->id);
                $userSession->removeReturnUrl();
                Craft::dd(['SUCCESS',[
                    'Is Admin?' => $userSession->getIsAdmin(),
                    'Craft User' => [
                        'id' => $user->id,
                        'firstName' => $user->firstName,
                        'lastName' => $user->lastName
                    ]
                ]]);
            }

        }
    }

    /**
     * @throws \yii\base\ExitException
     */
    public function actionLogout()
    {
        $this->_auth0->logout();
        // TODO: env in config - or same as craft core one
        $return_to = 'https://' . $_SERVER['HTTP_HOST'];
        // TODO: env in config
        $logout_url = sprintf('https://%s/v2/logout?client_id=%s&returnTo=%s', getenv('AUTH0_DOMAIN'), getenv('AUTH0_CLIENT_ID'), $return_to);
        header('Location: ' . $logout_url);
        exit;
    }
}
