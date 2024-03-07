=== Publish To Apple News ===
Contributors: potatomaster, kevinfodness, jomurgel, tylermachado, benpbolton, alleyinteractive, beezwaxbuzz, gosukiwi, pilaf, jaygonzales, brianschick, wildist
Donate link: https://wordpress.org
Tags: publish, apple, news, iOS
Requires at least: 6.3
Tested up to: 6.4.2
Requires PHP: 8.0
Stable tag: 2.4.6
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl.html

Enables journalistic outlets already approved by Apple News to send content directly to the app.

== Description ==

The 'Publish to Apple News' plugin enables WordPress sites with approved Apple News channels to publish content directly on Apple News.

**Features include:**

* Convert your WordPress content into Apple News format automatically.
* Create a custom design for your Apple News content with no programming knowledge required.
* Automatically or manually publish posts from WordPress to Apple News.
* Control individual posts with options to publish, update, or delete.
* Publish individual posts or in bulk.
* Handles image galleries and popular embeds like YouTube and Vimeo that are supported by Apple News.
* Automatically adjust advertisement settings.

To enable content from your WordPress site to be published to your Apple News channel, you must obtain and enter Apple News API credentials from Apple.

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

= 2.4.6 =

* Bugfix: #1057 - Resolved type error on Automation admin screen
* Bugfix: #1056 - Resolved error fetching list of sections
* Bugfix: #1055 - Moved async functions outside useEffect to avoid synchronous errors
* Bugfix: #1051 - Updated npm dependencies to match WordPress
* Bugfix: #1050 - Resolved uncaught promise error when saving automation rules
* Bugfix: #1048 - Fix posting bug caused by invalid document name.
* Bugfix: #1030 - Fixed render error with "publish-to-apple-news" plugin.
* Bugfix: #1028 - Fixed inability to select checklist options in Post Apple News Options plugin.
* Bugfix: #1018 - Fixed bug deleting automation rules which removed all page contents.
* Bugfix: #1014 - Resolved invalid document exception while processing articles.
* Enhancement: Upgraded to Node.js 20 and npm 10.
* Enhancement: Added info for using plugin with News Preview app.

= 2.4.5 =
* Fixes regular expression for adding identifiers.

= 2.4.4 =

* Enhancement: Adds support for the anchor links.

= 2.4.3 =

* No changes, re-releasing to trigger a new release.

= 2.4.2 =

* No changes, re-releasing to trigger a new release.

= 2.4.1 =
* Enhancement: Migrate to use Mantle Testkit for unit tests
* Enhancement: Add support for PHP 8.2.
* Deprecation: Dropped support for PHP 7.1, PHP 8.0 is now the minimum supported version.
* Deprecation: Bumped minimum supported WordPress version to 5.6 (the first release with PHP 8.0 support).

= 2.4.0 =
* Enhancement: Adds a new Automation configuration to set sections, themes, and other metadata based on taxonomic association for a selected term in the selected taxonomy. This system replaces the Sections configuration previously in use and Sections settings will auto-migrate to the new system.
* Enhancement: Adds UI controls to set options for whether posts should be deleted from Apple News when unpublished in WordPress.
* Enhancement: Adds support for Apple Podcast embeds.
* Enhancement: Adds support for TikTok embeds.
* Enhancement: Makes layout width a configurable property in the theme editor.
* Enhancement: Includes the original article ID in the duplicate article error message.
* Enhancement: Allows publishers to opt out of automatically adding video metadata on posts that contain videos on a per-post basis.
* Enhancement: Disables dropcap when the first paragraph is under a certain character limit and when it starts with punctuation. Adds theme settings to configure this behavior.
* Enhancement: Updates the link button style for all themes to match the Apple standard button style.
* Enhancement: Allows publishers to configure whether images should use the Image or Photo component on a per-post basis. (Photos are tap-to-enlarge whereas Images are not.)
* Bugfix: Capitalizes the word "By" in the default byline and author components.
* Bugfix: Fixes the display of the table component in dark mode to properly apply selected dark mode colors.
* Bugfix: Fixes an issue where posts duplicated by Yoast Duplicate Post are associated with the same article ID in Apple News and updates overwrite the original post.
* Deprecation: Removes official support for PHP 5.6 and 7.0.

= 2.3.3 =
* Enhancement: Tested up to WordPress 6.1.
* Experimental: Adds opt-in filters for deleting posts that have been unpublished.

= 2.3.2 =
* Bugfix: Fixes a bug where the layout body-layout-last is not added to the list of layouts if the body content ends with something other than a paragraph.
* Bugfix: Fixes a bug where galleries were no longer being properly converted to Apple News Format due to a change in gallery markup that was introduced in WordPress 5.8.
* Bugfix: Fixes an issue with enqueueing the Gutenberg PluginSidebar script on Windows webservers.
* Bugfix: Fixes an error in class-components.php if the function is called with an empty list of components.

= 2.3.1 =
* Bugfix: Fixes an issue where images with different URLs but the same filename are bundled with the same name when not using remote images, which can lead to images appearing out of order.

= 2.3.0 =
* Bugfix: Fixes an issue with some of the example themes where pullquotes would create invalid JSON due to the default-pullquote textStyle not being set. Props to @soulseekah for the fix.
* Bugfix: Fixes an issue where a custom filter is used to make all image URLs root-relative when using featured images to populate the Cover component, which was leading to an INVALID_DOCUMENT error from the News API due to the root-relative URL (e.g., /path/to/my/image.jpg instead of https://example.org/path/to/my/image.jpg).
* Bugfix: Fixes an issue with images not deduping when Jetpack Site Accelerator (Photon) is enabled.
* Bugfix: Synchronizes the list of available fonts to what is actually available.
* Bugfix: Fixes display of date pickers in the article list.
* Bugfix: Fixes apple_news_is_exporting function to make it fire for both downloading JSON in the article list and pushing articles to Apple via the API.
* Bugfix: Fixes an editor crash when using Gutenberg and a custom post type that does not support postmeta (custom-fields).
* Bugfix: Fixes an issue with embedding YouTube and Vimeo videos when using Gutenberg blocks.
* Bugfix: Fixes an issue where making a mistake in customizing JSON results in the custom JSON being reset to the default value rather than the previously saved value.
* Enhancement: Added support for mailto:, music://, musics://, stocks:// and webcal:// links.
* Enhancement: Added an option and a filter for skipping auto-push of posts with certain taxonomy terms.
* Enhancement: Added support for HTML tags when customizing theme JSON.
* Enhancement: Added an author component for author without date.
* Enhancement: Added a date component for date without author byline.
* Enhancement: Added support for determining the aspect ratio of an embedded video based on the value configured on the embed block.

= 2.2.2 =
* Bugfix: Moved custom metadata fields to the request level rather than the article level to align them with existing metadata properties like isPaid and isHidden.
* Bugfix: Removed JSON alerts setting, as it no longer does anything.
* Enhancement: Shows a confirmation message to the user when channel credentials are successfully saved, since the channel ID, key, and secret fields are no longer visible following the update to using .papi files to configure credentials.

= 2.2.1 =
* Bugfix: Fixed a bug with .papi file upload that occurred when any of the three fields (channel_id, key, secret) contained a character that was not alphanumeric or a hyphen (e.g., /), which would cause the field to get cut short, thereby causing API requests to fail.

= 2.2.0 =
* Enhancement: Added support for HLS video (.m3u8) in thumbnails.
* Enhancement: Added support for custom article metadata.
* Enhancement: Added a slug component (props to @hughiemolloy for the initial work).
* Enhancement: Added support for HTML in headings.
* Enhancement: Added support for uploading .papi files to configure channel credentials.

= 2.1.3 =
* Enhancement: Added article authors to the `metadata.authors` property so they display in the article listing view on Apple News.
* Enhancement: Added a new filter for whether to enable Co-Authors Plus support, which defaults to `true` if the `coauthors` function is defined (the same as the previous behavior, but now the setting is filterable).
* Enhancement: Updated the plugin description to more clearly articulate its purpose and intended users.
* Bugfix: Fixed a bug with applying automatic section mappings based on taxonomy in a Gutenberg context.
* Bugfix: Fixed a bug with video metadata parsing related to having a `video` element with a `src` attribute rather than `source` inner elements.

= 2.1.2 =
* Bugfix: Fixed an error that would occur if sections could not be fetched from the API, where the function being called to handle the error didn't exist.
* Bugfix: Removed unnecessary argument to `libxml_clear_errors` which was causing an error in PHP 8.

= 2.1.1 =
* Enhancement: Clarifies source of admin messages coming from the plugin to ensure that they say "Apple News."
* Enhancement: Adds checks for plugin initialization before making REST responses.
* Enhancement: Adds documentation to all hooks, which has been synchronized with the wiki.
* Bugfix: Resolves an issue where API information (e.g., article ID) were removed upon save when using the Gutenberg editor, which would cause articles in WordPress to become unlinked from articles in Apple News, and would result in a duplicate article error if publishing again was attempted.
* Bugfix: Uses new `wp_after_insert_post` hook, if available (was added in WP 5.6) to ensure that postmeta and terms are saved before running an autosync to Apple.
* Bugfix: Pullquote styles are now being included properly if alignments other than the default are used.
* Bugfix: Stops using `get_user_attribute` on VIP Go, preferring `get_user_meta` instead.
* Bugfix: Resolves an error when using theme preview related to a bad script localization reference.
* Bugfix: Ensures that notices are available via the REST API for custom post types.
* Bugfix: Replaces admin sections error display function.
* Bugfix: Updates `pluginSidebar` sections default values to prevent save errors in some cases.
* Bugfix: Outdated links to Apple News docs have been replaced with their modern equivalents.

= 2.1.0 =
* Enhancement: Adds support for Dark Mode, including the ability to customize Dark Mode colors in a theme.
* Enhancement: The cover component now supports captions. If a featured image is used for the cover, the caption will come from the attachment itself in the database. If the first image from the content is used, the caption will be read from the HTML. There is also a new filter, apple_news_exporter_cover_caption, which allows for filtering of the caption text.
* Enhancement: Adds a new End of Article module, available via the Customize JSON feature, to allow publishers to insert content at the end of every article, customized per theme.
* Enhancement: HTML is now allowed in lightbox image captions.
* Enhancement: Allows configuration of cover images in the sidebar / metabox explicitly, rather than pulling them out of the featured image or main content.
* Enhancement: Adds support for Brightcove videos via the Brightcove Video Connect plugin for videos added via either the Gutenberg block or the shortcode. Note that this feature will only work if you contact Apple support to link your Brightcove account with your Apple News channel.
* Enhancement: Replaces usage of the deprecated `advertisingSettings` object with the new `autoplacement.advertising` object. Bumps default advertisement frequency from 1 to 5 (out of 10).
* Enhancement: Adds an error to the notice bar if the `DATE_NOT_RECENT` API error is encountered advising the user to synchronize the time on their server to restore API connectivity.
* Enhancement: Adds padding above and below `EmbedWebVideo` components.
* Enhancement: Converts notices in a block editor context to native block editor notice components, rather than the previous custom implementation.
* Enhancement: In the block editor, warns when the post has unsaved changes, and publishing to Apple News would result in unsaved changes not being published.
* Bugfix: Removes Cover Art configuration, as Cover Art is no longer used by Apple.
* Bugfix: Fixes the logic in the default theme checker to properly check the configured values against the defaults and prompt the user if they are using the default theme that ships with Apple News without modification.
* Bugfix: Fixes a bug where renaming a theme would not carry over any changes made to the theme, and renaming the active theme would make it no longer active.
* Bugfix: If the same image is used for the featured image and the first image embedded into the post, it no longer shows up twice.
* Bugfix: Removed extra space between an image and its caption.
* Bugfix: If a custom excerpt is used, but the custom excerpt repeats text in its entirety from elsewhere in the article (e.g., the first paragraph), then the Intro component will be skipped. This prevents an issue where Apple will flag articles that contain repeated text due to the Intro component using a custom excerpt suitable in a WordPress context but not an Apple News context.
* Bugfix: Select menus are no longer oversized on the settings page.
* Updated filter name for `get_sections_for_post` to `apple_news_get_sections_for_post`.

= 2.0.8 =
* Enhancement: Adds styles for Button elements that are links which are added by the Gutenberg editor.
* Enhancement: Bumps the Apple News Format version from 1.7 to 1.11 to make support for new features possible, like LinkButton.

= 2.0.7 =
* Fixes a bug where sections by category is not checked by default for new posts.
* Fixes visual bug when manual section selection are visible.

= 2.0.6 =
* Bugfix: Rolled back support for Button elements for now due to a problematic implementation.

= 2.0.5 =
* Enhancement: Added support for audio, video, and table captions in Gutenberg.
* Enhancement: Adds styles for Button elements that are links.
* Bugfix: Blockquotes using alignments other than left are now properly recognized.
* Bugfix: Facebook URLs inline within other elements no longer getting converted to Facebook embeds.
* Diversity and Inclusion: Replaced instances of "whitelist" with "allowlist" throughout the codebase, change head branch from "master" to "develop". Language matters.

= 2.0.4 =
* Bump "tested up to" tag to 5.4.
* Upgrades node version used for compiling assets to version 12, and patches vulnerabilities reported via npm audit.
* Adds TikTok compatibility to the generic embed handler (props to @hrkhal for the fix).
* Fixes an undefined property notice when there is an error but no error message (props to @khoipro for the fix).
* Adds size attributes to select fields and API configuration fields for better readability (props to @paulschreiber for the fix).
* Fixes a bug where captions were not being correctly read from images.
* Adds a warning for the isPaid flag to prevent confusion if a channel is not set up for paid content.
* Fixes a bug where settings are not initialized to an array when the plugin is loaded via code.

= 2.0.3 =
* Bugfix: Resolves fatal error when trying to load posts that aren't active in some cases.

= 2.0.2 =
* Bugfix: Adds check for some 5.0.0+ functions before attempting to execute.
* Bugfix: Adds fallback and additional checks for sidebarPlugin retrieval of post meta.
* Bugfix: Only makes REST request for post save when Gutenberg is enabled.
* Enhancement: Enqueues block editor scripts with `enqueue_block_editor_assets`.

= 2.0.1 =
* Bugfix: Including the built pluginSidebar.js files with the WordPress.org distribution which were erroneously left off.

= 2.0.0 =
* Enhancement: Adds full support for Gutenberg. If Gutenberg is active for a post, uses a Gutenberg PluginSidebar to house all the Apple News options rather than a metabox. Also adds support for new HTML generated by Gutenberg, including various embeds.
* Enhancement: Adds support for the isPaid flag to indicate that a post is part of News+ and requires a subscription to view.
* Enhancement: Set the default for Use Remote Images to Yes, as this should be the setting that all publishers use now that Apple News supports remote image URLs.
* Bugfix: Refreshes the nonce after re-authenticating to prevent data loss when Gutenberg is not active. Props to @hrkhal for the fix.

= 1.4.4 =
* Enhancement: Added the apple_news_notification_headers filter to allow headers to be added to the notification email message. Props to @paulschreiber for the addition.
* Bugfix: Improved handling of UTF-8 multibyte characters to provide better support for the French language.
* Updated the description that appears below the section selection checkboxes to more accurately explain the current behavior.
* Updated to the latest version of the phpcs and vipwpcs standards, resolving most issues, while allowlisting some rules for resolution in the next version. Props to @paulschreiber for doing most of the work here.

= 1.4.3 =
* Bugfix: Decodes HTML entities in URLs before performing remote file exists check for embedded media. Props to @kasparsd for the fix.

= 1.4.2 =
* Bugfix: Issues with making updates via the quick edit interface and on unsupported post types are now fixed, as the publish action bails out early if the nonce is not set, which occurs when the metabox does not load. Props to @danielbachhuber and @srtfisher for the fixes.
* Added 'apple_news_should_post_autopublish' filter to override automatic publish settings on a per-article basis. Props to @srtfisher for the update.

= 1.4.1 =
* Bugfix: Post types that were not registered with Publish to Apple News were failing the nonce check on publish/update because the metabox was not present. Refined the save_post hook to register only for post types with Publish to Apple News support to avoid this situation.

= 1.4.0 =
* Set HTML to the default output format (instead of Markdown) for new installs. HTML format is now recommended for all installs. Support for Markdown may be removed in a future version. Individual components now have a filter to toggle HTML support on or off for each component.
* Added support for HTML format to Heading and Quote components.
* Added support for tables (requires HTML support to be turned on) with new table styles defined in example themes, and intelligent defaults applied to existing themes based on comparable settings.
* Made Cover Art feature opt-in for new installs to avoid creating a plethora of additional image crops which may not be necessary. Sets the setting to enabled for existing installations during upgrade, since there is not a good way to know whether a user has utilized this feature without running a very expensive postmeta query.
* Added a "Refresh Sections" button on the Sections page to clear the cached settings list.
* Set the default publish and delete capabilities to the corresponding post publish and delete capabilities for the post type being edited, rather than requiring "manage_options," which is typically an administrator-only capability. This allows users with the ability to publish posts to also publish those posts to Apple News.
* Removed overzealous check for invalid Unicode sequences. Over the past several releases, enhancements have been made to better identify and fix problems with content that would cause issues upon pushing to Apple News. Therefore, the check for invalid Unicode character sequences is now not providing much value, and is inhibiting valid content (including emoji) from being pushed to Apple News.
* Added a function (apple_news_is_exporting) for determining whether an export is happening, which can be used in themes and plugins to change behavior if a hook is being executed in the context of an Apple News request.
* Added context to the message that is displayed when a post push is skipped explaining why it was skipped.
* Added a framework for saving dismissed state of persistent admin notices (such as those that appear after an upgrade) so that the close button causes the notice to not appear again for that user.
* Set the language code from blog settings for document properties (thanks @ffffelix).
* Added support for the isHidden property (thanks @jonesmatthew).
* Added support for Jetpack Tiled Galleries.
* Swapped deprecated wpcom_vip_* functions for core versions.
* Added expand/collapse functionality to the theme editor to reduce scrolling between where settings are set and the preview area.
* Brought entire codebase up to WordPress coding standards, which is now being verified through PHP CodeSniffer on each pull request.
* Updated Travis configuration for more robust testing.
* Bumped minimum required version to PHP 5.6 due to incompatibility with certain tools (e.g., Composer) required for running builds and tests.
* Security: Added nonce verification to all remaining form data processing sections.
* Bugfix: Added a handler for WordPress.com/OpenGraph Facebook embeds so that they properly render as Facebook components instead of a blockquote.
* Bugfix: Addressed an issue with sanitization that did not properly strip out script tags that contain CDATA with a greater than symbol.
* Bugfix: Empty meta_component_order settings can now be saved properly on the theme edit screen.
* Bugfix: No longer assumes that any embed that isn't YouTube is actually Vimeo. Performs a strict check for Vimeo embed signatures and drops the embed if it does not match known providers.
* Bugfix: Re-added erroneously removed apple_news_fonts_list hook.
* Bugfix: Fixed an error where the list of sections was occasionally being encoded as an object instead of an array.
* Bugfix: Fixed the undefined message warning if an article was deleted from iCloud, thereby breaking the linkage between the plugin and the Apple News API, to explain why the link was broken.
* Bugfix: Fixed undefined index and undefined variable notices in a few places.
* Bugfix: Fixed an assignment bug in class-admin-apple-post-sync.php (thanks @lgladdy and @dhanendran).
* Bugfix: Prevented empty text nodes from being added in a variety of ways, which was causing errors on publish in some cases, and unwanted extra space in articles in others.
* Bugfix: Prevented Apple News API timeouts from causing the entire WordPress install to hang by only using long remote request timeouts when making a POST request to the Apple News API.
* Bugfix: Fixed improper handling of several different types of links, such as empty URLs, malformed URLs, root-relative URLs, and anchor links.
* Bugfix: Properly decoded ampersands and other HTML-encoded entities when using Markdown format.
* Bugfix: Removed style tags and their contents where they appear inline.

= 1.3.0 =
* Moved JSON customizations to themes so that JSON can be customized on a per-theme basis.
* Enabled access to postmeta in custom JSON so that values from postmeta fields can be inserted into customized JSON.
* Removed all formatting settings from the settings option in favor of storing them in themes. This is a potentially breaking change if you are using custom code that relies on formatting settings stored in the settings option.
* Removed the option for JSON customization in favor of moving those settings to themes. This is a potentially breaking change if you are accessing the custom JSON option directly.
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
* Added support for date metadata (https://developer.apple.com/documentation/apple_news/metadata)
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
