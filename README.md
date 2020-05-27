# Auth0

Use Auth0 with Craft.

![Banner](resources/img/banner.png)

## Requirements

This plugin requires Craft CMS 3.1.x or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require angell-co/auth0

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Auth0.

## Overview

[Auth0](https://auth0.com/) is a service that allows you to rapidly integrate Universal Login, Single Sign On (SSO), Multifactor Authentication and more into your website or application.

You can use this plugin to frictionlessly integrate your Craft site with Auth0.

## Configuration

Before you get going with this plugin you’ll want to set up an application on Auth0, choose the "Regular Web Application" option at this stage and don’t worry about filling in the rest of the settings as we’ll get to that later.

Configuration for this plugin is managed through the config file. Once you have installed the plugin copy the file from `/path/to/project/vendor/angellco/auth0/src/config.php` to `/path/to/project/craft/config/auth0.php` and fill in the relevant values.

The file has comments to help you out, but you can see a detailed list of the variables and their values below.

TODO: document config.

Once you have done that, return to your application in the Auth0 dashboard and fill in the following sections:

TODO: document Auth0 appliction settings.

URL for callback is `https://myproject.test/actions/auth0/auth/callback`.

"Allowed Logout URLs" setting should match what is set in `logoutReturnUrl` config variable.

## Usage

TODO

- Login action: `{{ actionUrl('auth0/auth/login') }}` - logs in to both Auth0 and Craft and silently creates and activates a user if there isn’t one 
- Logout action: `{{ actionUrl('auth0/auth/logout') }}` - just logs out Auth0, use regular Craft logout action or URL to be logged out of both

## Roadmap

Some things to do, and ideas for potential features:

- [x] Release it
- [ ] Add option to _not_ automatically create new users
- [ ] Add the default login route
- [ ] Document the config file
- [ ] Document the Auth0 application settings
- [ ] Document the events
- [ ] Document how to use with alternative session stores
- [ ] Add twig variable so we can get at the user data in templates
- [ ] Handle being logged out of Auth0 but still logged in to Craft, what happens then?
- [ ] Allow the `EVENT_BEFORE_USER_LOGIN` event to cancel the login attempt
- [ ] Use session duration from Auth0 and add option to override this to use Craft’s

--- 

Brought to you by [Angell & Co](https://angell.io)
