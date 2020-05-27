<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 SS0 alongside the core Craft login.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0;

use angellco\auth0\models\Settings;
use angellco\auth0\services\Auth as AuthService;

use Craft;
use craft\base\Plugin;

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

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

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
