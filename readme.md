# Digest Notifications #
Contributors:      wearerequired, swissspidy, grapplerulrich, ocean90  
Tags:              admin, emails, comments, notification, updates  
Tested up to:      6.7  
Stable tag:        3.0.0  
License:           GPLv2 or later  
License URI:       http://www.gnu.org/licenses/gpl-2.0.html  

Get a daily, weekly, or monthly digest of what's happening on your site instead of receiving a single email each time.

## Description ##

When you have lots of new user sign-ups or comments every day, it’s very distracting to receive a single email for each new event.

With this plugin you get a daily, weekly, or monthly digest of your website’s activity. The digest includes the following events:

* New Core Updates
* New comments that need to be moderated (depending on your settings under 'Settings' -> 'Discussion')
* New user sign-ups
* Password resets by users

## Installation ##

### Manual Installation ###

1. Upload the entire `/digest` directory to the `/wp-content/plugins/` directory.
2. Activate Digest Notifications through the 'Plugins' menu in WordPress.
3. Head over to 'Settings' -> 'General' to configure the digest schedule.

## Frequently Asked Questions ##

### What’s the default schedule? ###

By default, the digest is sent at the beginning of the week at 18:00.

### I still get single notification emails for event X! ###

This plugin relies on specific hooks and filters in WordPress and also overrides two pluggable functions for user sign-ups and password reset notifications. If another plugin already overrides these, we can’t include these events in the digest.

### How can I add something to the digest? ###

The plugin is quite extensible. There are many well documented hooks developers can use to add something to the digest queue and modify the complete email message.

## Screenshots ##

1. The plugin settings under 'Settings' -> 'General'.
2. An example digest sent by the plugin.

## Contribute ##

If you would like to contribute to this plugin, report an issue or anything like that, please note that we develop this plugin [on GitHub](https://github.com/wearerequired/digest). Please submit pull requests to the develop branch.

Developed by [required](https://required.com/).

## Changelog ##

### 3.0.0 - 2023-03-27 ###
* Added: Support for sending digest on first day of a month
* Changed: Bump minimum requirements to WordPress 6.0 and PHP 7.4

### 2.0.0 - 2021-06-02 ###
* Changed: Plugin rewrite
* Changed: Tested with WordPress 5.7
* Changed: Bump minimum requirements to WordPress 4.7 and PHP 5.6

For previous updates see [CHANGELOG.md](https://github.com/wearerequired/digest/blob/master/CHANGELOG.md).
