# Auth0 Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).


## 1.1.3 - 2020-06-22
### Added
- Added the option to check for existing users by setting a custom field and Auth0 metadata key in the config file. Set `uniqueUserFieldHandle` to the field handle and `uniqueUserFieldMetaKey` to the metadata key that will be used to validate the check. This check runs before the email one and will only run if those two values are set in the config file.


## 1.1.2 - 2020-06-05
### Added
- Added a second parmater to the silen login to allow for query params to act as a fallback if the referrer is not present. Use like so: `{% do craft.auth0.silentLogin(['someapp.com','api-someapp.com'], {'ref':'someapp','n':'123'}) %}` or `{% do craft.auth0.silentLogin(['someapp.com','api-someapp.com'], [{'ref':'someapp1','n':'123'},{'ref':'someapp2','n':'456'}]) %}`

### Changed
- Changed how the silent login referral matching works, you can now provide an array of domains to whitelist: `{% do craft.auth0.silentLogin(['someapp.com','api-someapp.com']) %}`


## 1.1.1 - 2020-06-05
### Added
- Added referer matching to the silent login. This covers the situation where the client is logged in to Auth0 from another application than ours - you can pass the domain of that application in as the referrer and it will then auto-redirect to the Auth0 login URL, which in turn will redirect back to our site and log them in: `{% do craft.auth0.silentLogin('someapp.com') %}`


## 1.1.0 - 2020-06-05
### Added
- Added a variable and service method that attempts to silently log in to Craft if there is already an active Auth0 session: `{% do craft.auth0.silentLogin() %}`

### Changed
- When logging out from the core Craft session there is now a check for an active Auth0 session before attempting to logout from there too 


## 1.0.1 - 2020-05-27
### Fixed
- Fixed a few bad use references in the Auth service


## 1.0.0 - 2020-05-27
### Added
- Initial release
