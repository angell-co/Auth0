<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 with Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0;

use angellco\auth0\models\Settings;
use angellco\auth0\services\Auth as AuthService;

use angellco\auth0\variables\Auth0Variable;
use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;
use yii\web\User;
use yii\web\UserEvent;

/**
 * Class Auth0
 *
 * @author    Angell & Co
 * @package   Auth0
 * @since     1.0.0
 *
 * @property  AuthService $auth
 */
class Auth0 extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Auth0
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register the variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('auth0', new Auth0Variable());
            }
        );

        // Bind to the after logout event so we can clear the Auth0 session
        Event::on(
            User::class,
            User::EVENT_AFTER_LOGOUT,
            function (UserEvent $event) {
                if ($this->auth->getUser() && $logoutUrl = $this->auth->logout()) {
                    Craft::$app->getResponse()->redirect($logoutUrl)->send();
                }
            }
        );

        Craft::info(
            Craft::t(
                'auth0',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }
}
