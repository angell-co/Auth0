# Auth0 Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).


## 1.0.2 - 2020-06-05
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
