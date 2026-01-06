=== BuddyPress Birthday ===
Contributors: amitgrhr
Tags: buddypress, birthday, members, community, social
Requires at least: 5.2
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display upcoming birthdays of your BuddyPress members with a beautiful, customizable block.

== Description ==

BuddyPress Birthday is a lightweight plugin that displays upcoming birthdays of your BuddyPress community members. Perfect for building engagement and celebrating your community!

= Features =

* **Gutenberg Block** - Modern block editor integration
* **Customizable Display** - Control what information to show
* **Multiple Date Ranges** - Today, weekly, monthly, or all upcoming
* **Age Display** - Optionally show member ages
* **Birthday Wishes** - Direct message integration for sending wishes
* **Friends Filter** - Show only friends' birthdays
* **Flexible Formatting** - Custom date format support
* **Birthday Emoji** - Fun cake emoji for birthdays
* **Responsive Design** - Matches BuddyPress default styles
* **REST API** - Full API support for custom integrations

= Block Settings =

The birthday block includes extensive customization options:

* **Title** - Customize the block heading
* **Show Age** - Display the upcoming age
* **Send Birthday Wishes** - Enable message button (requires BuddyPress Messages)
* **Date Format** - PHP date format (e.g., "F d" for "January 15")
* **Birthday Range** - Today only, next 7 days, this month, or all upcoming
* **Show Birthdays Of** - All members or friends only
* **Display Name Type** - Display name, username, or full name from xProfile
* **Number to Show** - Limit results (1-50)
* **Birthday Emoji** - Show cake emoji next to names

= Requirements =

* WordPress 5.2 or higher
* BuddyPress plugin installed and activated
* BuddyPress Extended Profiles component enabled
* A date-type xProfile field for birthdays

= Multilingual Ready =

BuddyPress Birthday is translation-ready and follows WordPress internationalization standards.

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins > Add New
3. Search for "BuddyPress Birthday"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Go to Plugins > Add New > Upload Plugin
3. Choose the downloaded file and click "Install Now"
4. Activate the plugin

= Configuration =

1. Make sure BuddyPress is installed and the Extended Profiles component is active
2. Create a date-type xProfile field for birthdays (or use an existing one)
3. Go to Settings > BP Birthday
4. Select your birthday xProfile field
5. Configure default settings (optional)
6. Add the "BuddyPress Birthday" block to any post or page

= Usage =

1. Edit a post or page with the block editor
2. Add the "BuddyPress Birthday" block (find it under WBCOM Designs category)
3. Customize the block settings in the sidebar
4. Publish your page

== Frequently Asked Questions ==

= Do I need BuddyPress installed? =

Yes, this plugin requires BuddyPress to be installed and activated.

= Which BuddyPress components are required? =

The Extended Profiles component is required. The Messages component is optional (needed for "Send Wishes" feature).

= How do I add birthday data for members? =

Members can add their birthday through their profile by editing the birthday xProfile field. Admins can also edit member profiles to add birthday information.

= What date format should I use? =

Use PHP date format strings. Examples:
* "F d" = January 15
* "m/d" = 01/15
* "d F" = 15 January
* "M j" = Jan 15

= Can I show only friends' birthdays? =

Yes! In the block settings, set "Show Birthdays Of" to "Friends Only". This requires the BuddyPress Friends component to be active.

= Does it work with custom themes? =

Yes! The plugin uses BuddyPress standard CSS classes and should work with any properly coded theme.

= Is there an API? =

Yes! The plugin provides a REST API endpoint at `/wp-json/buddypress-birthday/v1/birthdays` with parameters for range, limit, and scope.

= Can I customize the styling? =

Yes! The plugin uses standard BuddyPress CSS classes. You can add custom CSS to your theme to override the default styles.

== Screenshots ==

1. Birthday block in the editor with settings panel
2. Frontend display of upcoming birthdays
3. Admin settings page for field configuration
4. Birthday block with different display options

== Changelog ==

= 1.0.0 =
* Initial release
* Gutenberg block for displaying birthdays
* Admin settings for field configuration
* Multiple date range filters
* Age display option
* Birthday wishes integration
* Friends filter support
* Custom date formatting
* REST API support
* Birthday emoji option
* Responsive design matching BuddyPress styles

== Upgrade Notice ==

= 1.0.0 =
Initial release of BuddyPress Birthday plugin.

== Privacy Policy ==

BuddyPress Birthday does not collect, store, or share any user data outside of your WordPress installation. All birthday information is stored in your BuddyPress xProfile fields.

== Support ==

For support, feature requests, or bug reports, please visit the [plugin support forum](https://wordpress.org/support/plugin/buddypress-birthday/).

== Credits ==

Developed by Amit Kumar Agrahari

== Contribute ==

Contributions are welcome! Please visit our GitHub repository to contribute code, report issues, or suggest features.
