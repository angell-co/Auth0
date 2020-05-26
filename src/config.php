<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 SS0 alongside the core Craft login.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

/**
 * Auth0 config.php
 *
 * This file exists only as a template for the Auth0 settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'auth0.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    'domain' => getenv('AUTH0_DOMAIN'),
    'clientId' => getenv('AUTH0_CLIENT_ID'),
    'clientSecret' => getenv('AUTH0_CLIENT_SECRET'),
    'callbackUrl' => getenv('AUTH0_CALLBACK_URL'),
];
