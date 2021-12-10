# Thumbnail Processor for Shopware 6

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md) [![Shopware Store](https://img.shields.io/badge/shopware-store-blue.svg?style=flat-square)](https://store.shopware.com/en/frosh69611263569f/thumbnailprocessor-plugin.html)

This plugins allows you to use variable thumbnails, if your filesystem (or storage-adapter) supports it.  
Additionally it has built-in Lazyloading with auto generated sizes.  
You can use this, if you don't want thumbnails to be created on you development-system, too.  
It will add parameters to original image paths.  
So you are able to save storage and add new thumbnails on the fly.

| Version 	            | Requirements      |
|----------------------|-------------------|
| 1.0.0 - 1.0.5     	  | Min. Shopware 6.0 |
| 1.0.6 - 1.0.15     	 | Min. Shopware 6.3 |
| 1.0.16 - *     	     | Min. Shopware 6.4 |

## Install

Download the plugin from the release page and enable it in shopware.

### From source

Run `npm install` in `src/Resources/app/storefront` within the plugin directory

## Usage
While active, this will access all thumbnails variable from original image. The thumbnail-files won't be needed anymore.

````
f.e.:
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
You may want to delete folder `thumbnails` within folder `public`

## Adding more thumbnail sizes:
- Save new size in the folder of the media management
- then run the command `bin/console media:generate-thumbnails` on the console to update the thumbnails for all images in the database
- Clear shop cache

## Find Patterns

You can find patterns in [Github Discussions in category Patterns](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/discussions/categories/patterns)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
