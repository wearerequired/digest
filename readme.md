# Digest Notifications #
Contributors:      wearerequired, swissspidy, grapplerulrich, ocean90  
Tags:              admin, emails, comments, notification, updates  
Requires at least: 4.7  
Tested up to:      5.7  
Requires PHP:      5.6  
Stable tag:        2.0.0  
License:           GPLv2 or later  
License URI:       http://www.gnu.org/licenses/gpl-2.0.html  

Get a daily or weekly digest of what's happening on your site instead of receiving a single email each time.

## Description ##

When you have lots of new user sign-ups or comments every day, it’s very distracting to receive a single email for each new event.

With this plugin you get a daily or weekly digest of your website’s activity. The digest includes the following events:

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

### 2.0.0 - 2021-06-02 ###
* Changed: Plugin rewrite
* Changed: Tested with WordPress 5.7
* Changed: Bump minimum requirements to WordPress 4.7 and PHP 5.6

### 1.2.1 - 2015-10-06 ###
* Fixed: A small issue with password notifications emails in WordPress 4.3.1. The behaviour changed slightly in that release.

### 1.2.0 - 2015-09-02 ###
* Fixed: Supports the new password notification emails introduced in WordPress 4.3
* Enhancement: Began writing user tests to ensure compatibility.

### 1.1.0 - 2015-06-30 ###
* Enhancement: Don’t include already processed items in the email.

### 1.0.0 - 2015-06-25 ###
* First release

## Upgrade Notice ##

### 2.0.0 ###
Plugin rewrite

### 1.2.1 ###
Includes a small bug fix for improved compatibility with WordPress 4.3.1.

### 1.2.0 ###
Now 100% compatible with WordPress 4.3. Update while it’s hot!

### 1.1.0 ###
Already moderated comments won’t be listed in the emails now.

### 1.0.0 ###
First release
