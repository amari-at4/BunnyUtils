# Bunny CDN Management Utils

The purpose of this application is to create small support scripts for the daily administration of the CDN Bunny

All the scripts has been written using symfony console package

## Script for cleaning Permanent Cache in Edge Storage

One great product from Bunny.net it their Edge Storage, you can use as origin of static sites or as permanent cache for your pull zones.

One caveat of using as Pull Zone's permanent cache is every time that the pull zone cache is purged, a new folder it's created inside Edge Storage but the old one folder it's not deleted.  Then you'll pay for storage that you don't use anymore.

The script `BunnyUtilsCleanPermCache.php` deletes all folders inside the folder `/__bcdn_perma_cache__/` _(this is the folder where Bunny CDN stores permanent cache)_ except the last one created.

