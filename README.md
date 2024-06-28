# Publish to Apple News

[![read me standard badge](https://img.shields.io/badge/readme%20style-standard-brightgreen.svg?style=flat-square)](https://github.com/RichardLitt/standard-readme)

The Publish to Apple News plugin enables your WordPress content to be published to your Apple News channel.

- Convert your WordPress content into Apple News format automatically.
- Create a custom design for your Apple News content with no programming knowledge required.
- Automatically or manually publish posts from WordPress to Apple News.
- Control individual posts with options to publish, update, or delete.
- Publish individual posts or in bulk.
- Handles image galleries and popular embeds like YouTube and Vimeo that are supported by Apple News.

Please visit our [wiki](https://github.com/alleyinteractive/apple-news/wiki) for detailed information on the follow items:

- [Background](#background)
- [Releases](#Releases)
	- [Install](#install)
	- [Use](#use)
	- [Source](#from-source)
	- [Changelog](#changelog)
- [Development Process](#development-process)
	- [Contributing](#contributing)
- [Project Structure](#project-structure)
- [Third-Party Dependencies](#third-party-dependencies)
- [Related Efforts](#related-efforts)
- [Maintainers](#maintainers)
- [License](#license)

## Background

To enable content from your WordPress site to be published to your Apple News channel, you must obtain and enter Apple News API credentials from Apple.

Please see the [Apple Developer](https://developer.apple.com/) and [Apple News Publisher documentation](https://developer.apple.com/news-publisher/) and terms on Apple's website for complete information.

## Releases

### Install
See the wiki for [installation instructions](https://github.com/alleyinteractive/apple-news/wiki/Installation).

###	Use
See the wiki for [usage instructions](https://github.com/alleyinteractive/apple-news/wiki/Usage) as well as [configuration guidance](https://github.com/alleyinteractive/apple-news/wiki/Configuration).

###	Source

###	Changelog
See the release archive for the [changelog](https://github.com/alleyinteractive/apple-news/releases).

## Development Process

###	Contributing
The wiki has [details about contributing](https://github.com/alleyinteractive/apple-news/wiki/Contributing).

## Project Structure

## Third-Party Dependencies

## Related Efforts
- [Connect to Apple Music](https://github.com/alleyinteractive/apple-music)

## Maintainers
- [Alley](https://github.com/alleyinteractive)

![Alley logo](https://avatars.githubusercontent.com/u/1733454?s=200&v=4)

## Releasing the Plugin

The plugin uses a [built release workflow](./.github/workflows/built-release.yml)
to compile and tag releases. Whenever a new version is detected in the root
`composer.json` file or in the plugin's headers, the workflow will automatically
build the plugin and tag it with a new version. The built tag will contain all
the required front-end assets the plugin may require. This works well for
publishing to WordPress.org or for submodule-ing.

When you are ready to release a new version of the plugin, you can run
`npm run release` to start the process of setting up a new release.

### Contributors
Thanks to all of the [contributors](CONTRIBUTORS.md) to this project.

## License
This project is licensed under the
[GNU Public License (GPL) version 3](LICENSE) or later.
