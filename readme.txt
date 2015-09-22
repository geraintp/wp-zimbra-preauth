=== Zimbra Preauth Widget ===
Contributors: geraint
Tags: zimbra, preauth, authentication, pre-auth, widget
Stable tag: trunk
Tested up to: 4.1.0
Requires at least: 4.0.x

License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a simple link widget for Zibra Pre authentication

== Description ==

This plugin adds a login link/image widget that allows logged-in users to automatically login into their Zimbra account based on their wp account email address. [see the Zimbra wiki for more details on preauth](http://wiki.zimbra.com/wiki/Preauth) 

Key Features:

* Zimbra Preauthenitcation
* guests (! logged_in users) get sent to webmail login.

**Contributing**

The development home for this plugin is on GitHub. This is where active development will happen, please post any issues and associated discussions there.

https://github.com/geraintp/wp-zimbra-preauth

**Support**

Support for this plugin will be provided in the form of _Product Support_. This means that I intend to fix any confirmed bugs and improve the user experience when enhancements are identified and can reasonably be accomodated. There is no _User Support_ provided for this plugin. If you are having trouble with this plugin in your particular installation of WordPress, I will not be able to help you troubleshoot the problem.

This plugin is provided under the terms of the GPL, including the following:

> BECAUSE THE PROGRAM IS LICENSED FREE OF CHARGE, THERE IS NO WARRANTY
> FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE LAW.  EXCEPT WHEN
> OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR OTHER PARTIES
> PROVIDE THE PROGRAM "AS IS" WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED
> OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
> MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.  THE ENTIRE RISK AS
> TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU.  SHOULD THE
> PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY SERVICING,
> REPAIR OR CORRECTION.

== Installation ==

1. Install through the WordPress admin.

_or_

1. Upload `wp-zimbra-preauth` folder to the `/wp-content/plugins/` directory

_Then_

1. Activate the plugin through the 'Plugins' menu in WordPress
2. click on the settings link and add you generated Zimbra Auth Key and the preauth url.
3. go to Widgets and add the widget to you sidebar. 

== Screenshots ==

1. How the default Widget Looks
2. settings panel

== Frequently Asked Questions ==

= How do I find my Zimbra Pre-Auth Key? =

1. [follow the instruction in the Zimbra Documentation](http://wiki.zimbra.com/wiki/Preauth#Preparing_a_domain_for_preauth)

== Changelog ==

= 0.1.2 =
Opens link in new tab/window

= 0.1.1 =
Fix typos in Readme

= 0.1.0 =
Initial Release, basic functionality matching moodle Zimbra SSO block