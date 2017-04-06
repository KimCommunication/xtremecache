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
const SEPARATE_MOBILE_AND_DESKTOP = false;

/**
 * True add one more DB query to each reqest.
	 * Clear cache after changing this value, because
	 * "maintenance" cached pages can still be served if this value
	 * is currently false and shop has been prieviously in maintenance mode,
	 * or opposite.
	 * If maintenance mode is On and this value true, cache will be off.
 */
const CHECK_FOR_MAINTENANCE = false;