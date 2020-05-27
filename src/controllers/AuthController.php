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


use angellco\auth0\Auth0;
use Craft;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use craft\web\Controller;
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

    // Protected Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Log in to Auth0.
     */
    public function actionLogin()
    {
        Auth0::$plugin->auth->login();
    }

    /**
     * Handles the Auth0 callback.
     *
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws ElementNotFoundException
     * @throws Exception
     * @throws MissingComponentException
     * @throws \Throwable
     */
    public function actionCallback()
    {
        // Try the callback handler first
        if (!Auth0::$plugin->auth->handleCallback()) {
            return null;
        }

        // If we got this far we can redirect properly
        $userSession = Craft::$app->getUser();
        $session = Craft::$app->getSession();

        // Get the return URL
        $returnUrl = $userSession->getReturnUrl();

        // Clear it out
        $userSession->removeReturnUrl();

        // Set the logged in notice and redirect
        $session->setNotice(Craft::t('app', 'Logged in.'));
        return $this->redirectToPostedUrl($userSession->getIdentity(), $returnUrl);
    }

    /**
     * Logs out the user from Auth0 and redirects to the correct URL.
     *
     * @return Response
     */
    public function actionLogout()
    {
        $logoutUrl = Auth0::$plugin->auth->logout();
        return $this->redirect($logoutUrl);
    }
}
