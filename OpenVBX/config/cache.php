<?php

/**
 * Enable/disable the cache system
 * By default we are enabled and let the system choose the correct type
 */
$config['cache']['cache_enabled'] = true;

$config['cache']['default_expires'] = 3600;

/**
 * Currently supported caches:
 * - autodetect: @todo autodetect the correct system to use
 * - memory: use memory for a per-page load cache
 * - apc: @todo use APC for the memory cache
 * - memcached: @todo use memcached for the memory cache
 * 
 * @todo auto-detect cache availability
 */
$config['cache']['cache_type'] = 'auto-detect';