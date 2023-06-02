# 3.0.1
* Add configuration to set maximum size for original image
* Add configuration to specify file extension which can be processed
* Remove configuration to toggle processing the original image

# 3.0.0
* Shopware 6.5 compatibility
* Add support to define ThumbnailPattern per SalesChannel

# 2.0.0

* ATTENTION: Removed lazysizes from this plugin! Please install dedicated plugin: FroshLazySizes

# 1.1.2

* catch error thrown when image has been specified wrong

# 1.1.1

* set values of width and height as ratio without calculation

# 1.1.0

* Set aspect-ratio with inline-css to reserve space of images which reduces Cumulative Layout Shift (CLS)
* Set class `frosh-proc` onto images managed by this plugin, use selector `img.frosh-proc` to set specific css style
* Add usage of variable `src`. Use this to determine an own placeholder image or preload image.

# 1.0.28

* Restrict configuration to "All Saleschannels"

# 1.0.27

* Fix problems from Shopware 6.4.10

# 1.0.26

* Implement own URL encoding

# 1.0.25

* Sort thumbnail sizes in code

# 1.0.24

* Set width of 100% to image elements width set alignment

# 1.0.23

* Remove url encoding for image paths

# 1.0.22

* Optimize url encoding for image paths to support imgproxy v3

# 1.0.21

* Fixes a problem with the retrieval of the micro data of the product images

# 1.0.20

* Output alt and title attributes for thumbnails if they were saved with the image in media
* Remove the use of height from the thumbnail sizes, as the automatic calculation of the necessary size does not make any direct reference to it
    * Please remove the height from your template in the plugin settings
* Increase the calculated ratio to three digits

# 1.0.19

* Remove automatic room reservation due to compatibility problems. Usable now per variable fullWidth per sw_thumbnail.

# 1.0.18

* Fix error with images without dimensions

# 1.0.17

* Cleanup code
* Define blocks: thumbnail_utility and thumbnail_utility_img
* Reserve room in fullwidth cms elements to optimize CLS
* Fix thumbnail generation to not create files

# 1.0.16

* FEATURE Better support for SW6.4

# 1.0.15

* FEATURE Add support for SW6.4

# 1.0.14

* FEATURE Verwende automatische Erzeugung der Sizes für die Thumbnails
* FEATURE SEO Gebe auch leere attribute aus

# 1.0.13

* FEATURE Added Option to set max width of container

# 1.0.12

* BUGFIX Fix error when replacing media

# 1.0.11

* FEATURE Respect not given attributes in thumbnails

# 1.0.10

* FEATURE Thumbnails will be lazy loaded

# 1.0.9

* BUGFIX Fix thumbnail-variable, which occurs, when no thumbnail exists
 
# 1.0.8

* FEATURE Added Option to fix ThumbnailSizes for the gallery on product page
 
# 1.0.7

* FEATURE Thumbnails that are larger than the original image are no longer displayed, requested and delivered
 
# 1.0.6

* Fix mediaUrl in 6.3.0.0

# 1.0.5

* Compatibility to 6.3.0.0

# 1.0.4

* Option to improve thumbnail display in listings

# 1.0.3

* Fix wrong uploading of files

# 1.0.2

* Option to process original images
* Option to process SVGs, disabled by default
* Optimizing plugin config

# 1.0.1

* First release in store

# 1.0.0

* First release
