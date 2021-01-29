# Solspace Express Forms plugin for Craft CMS 3.x

Express Forms is an intuitive and lightweight form builder that gets the job done but doesn’t get in your way.

![Screenshot](packages/plugin/src/icon.svg)

- [Overview](#overview)
- [Key Features](#key-features)
- [Pricing & Support](#pricing--support)
- [Requirements](#requirements)
- [Installation](#installation)
- [Documentation](#documentation)

## Overview

Express Forms is a FREE reliable form building plugin for Craft CMS. It contains every feature available to the native Craft Contact Form plugin and more. It's simple and intuitive to use, and doesn't get in your way if you're wanting to build simple forms or handle your own templating and custom features. It was developed with a "developer first" mentality, so it’s highly extendable. It also contains several built-in popular API integrations. Stop wasting valuable development hours wrestling with alternative form plugins. Express Forms makes form building smooth.

## Key Features

Compare the key features for Express Forms between _Lite_ and _Pro_ editions. The FREE _Lite_ edition includes all the essentials for creating and managing forms for most websites. Upgrade to _Pro_ edition if you need additional functionality for your forms.

For a full feature comparison to **Freeform** and the native Craft **Contact Form** plugin, [check out the Compare page](https://craft.express/forms/v1/compare.html)!

| Feature                                                  | Description                                                                                         | **Lite** | **Pro** |
| :------------------------------------------------------- | :-------------------------------------------------------------------------------------------------- | :------: | :-----: |
| Unlimited forms                                          | Create and manage as many forms as you need                                                         |    ✓     |  **✓**  |
| Unlimited email notifications and template choices       | Almost every conceivable option for sending HTML email(s), all customizable                         |    ✓     |  **✓**  |
| Email notifications saved as HTML files                  | Save email notification templates as HTML files, but also manage from directly inside control panel |    ✓     |  **✓**  |
| Beautiful and simplified Form Builder                    | Simple yet powerful, and easy to use                                                                |    ✓     |  **✓**  |
| All basic browser field types                            | Choose between a variety of regular field types                                                     |    ✓     |  **✓**  |
| Upload/attach files to submissions                       | Allow users to upload files which are validated and stored as Assets                                |    ✓     |  **✓**  |
| Manage submissions                                       | View, edit, delete or export form submissions                                                       |    ✓     |  **✓**  |
| Save submissions to database, or not                     | Choose to store submission data, or never keep it on your site                                      |    ✓     |  **✓**  |
| GDPR compliant                                           | All the tools you need to make your forms GDPR compliant                                            |    ✓     |  **✓**  |
| CSV Exporting                                            | Export all submissions for a form as a CSV file                                                     |    ✓     |  **✓**  |
| XML, JSON and Excel Exporting                            | Export all submissions for a form as a XML, JSON or Excel file                                      |          |  **✓**  |
| Built-in Honeypot spam protection                        | Powerful and effective advanced spam control built right in                                         |    ✓     |  **✓**  |
| reCAPTCHA v2 Checkbox                                    | Fight spam with reCAPTCHA v2 Checkbox                                                               |    ✓     |  **✓**  |
| Clean and simplified templating and available automation | Full control with regular Twig/HTML templates to customize layouts                                  |    ✓     |  **✓**  |
| Inline errors                                            | Display error messages and validation per field upon submit                                         |    ✓     |  **✓**  |
| Demo templates                                           | Just 1 click and you have an example form and a real-world set of working templates                 |    ✓     |  **✓**  |
| Basic permission controls                                | Basic user group and user permission controls for each section of plugin                            |    ✓     |  **✓**  |
| Developer-friendly                                       | Extensive developer events for all your needs                                                       |    ✓     |  **✓**  |
| Translatable                                             | Translate fields for front end with translation files                                               |    ✓     |  **✓**  |
| Popular CRM API integrations                             | Currently includes Salesforce Lead, Salesforce Opportunity and HubSpot                              |          |  **✓**  |
| Popular Mailing List API integrations                    | Currently includes MailChimp, Campaign Monitor, and Constant Contact                                |          |  **✓**  |
| Dashboard Widgets                                        | Includes a dashboard widget that displays stats for your form submissions                           |          |  **✓**  |
| Rename plugin                                            | Rename the plugin name (throughout CP) to whatever you like                                         |          |  **✓**  |

## Pricing & Support

Check out the chart below to help you choose the right version for your needs.

|                                                                                                                                                             |                          **Lite**                           |                      **Pro**                       |
| :---------------------------------------------------------------------------------------------------------------------------------------------------------- | :---------------------------------------------------------: | :------------------------------------------------: |
| Price                                                                                                                                                       |                            FREE                             |                      **$49**                       |
| Number of sites (including dev/staging)                                                                                                                     |                           1 site                            |                     **1 site**                     |
| Support Level<br /><small><em>† Support tickets will typically receive first response within this timeframe or sooner, based on business days.</em></small> |                          3-5 days†                          |                    **2 days†**                     |
| Support term                                                                                                                                                |                             N/A                             |                     **1 year**                     |
| Updates available                                                                                                                                           |                             N/A                             |                     **1 year**                     |
| Renewal (optional)                                                                                                                                          |                             N/A                             |                     **$29/yr**                     |
| Compatibility                                                                                                                                               |                         Craft 3.1+                          |                   **Craft 3.1+**                   |
| Refund policy                                                                                                                                               |                             N/A                             |                    **30 days**                     |
| <small><a href="https://plugins.craftcms.com/express-forms">TRY IT TODAY!</a></small>                                                                       | [Install Free!](https://plugins.craftcms.com/express-forms) | [Buy!](https://plugins.craftcms.com/express-forms) |

## Requirements

Solspace Express Forms mostly meets the same requirements as listed on the [Craft Requirements](https://docs.craftcms.com/v3/requirements.html) page.

- **Craft 3.1.0 or later**
- PHP 7.0+
- MySQL 5.5+ with InnoDB, MariaDB 5.5+, or PostgreSQL 9.5+
- Windows and OS X browsers:
  - Chrome 29 or later
  - Firefox 28 or later
  - Safari 9.0 or later
  - Microsoft Edge

## Installation

To install Express Forms, simply:

1. Go to the **Plugin Store** area inside your Craft control panel and search for _Express Forms_.
2. Choose to install _Express Forms Lite_ or _Express Forms Pro_ edition.
3. Click on the **Try** button to install a trial copy of Express Forms.
4. Try things out and if Express Forms is right for your site, and then purchase a copy of it through the Plugin Store when you're ready!

Express Forms can also be installed manually through Composer:

1. Open your terminal and go to your Craft project: `cd /path/to/project`
2. Then tell Composer to require the plugin: `composer require solspace/craft-express-forms`
3. In the Craft control panel, go to _Settings → Plugins_ and click the **Install** button for Express Forms.

## Documentation

Full documentation for Express Forms can be found on the [Craft Express documentation website](https://craft.express/forms/v1/).
