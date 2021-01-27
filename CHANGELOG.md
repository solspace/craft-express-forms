# Solspace Express Forms Changelog

## 1.0.9 - 2021-01-21

### Fixed

- Fixed a compatibility issue with Craft 3.6+.
- Fixed a bug where the Form Submit developer event was not using modified submitted data values.

## 1.0.8 - 2020-10-12

### Fixed

- Fixed a bug where the form builder would not load correctly if reCAPTCHA was not enabled for the site.

## 1.0.7 - 2020-10-09

### Changed

- Updated reCAPTCHA to offer a Light/Dark theme preference.
- The 'Accept' header now can be used to specify a JSON response by using `application/json` when returning AJAX data.

### Fixed

- Fixed a bug where email notifications would still be sent via Dynamic Recipients when the submission was considered spam.
- Fixed a bug where an error would be shown if using the Options field type with data that as not an array.
- Fixed a bug where the CP Error Log could not be accessed when the Craft `allowAdminChanges` config setting was disabled.

## 1.0.6 - 2020-07-29

### Fixed

- Fixed some compatibility issues with Craft 3.5+.
- Fixed a bug where the Save form button was not correctly positioned.
- Fixed a bug where deleted submissions were being included in CSV exports.
- Fixed a bug where using Dashboard Widget could show an error.

## 1.0.5 - 2020-02-11

### Fixed

- Fixed a bug where installing Express Forms through the CLI could error.
- Fixed a bug where the Dynamic Recipients feature would bypass spam protection.
- Fixed a bug where there was a case sensitivity issue in the class namespace for the dashboard widget.

## 1.0.4 - 2019-08-22

### Fixed

- Fixed a bug where search index updating in the CP would error each time after a new Express Forms submission was created.

## 1.0.3 - 2019-07-31

### Changed

- Updated Honeypot field to ensure screen readers don't see it and it can't be tabbed to, etc.

### Fixed

- Fixed a bug where fields with array values wouldn't work correctly with required field validator.
- Fixed a bug where the HubSpot integration wouldn't work correctly when a duplicate Contact is being created.

## 1.0.2 - 2019-06-12

### Changed

- Updated `carbon` dependency to `^1.22.1|^2.19` for better compatibility with other plugins, and to reduce the chances of seeing deprecation notice.
- Updated plugin icon.

### Fixed

- Fixed a bug where submission saving on multi-site Craft installs would trigger an error.

## 1.0.1 - 2019-05-08

### Fixed

- Fixed a bug where using AJAX would not record data on additional submits (e.g. after first one errored) when using the 'Prevent Duplicate Submissions' setting.

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
