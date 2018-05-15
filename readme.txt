=== Publish To Apple News ===
Contributors: potatomaster, kevinfodness, alleyinteractive, beezwaxbuzz, gosukiwi, pilaf, jaygonzales, brianschick
Donate link: https://wordpress.org
Tags: publish, apple, news, iOS
Requires at least: 4.0
Tested up to: 4.7.4
Stable tag: 1.3.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl.html

Enable your WordPress blog content to be published to your Apple News channel.

== Description ==

The Publish to Apple News plugin enables your WordPress blog content to be published to your Apple News channel.

**Features include:**

* Convert your WordPress content into Apple News format automatically.
* Create a custom design for your Apple News content with no programming knowledge required.
* Automatically or manually publish posts from WordPress to Apple News.
* Control individual posts with options to publish, update, or delete.
* Publish individual posts or in bulk.
* Handles image galleries and popular embeds like YouTube and Vimeo that are supported by Apple News.
* Automatically adjust advertisement settings.

To enable content from your WordPress blog to be published to your Apple News channel, you must obtain and enter Apple News API credentials from Apple.

Please see the [Apple Developer](https://developer.apple.com/) and [Apple News Publisher documentation](https://developer.apple.com/news-publisher/) and terms on Apple's website for complete information.

== Installation ==

Please visit our [wiki](https://github.com/alleyinteractive/apple-news/wiki) for detailed [installation instructions](https://github.com/alleyinteractive/apple-news/wiki/Installation) as well as [configuration](https://github.com/alleyinteractive/apple-news/wiki/Configuration) and [usage instructions](https://github.com/alleyinteractive/apple-news/wiki/Usage), [troubleshooting information](https://github.com/alleyinteractive/apple-news/wiki/Usage#troubleshooting) and a full list of [action and filter hooks](https://github.com/alleyinteractive/apple-news/wiki/action-and-filter-hooks).

== Frequently Asked Questions ==

Please visit our [wiki](https://github.com/alleyinteractive/apple-news/wiki) for detailed [installation instructions](https://github.com/alleyinteractive/apple-news/wiki/Installation) as well as [configuration](https://github.com/alleyinteractive/apple-news/wiki/Configuration) and [usage instructions](https://github.com/alleyinteractive/apple-news/wiki/Usage), [troubleshooting information](https://github.com/alleyinteractive/apple-news/wiki/Usage#troubleshooting) and a full list of [action and filter hooks](https://github.com/alleyinteractive/apple-news/wiki/action-and-filter-hooks).

== Screenshots ==

1. Manage all of your posts in Apple News from your WordPress dashboard
2. Create a custom theme for your Apple News posts with no programming knowledge required
3. Publish posts in bulk
4. Manage posts in Apple News right from the post edit screen

== Changelog ==

= 1.3.0 =
* Moved JSON customizations to themes so that JSON can be customized on a per-theme basis.
* Enabled access to postmeta in custom JSON so that values from postmeta fields can be inserted into customized JSON.
* Removed all formatting settings from the settings option in favor of storing them in themes. This is a potentially breaking change if you are using custom code that relies on formatting settings stored in the settings option.
* Removed the option for JSON customization in favor of moving those settings to themes. This is a potentailly breaking change if you are accessing the custom JSON option directly.
* Deprecated access of formatting settings using the Settings object.
* Added a new Theme object to handle all formatting settings.
* Bugfix: Fixed a bug where themes were not being automatically switched via section mappings.
* Bugfix: HTML in titles is now supported.

= 1.2.7 =
* Fixed a bug where HTML tags were being stripped before being sent to the API.
* Fixed a bug where older theme files couldn't be imported if new formatting settings were added.

= 1.2.6 =
* WP Standards: Ensured all instances of in_array use the strict parameter
* WP Standards: Replaced all remaining instances of == with ===
* WP Standards: Replaced all remaining instances of != with !==
* WP Standards: Ensured all calls to wp_die with translated strings were escaped
* WP Standards: Added escaping in a few additional places
* WP Standards: Replaced all remaining instances of json_encode with wp_json_encode
* Bugfix: Root-relative URLs for images, audio, and video are now supported
* Bugfix: Images, audio, and video with blank or invalid URLs are no longer included, avoiding an error with the API
* Bugfix: Image blocks with multiple src attributes (e.g., when using a lazyload plugin with a raw &lt;img&gt; tag in the &lt;noscript&gt; block) are now intelligently probed

= 1.2.5 =
* Bugfix: Fixed version of PHPUnit at 5.7.* for PHP 7.* and 4.8.* for PHP 5.* in the Travis configuration to fix a bug with incompatibility with PHPUnit 6
* Bugfix: Set the base URL for the Apple News API to https://news-api.apple.com everywhere for better adherence to official guidance in the API docs (props to ffffelix for providing the initial PR)
* Bugfix: Made the administrator email on the settings screen no longer required if debug mode is set to "no"
* Bugfix: Converted the error that occurs when a list of sections cannot be retrieved from the API to a non-fatal to fix a problem where the content of the editor would appear white-on-white
* Bugfix: Resolved an error that occurs on some systems during plugin activation on the Add New screen due to a duplicated root plugin file in the WordPress.org version of the plugin

= 1.2.4 =
* Added an interface for customizing of component JSON
* Added support for making certain components inactive
* Added hanging punctuation option for pull quotes
* Added additional styling options for drop caps
* Added support for nested images in lists
* Added support for Instagram oEmbeds
* Updated the interface and workflow for customizing cover art

= 1.2.3 =
* Allowed mapping themes to Apple News sections
* Added support for videos in feed
* Added support for maturity rating
* Added support for cover art
* Added support for the Facebook component
* Added support for captions in galleries
* Bugfix for invalid JSON errors caused by non-breaking spaces and other Unicode separators

= 1.2.2 =
* Created Apple News themes and moved all formatting settings to themes
* Added support for sponsored content (isSponsored)
* Added ability to map categories to Apple News sections
* Split block and pull quote styling
* Allowed for removing the borders on blockquotes and pull quotes
* Added post ID to the apple_news_api_post_meta and apple_news_post_args filters
* Fixed handling of relative URLs and anchors in the post body
* Provided a method to reset posts stuck in pending status
* Added a delete confirmation dialog
* Added a separate setting for automatically deleting from Apple News when deleted in WordPress
* Fixed captions so that they're always aligned properly with the corresponding photo
* Added separate settings for image caption style

= 1.2.1 =
* Added an experimental setting to enable HTML format on body elements.
* Added settings for monospaced fonts, which applies to &lt;pre&gt;, &lt;code&gt;, and &lt;samp&gt; elements in body components when HTML formatting is enabled.
* Added additional text formatting options, including tracking (letter-spacing) and line height.
* Split text formatting options for headings to allow full customization per heading level.
* Modified logic for image alignment so that centered and non-aligned images now appear centered instead of right-aligned.
* Added an option for full-bleed images that will cause all centered and non-aligned images to display edge-to-edge.
* Added logic to intelligently split body elements around anchor targets to allow for more opportunities for ad insertion.
* Modified column span logic on left and right orientation to align the right side of the text with the right side of right-aligned images.
* Fixed a bug caused by hardcoded column spans on center orientation.
* Fixed a PHP warning about accessing a static class method using arrow syntax.
* Added unit test coverage for new functionality.
* Refactored several core files to conform to WordPress standards and PHP best practices.

= 1.2.0 =
* Added a live preview of the font being selected (macOS only).
* Added a live preview of formatting settings (font preview in macOS only).
* Switched to the native WordPress color picker for greater browser compatibility.
* Added a framework for JSON validation and validation for unicode character sequences that are symptomatic of display issues that have been witnessed, though not reproduced, in Apple News.
* Broke out Action_Exception into its own class file for cleanliness.
* Added direct support links to every error message.
* Added better formatting of multiple error messages.
* Added unit tests for the Apple_News and Admin_Apple_Notice classes.
* Added new unit tests for Push.

= 1.1.9 =
* Updated the logic for bundling images for the Apple News API's new, stricter MIME parsing logic.

= 1.1.8 =
* Fixed a bug with the Apple News meta box not saving values when "Automatically publish to Apple News" was set to "Yes".

= 1.1.7 =
* Fixed a bug with posts created via cron skipping post status validation (thanks, agk4444 and smerriman!).

= 1.1.6 =
* Fixed a bug with automatically publishing scheduled posts (thanks, smerriman!).
* Apple News meta box is now displayed by default on posts.
* Displaying the Apple News meta box even on draft posts to allow saving settings before publish.
* Updated minimum PHP version to 5.3.6 due to usage of DOMDocument::saveHTML.
* Fixed invalid formatting for plugin author name and plugin URI.
* isPreview=false is no longer sent passively to the API. Only isPreview=true is sent when explicitly specified.
* Fixed an issue where author names with hashtags were breaking the byline format.
* Added image settings, bundled images (if applicable) and JSON to the debug email.
* Checking for blank body nodes early enough to remove and log them as component errors.
* Retrieving and displaying more descriptive error messages from the API response.

= 1.1.5 =
* Updated logic for creating a unique ID for anchored components to avoid occasional conflicts due to lack of entropy.
* Fixed issue with lack of container for components when cover isn't the first component causing text to scroll awkwardly over the image with the parallax effect.
* Added the ability to set the body background color.
* Fixed issue with empty but valid JSON components causing an "Undefined index" error in Apple News validation.
* Fixed issue with an invalid API response or unreachable endpoint causing the post edit screen to break when trying to load the Apple News meta box.

= 1.1.4 =
* Released updates to the default settings for the Apple News template
* Added customizable settings for pull quote border color, style and width
* Refactored logic to obtain size of bundled images for wider web host compatibility

= 1.1.3 =
* Fixed issue with the Apple News plugin not respecting the site's timezone offset

= 1.1.2 =
* Added support for remote images
* Fixed error on loading the Apple News list view before channel details are entered

= 1.1.1 =
* Fixed issue with publishing to sections

= 1.1 =
* Added composer support (thanks ryanmarkel!)
* Removed unnecessary ob_start() on every page load
* Fixed issue with manual publish button on post edit screen
* Fixed issue with bottom bulk actions menu on Apple News list table
* Added ability to publish to any section
* Added ability to publish preview articles

= 1.0.8 =
* Added support for date metadata (https://developer.apple.com/library/ios/documentation/General/Conceptual/Apple_News_Format_Ref/Metadata.html#//apple_ref/doc/uid/TP40015408-CH3-SW1)
* Fixed issue with shortcodes appearing in excerpt metadata
* Added the ability to alter a component's style property via a filter
* Refactored plugin settings to save as a single option value
* Settings are now only deleted on uninstall and not deactivation
* Removed unit tests that were making remote calls to the API
* Added improved support for known YouTube and Vimeo embed formats

= 1.0.7 =
* Addressed issue with component order settings field for users with PHP strict mode enabled.

= 1.0.6 =
* Updated the plugin from 0.10 to 1.1 Apple News format.
* Added alert options when unsupported embeds are found in the post content while publishing.
* Added better handling for MIME_PART_INVALID_SIZE errors.
* Added the ability to reorder the title, cover and byline components.
* Updated ads to use new features available in the 1.1 Apple News format.
* Minor settings page updates.
* Resolved some PHP warnings on the dashboard.
* Updated all unit tests and improved test coverage.

= 1.0.5 =
* Fixed a performance issue caused by introduction of live post status, added 60 second cache and removed from email debugging.

= 1.0.4 =
* Added canonicalURL to metadata (thanks @dashaluna)
* Added automatic excerpt to metadata following normal WordPress logic if a manual one is not present
* Removed unnecessary redirect logic and allowed Apple News notices to display on any screen, updated vague error messages for clarity
* Added plugin information to generator metadata
* Added new field for adjusting byline format
* Added the ability to set the field size and required attributes on the Apple News settings page
* Fix matching of Instagram URL, so component is generated correctly (thanks @dashaluna)
* Added logic to extract the thumbnail/cover from the body content when not explicitly set via the featured image
* Added display of current Apple News publish state to admin screens
* Added set_layout as a separate method for consistency in the Twitter component (thanks @dashaluna)
* Use register_full_width_layout instead of register_layout for byline and cover for consistency (thanks @dashaluna)
* Matching dashes and extra query parameters in YouTube URLs (thanks @smerriman)

= 1.0.3 =
* Added multiple checks for publish status throughout built-in publishing scenarios. Still allowing non-published posts to be pushed at the API level to not prevent custom scenarios. Fixed issue with auto publishing not respecting post type settings.

= 1.0.2 =
* Improvements to asynchronous publishing to ensure posts cannot get stuck in pending status and to return all error messages that may occur.

= 1.0.1 =
* Bug fixes for removing HTML comments, fixing video embed URL regular expressions and fixing auto sync and auto update logic.

= 1.0.0 =
* Major production release. Introduces asynchronous publishing for handling large posts, developer tools, new filters and bug fixes.

= 0.9.0 =
* Initial release. Includes changes for latest WP Plugin API compatibility and Apple News Publisher API.

== Upgrade Notice ==

= 0.9.0 =
Initial release. Recommended for production.


== Developers ==

Please visit us on [github](https://github.com/alleyinteractive/apple-news) to [submit issues](https://github.com/alleyinteractive/apple-news/issues), [pull requests](https://github.com/alleyinteractive/apple-news/pulls) or [read our wiki page about contributing](https://github.com/alleyinteractive/apple-news/wiki/contributing).
