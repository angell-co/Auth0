<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 SS0 alongside the core Craft login.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0\models;

use angellco\auth0\Auth0;

use Craft;
use craft\base\Model;

/**
 * @author    Angell & Co
 * @package   Auth0
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string
     */
    public $clientId;

    /**
     * @var string
     */
    public $clientSecret;

    /**
     * @var string
     */
    public $callbackUrl;

    /**
     * @var string
     */
    public $userGroupHandle;
}
