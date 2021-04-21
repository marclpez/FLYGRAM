=== Images with GPS on GoogleMaps ===
Contributors: severinroth
Donate link:
Tags: GPS,GPX,Map,Image,geotag,picture,geo,Photos, GoogleMaps, Placemarks, MapImages, EXIF, location, latitude, longitude, altitude, map,
Requires at least: 4.9
Tested up to: 5.6
Stable tag: 0.911
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Images with GPS on Google Maps displays your photos on a Google Maps map using GPS or without GPS Geotags.

== Description ==
Images with GPS on Google Maps displays your photos on a Google Maps map using GPS or without GPS Geotags. Add your own Geo-Tags to your pictures.

To show more about 'Images with GPS on GoogleMaps' visit [this page](http://www.travel-logbuch.com/vila-do-bispo-sagres-portugal/) at [travel-logbuch.com](http://www.travel-logbuch.com)

= Feedback =
* I am open for your suggestions and feedback - [Just send it in](https://www.travel-logbuch.com/kontakt/)


== Installation ==
1. Deactivate plugin if you have the previous version installed.
2. Extract "geoImageAndGPX.zip" archive content to the "/wp-content/plugins/geoImageAndGPX" directory.
3. Activate "Images with GPS on GoogleMaps" plugin via 'Plugins' menu in WordPress admin menu.
4. Go to the "Settings"-"Image GPS & GPX in Map" menu item and fill in the Google API Key. Become a Key on: [this page] https://developers.google.com/maps/documentation/javascript/get-api-key? at [https://developers.google.com/maps/documentation/javascript/get-api-key](https://developers.google.com/maps/documentation/javascript/get-api-key)
5. Load your Images to a page or post
6. Include the Shortcode [geoImg] in your content.

== Frequently Asked Questions ==

= Where can I find the ExifTool path? =
The Exiftool must be installed by your administrator on the server.
= Runs the plugin only with the Exiftool? =
No, first try using the PHP command exif_read_data to extract the geotags. Only if this does not work must the Exiftool be installed in order to extract GeoTags.
= I can also use the plugin if there are no geotags or they can not be extracted? =
Yes, but in the backend the marker has to be dragged and dropped at the desired position on the map.

== Localization ==
* English (default) - always included
* German - always included
* .pot file (`gm.pot`) for translators is also always included :)
* *Your translation? - [Just send it in](https://www.travel-logbuch.com/kontakt/)*

== Screenshots ==
1. screenshot-0.jpg Frontend GoogleMaps
2. screenshot-00.jpg Click on marker to show the big image
3. screenshot-1.jpg Add Images to the page or post
4. screenshot-2.jpg Image overview
5. screenshot-3.jpg Drag and drop the marker to the right position on GoogleMaps
6. screenshot-4.jpg Add ShortCode
7. screenshot-5.jpg Add Google API Key

== Translations ==
* English
* German: Deutsch - Standard

* Note:* All my plugins are localized/ translateable by default. This is very important for all users worldwide. So please contribute your language to the plugin to make it even more useful. For translating I recommend the awesome ["Codestyling Localization" plugin](http://wordpress.org/extend/plugins/codestyling-localization/) and for validating the ["Poedit Editor"](http://www.poedit.net/).

== Changelog ==
= [0.911] 06.12.2020 =
* add Gutenberg block

= [0.91] 06.12.2020 =
* bugfix
* Choose get images from post/page or select by yourself from media
* When storing the geodata manually, the last starting point is always stored as a new point
* WP 5.6 ready

= [0.901] 14.02.2020 =
* css bugfix

= [0.900] 08.02.2020 =
* Gutenberg ready
* Select pictures from other site/page
* WordPress 5.3.3 ready
* Backend add refresh Button
* Backend add Media Library open Button
* some Gutenberg bugfix (gridItems[0], grid3d.js)

= [0.892] 28.01.2020 =
* WordPress 5.3.3 ready

= [0.891] 22.02.2019 =
* WordPress 5.1 ready
* Some small bugfixes

= [0.89] 14.11.2018 =
* some bugfixes and added languages de-CH & de-AT

= [0.88] 08.11.2018 =
* some bugfixes and added languages de-DE & en-US
* add banner and icon

= [0.80] 14.01.2017 =
* Alpha Version, it runs on two Pages stable




== Upgrade Notice ==

== Additional Documentation ==
Add image to page/site

To insert images of other pages or sites into the current Geo Image:
1. open a contribution in the backend
2. scroll to the "Images with GPS Data on Maps" section
3. click the Add Files button
4. select an image or add an image
5. click button "Insert into page
6. click the "Refresh" button in the "Images with GPS Data on Maps" section
7. possibly add manual GPS data to the image if none are available (visible with the red ? behind GPS data edit)
8. having fun if it worked.
