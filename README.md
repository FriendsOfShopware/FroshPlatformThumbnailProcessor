# Thumbnail Processor for Shopware 6

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md) [![Shopware Store](https://img.shields.io/badge/shopware-store-blue.svg?style=flat-square)](https://store.shopware.com/en/frosh69611263569f/thumbnailprocessor-plugin.html)

This plugin allows you to use variable thumbnails, if your filesystem (or storage-adapter) supports it.
So you [don't need modern file formats](https://blog.tinect.de/posts/you-might-not-need-thumbnails-or-modern-image-format/).  
Besides the benefits for using it in live shops, you can use this also in development-systems, if you don't want thumbnails to be created.  
It will add parameters to original image paths.  
So you are able to save storage and add new thumbnails on the fly.

| Plugin version 	     | Shopware version  | Branch            |
|----------------------|-------------------|-------------------|
| 5.*                  | Min. 6.6          | [main](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor)                 |
| 4.* - 3.0.0          | Min. 6.5          | [v4](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/tree/v4)                 |
| 2.* - 1.0.16       	 | Min. 6.4          | [v2](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/tree/v2)                 |
| 1.0.15 - 1.0.6     	 | Min. 6.3          |                   |
| 1.0.5 - 1.0.0     	  | Min. 6.0          |                   |


## Install

Download the plugin from the release page and enable it in Shopware.

### By composer

`composer require frosh/platform-thumbnail-processor`

### From source

Run `npm install` in `src/Resources/app/storefront` within the plugin directory

### By zip

download latest release and upload into admin:
https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/releases/latest/download/FroshPlatformThumbnailProcessor.zip

## Usage
While active, this will access all thumbnails variable from original image. The thumbnail-files won't be needed anymore.

````
e.g.:
https://www.example.com/thumbnail/01/82/69/sasse_200x200.png
 becomes:
https://www.example.com/media/01/82/69/sasse.png?width=200
````
You can edit the thumbnail-template within the plugin-config. Defaults `{mediaUrl}/{mediaPath}?width={width}`.
Available variables with examples:
* {mediaUrl}: https://www.example.com/
* {mediaPath}: media/01/82/69/sasse.png
* {width}: 800

Feel free to decorate `ThumbnailUrlTemplateInterface` to add more individual functions like [signed imgproxy](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessorImgProxy)

## Removing unneeded thumbnails
You may want to delete folder `thumbnails` within folder `public`.
If needed, you could create redirects on your web server for old paths.
Example for Apache .htaccess: `RewriteRule ^thumbnail/(.*)_\d+x\d+.(.*)$ https://cdn.myshop.com/media/$1.$2 [L,R=301]`, consult their docs for more details.

## Adding more thumbnail sizes:
- Save new size in the folder of the media management
- (no more needed from version 3.0.2) run the command `bin/console media:generate-thumbnails` on the console to update the thumbnails for all images in the database
- Clear shop cache

## Find Patterns

You can find patterns in [GitHub Discussions in category Patterns](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/discussions/categories/patterns)

## Uninstall

After uninstalling plugin you have to run `bin/console media:generate-thumbnails -strict` to generate the thumbnails-files on disk.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
