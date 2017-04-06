This is modified and little tweaked version for PrestaShop 1.7.


To change behavior edit constants in xtremecache.php:
   
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

It is not tested on PrestaShop 1.6.


SimoneS93 wrote:
#Prestashop Xtreme cache module

Today I was thinking about Prestashop front office performance optimization and the lack of a full cache system came to mind (by full cache, I mean save the page html to file and serve that on subsequent requests, with no processing at all). 
In the first place I thought it was not possible, since Prestashop is higly dynamic and needs to update whenever a user interacts with the carts or the account.
But the I realized not all people visit our site logged in and for those the content
is almost static (at least in the short term, if we’re not updating our catalogue).
So the full cache system idea (with an expiration time near in the future) gained sense to me and I implemented a module just to do that.
It works hooking into *actionDispatcher* to process the incoming request as soon as possibile, before any database query or controller’s processing: if the user is not logged in and it finds a cached version of the requested page, it serves that page and aborts execution. 
You gain not only a better response time, but a lighter workload on the server, too! A win-win!
But to serve cached pages we need to store one, first. Prestashop doesn’t provide such an hook by default, so I created one in the Controller class, right before echoing the response to the browser.
You’ll find the module on the Prestashop official forum.
