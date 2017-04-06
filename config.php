<?php
/**
 * Cache Time-To-Live in seconds
 * Since cache gets cleaned quite often, use a very high value (!removed admin performance override)
 */
const CACHE_TTL = 172800;

/**
 * Cache driver
 */
const DRIVER = 'files';

/**
 * Secret key to allow specific product to be deleted and regenerated from cache
 */
const SECRET_KEY = 'change-me';

/**
 * Cache folder for files and sql caches
 */
const CACHE_DIR = 'xcache';
	
/**
 * Cache mobile and desktop versions separatelly?
 */
const SEPARATE_MOBILE_AND_DESKTOP = true;

/**
 * True may add one more DB query to each reqest, based on your PrestaShop cache strategic.
 * If value is false, we will serve cached pages during maintenance.
 * If value is true, cache will be completly off during maintenance.
 */
const CHECK_FOR_MAINTENANCE = false;
