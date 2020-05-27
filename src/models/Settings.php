<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 with Craft.
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
     * @var string|null
     */
    public $domain;

    /**
     * @var string|null
     */
    public $clientId;

    /**
     * @var string|null
     */
    public $clientSecret;

    /**
     * @var string|null
     */
    public $callbackUrl;

    /**
     * @var string|null
     */
    public $userGroupHandle;

    /**
     * @var string|null
     */
    public $logoutReturnUrl;
}
