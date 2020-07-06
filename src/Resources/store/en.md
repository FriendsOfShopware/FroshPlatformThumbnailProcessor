With this plugin you can access dynamic thumbnails.  
Every product, every picture in the shopping worlds legitimately has thumbnails. These become standard
generated and saved automatically during upload.  
At this point this plugin intervenes and provides the function that no more thumbnails are created have to be created.  
The thumbnails are then generated and delivered by an external service in real time when visiting.

## Advantages of saving thumbnail generation:
- Save space on disk
- Faster upload of images
- Relief of the server
- Faster backups with fewer files

## Which service do I now use for the thumbnails:
We have already had experience with the following services.
- [BunnyCDN](https://bunnycdn.com) (paid)  
  Template example: {mediaUrl}/{mediaPath}?width={width}&height={height}
- [keycdn](https://www.keycdn.com/support/image-processing) (paid)  
  Template example: {mediaUrl}/{mediaPath}?width={width}&height={height}
- [imgproxy](https://imgproxy.net/) (free, self hosted)  
  Template example: http://localhost:8080/insecure/fit/{width}/{height}/sm/0/plain/{mediaUrl}/{mediaPath}
- [images.weserv.nl](https://images.weserv.nl/) (free)  
  Template example: https://images.weserv.nl/?url={mediaUrl}/{mediaPath}&w={width}&h={height}
- [cloudimage](https://www.cloudimage.io/en/home) (free plan available)  
  Template example: https://token.cloudimg.io/v7/{mediaUrl}/{mediaPath}&w={width}&h={height}

Any service provider that changes image sizes using the url parameter should also be compatible.

This plugin is part of [@FriendsOfShopware](https://store.shopware.com/en/friends-of-shopware.html).  
Maintainer from the plugin is: [Sebastian König (tinect)](https://github.com/tinect)

For questions or bugs please create a [Github Issue](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/issues/new)
