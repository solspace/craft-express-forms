# Solspace Express Forms Changelog

## 1.0.0 - 2019-05-07
### Added
- Added support for using environment variables inside email notification templates.

## 1.0.0-beta.6 - 2019-04-30
### Fixed
- Fixed a bug where saving a form would trigger an error when using PostgreSQL.

## 1.0.0-beta.5 - 2019-04-26
### Added
- Added 'Prevent Duplicate Submissions' setting for forms.

### Fixed
- Fixed a bug where Settings area in CP was still visible when the `allowAdminChanges` setting is disabled for Project Config.

## 1.0.0-beta.4 - 2019-04-25
### Fixed
- Fixed a bug where form submitting and return URL's were not working correctly.
- Fixed a bug where the Subject field was not displaying inside CP email notification template editor.
- Fixed a bug where the "Template Directory Path" setting was prepending the full file path after saving a relative path.
- Fixed a bug where the form would not render the flash success message if the return URL wasn't set.
- Fixed a bug with a namespace that was not the correct case, causing issues with Nginx servers.
- Fixed various bugs with demo templates.

## 1.0.0-beta.3 - 2019-04-23
### Changed
- Various improvements to developer events.

### Fixed
- Fixed a bug where CP could show errors if an incorrect template path was specified for Email Notification Templates.
- Fixed a bug where installing Express Forms could error in some cases.

## 1.0.0-beta.2 - 2019-04-19
### Added
- Added support for Opportunity resource mapping in Salesforce API integration (Pro).

### Fixed
- Fixed a bug that would cause the Email Notifications CP page to error for some customers.

## 1.0.0-beta.1 - 2019-04-17
### Added
- Initial release.
