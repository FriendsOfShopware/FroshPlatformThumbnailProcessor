# Thumbnail Processor for Shopware 6

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md) [![Shopware Store](https://img.shields.io/badge/shopware-store-blue.svg?style=flat-square)](https://store.shopware.com/en/frosh69611263569f/thumbnailprocessor-plugin.html)

This plugins allows you to use variable thumbnails, if your filesystem (or storage-adapter) supports it.  
Additionally it has built-in Lazyloading with auto generated sizes.  
You can use this, if you don't want thumbnails to be created on you development-system, too.  
It will add parameters to original image paths.  
So you are able to save storage and add new thumbnails on the fly.

| Version 	| Requirements               	
|---------	|----------------------------
| 1.0.0 - 1.0.5     	| Min. Shopware 6.0
| 1.0.6 - 1.0.15     	| Min. Shopware 6.3
| 1.0.16 - *     	| Min. Shopware 6.4

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
https://www.example.com/media/01/82/69/sasse.png?width=200&height=200
````
You can edit the thumbnail-template within the plugin-config. Defaults `{mediaUrl}/{mediaPath}?width={width}&height={height}`.
Available variables with examples:
* {mediaUrl}: https://www.example.com/
* {mediaPath}: media/01/82/69/sasse.png
* {width}: 800
* {height}: 800

 Feel free to decorate `ThumbnailUrlTemplateInterface` to add more individual functions like [signed imgproxy](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessorImgProxy)

## Removing unneeded thumbnails
You may want to delete folder `thumbnails` within folder `public`

## Adding more thumbnail sizes:
- Save new size in the folder of the media management
- then run the command `bin/console media:generate-thumbnails` on the console to update the thumbnails for all images in the database
- Clear shop cache

## Tested Supports

### imgproxy [Link](https://imgproxy.net/)

Tested with insecure environment for internal test-shops.  
Template example: `http://localhost:8080/x/fit/{width}/{height}/sm/0/plain/{mediaUrl}/{mediaPath}`  
will become `http://localhost:8080/x/fit/800/800/sm/0/plain/https://www.example.com/media/01/82/69/sasse.png`

### imgix [Link](https://imgix.com)

`Opinion: has free plan, fast & including webp, a lot of resizing & filtering options`  
You will have to create a Web Folder source and point it to your domain.  
Template example: `https://<your-subdomain>.imgix.net/{mediaPath}?w={width}&h={height}&fit=clip&fill=solid&fill-color=ffffff&lossless=1&auto=compress`  
will become `https://<your-subdomain>.imgix.net/{mediaPath}?w=800&h=800&fit=clip&fill=solid&fill-color=ffffff&lossless=1&auto=compress`  

### BunnyCDN [Link](https://bunnycdn.com/)

`Opinion: not cheap with 9,5$/m per zone, but fast and including webp`  
You would have to active `Bunny Optimizer` and `Manipulation Engine` in your Zone in BunnyCDN.  
Template example: `{mediaUrl}/{mediaPath}?width={width}&height={height}` (default)  
will become `https://www.example.com/media/01/82/69/sasse.png?width=800&height=800`

### Images.weserv.nl [Link](https://images.weserv.nl/)

`Opinion: free, but slow and without webp`  
An image cache & resize service. Manipulate images on-the-fly with a worldwide cache.  
Template example: `https://images.weserv.nl/?url={mediaUrl}/{mediaPath}&w={width}&h={height}`  
will become `https://images.weserv.nl/?url=https://www.example.com/media/01/82/69/sasse.png&w=800&h=800`

### cloudimage [Link](https://www.cloudimage.io/en/home)

`Opinion: has free plan, fast and including webp`  
An image cache & resize service. Manipulate images on-the-fly with a worldwide cache.  
Template example: `https://token.cloudimg.io/v7/{mediaUrl}/{mediaPath}&w={width}&h={height}`  
will become `https://token.cloudimg.io/v7/https://www.example.com/media/01/82/69/sasse.png&w=800&h=800`

### Cloudflare Image Resizing [Link](https://developers.cloudflare.com/images/)

`Opinion: many options but only for business and enterprise customers.`  
Has a lossless image optimization and big hosters have their NFS connected in their contracts directly to cloudflare
Template example: `{mediaUrl}/cdn-cgi/image/width={width},height={height},quality=85,format=auto/{mediaPath}`  
will become `https://yourshop.com/cdn-cgi/image/width%3D3000%2Cheight%3D3000%2Cquality%3D85%2Cformat%3Dauto/media/db/f4/18/1624437000/334c0cd2b7dd465c9a35a806e8052643.jpg`


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
