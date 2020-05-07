<?php
/**
 * Auth0 plugin for Craft CMS 3.x
 *
 * Use Auth0 SS0 alongside the core Craft login.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\auth0\assetbundles\auth0;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Angell & Co
 * @package   Auth0
 * @since     1.0.0
 */
class Auth0Asset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@angellco/auth0/assetbundles/auth0/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Auth0.js',
        ];

        $this->css = [
            'css/Auth0.css',
        ];

        parent::init();
    }
}
