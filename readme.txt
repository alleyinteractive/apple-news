=== Publish To Apple News ===
Contributors: potatomaster, kevinfodness, jomurgel, tylermachado, benpbolton, alleyinteractive, beezwaxbuzz, gosukiwi, pilaf, jaygonzales, brianschick, wildist
Donate link: https://wordpress.org
Tags: publish, apple, news, iOS
Requires at least: 6.3
Tested up to: 6.5.3
Requires PHP: 8.0
Stable tag: 2.5.0
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

= 2.5.0 =

* Enhancement: Added support for an aside component. To use, specify the class for the container that includes the aside content in the plugin settings.
* Enhancement: Added support for the Footnotes block.
* Enhancement: Added a new In Article module, similar to the End of Article module, but which is configurable to appear within article content instead.
* Enhancement: Added support for custom fonts added to the channel in the theme customizer.
* Enhancement: Added support for respecting the text alignment of individual paragraphs.
* Enhancement: Added support for customizing default tag styles via Customize JSON. To use, edit the Body component in the Customize JSON interface.
* Enhancement: Added support for customizing individual items in a gallery via Customize JSON.
* Enhancement: Added an option to Automation where the contentGenerationType can be set to AI for AI-generated content based on applied taxonomy terms.
* Enhancement: Added an option to Automation to prepend text to an Apple News article title based on applied taxonomy terms.
* Enhancement: Split layouts for headings by level, so different layouts can be applied to each heading level in Customize JSON.
* Enhancement: Improves the display of captions.
* Enhancement: Added debugging for Apple News API requests.
* Bugfix: Fixed an issue where a channel that was set to paid by default required every article to also be marked as paid, rather than accepting the channel default.
* Bugfix: Fixed an issue where images that were hyperlinked and aligned to the left or the right would not properly display in Apple News.
* Bugfix: Fixed an issue with a bad path for file inclusion that was causing a crash on some systems.
* Bugfix: Fixed an issue where including a Cover block with a caption would cause an error on publish.
* Bugfix: Fixed an issue where editing a synced pattern (formerly known as a reusable block) would cause an error in the editor. Props to @kasparsd for the fix.
* Bugfix: Fixed an issue where posts missing custom excerpts would show the publish date and time as the excerpt instead of an autogenerated excerpt based on post content.

Information on previous releases can be found on the plugin's [GitHub Releases page](https://github.com/alleyinteractive/apple-news/releases).

== Developers ==

Please visit us on [github](https://github.com/alleyinteractive/apple-news) to [submit issues](https://github.com/alleyinteractive/apple-news/issues), [pull requests](https://github.com/alleyinteractive/apple-news/pulls) or [read our wiki page about contributing](https://github.com/alleyinteractive/apple-news/wiki/contributing).
