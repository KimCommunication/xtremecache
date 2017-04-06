<?php
/**
 * Serve cached pages with no request processing
 * @author Salerno Simone
 * @version 1.0.6
 * @license MIT
 */

require __DIR__.DS.'vendor'.DS.'phpfastcache.php';


class XtremeCache extends Module {
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
	
    /**
     * Cache engine
     * @var BasePhpFastCache
     */
    private $fast_cache;
    
    
    public function __construct() {
        $this->name = 'xtremecache';
        $this->tab = 'frontend_features';
        $this->version = '1.0.6';
        $this->author = 'Simone Salerno';

        parent::__construct();

        $this->displayName = $this->l('Xtreme cache');
        $this->description = $this->l('Cache non-dynamic pages in the front office.');
        $this->fast_cache = $this->getFastCache();
    }
    
    /**
     * Handle non-explicitly handled hooks
     * @param string $name hook name
     * @param array $arguments
     */
    public function __call($name, $arguments) {        
        if (0 === strpos(strtolower($name), 'hookaction')) {
            $this->fast_cache->clean();
        }
    }

    /**
     * Install and register hooks
     * @return bool
     */
    public function install() {        
        return parent::install() && 
                $this->registerHook('actionDispatcher') &&
                $this->registerHook('actionRequestComplete') &&
                $this->registerHook('actionCategoryAdd') &&
                $this->registerHook('actionCategoryUpdate') &&
                $this->registerHook('actionCategoryDelete') &&
                $this->registerHook('actionProductAdd') &&
                $this->registerHook('actionProductUpdate') &&
                $this->registerHook('actionProductDelete') &&
                $this->registerHook('actionProductSave') &&
                $this->registerHook('actionEmptySmartyCache');
    }
    
    /**
     * Uninstall and clear cache
     * @return bool
     */
    public function uninstall() {
        //delete all cached files
        $this->fast_cache->clean();
        
        return $this->unregisterHook('actionDispatcher') &&
                $this->unregisterHook('actionRequestComplete') &&
                parent::uninstall();
    }
    
    /**
     * Check if page exists in cache
     * If it exists, serve and abort
     * @param array $params
     */
    public function hookActionDispatcher($params) {
        if (!$this->isActive($params))
            return;
        
        //if not in the checkout process
        if ($params['controller_class'] !== 'OrderController' && 
            $params['controller_class'] !== 'OrderOpcController')
		{
            $cached = $this->fast_cache->get($this->getCacheKey());
            
            if (NULL !== $cached) {
               //empty output buffer
               ob_get_clean();
               die($cached);
           }
        }
    }
    
    /**
     * Cache page content for front pages
     * @param string $params
     */
    public function hookActionRequestComplete($params) {
        if (!$this->isActive($params))
            return;
		
        $controller = $params['controller'];
        
        if (is_subclass_of($controller, 'FrontController') &&
            !is_subclass_of($controller, 'OrderController') &&
            !is_subclass_of($controller, 'OrderOpcController')) {
			
            $key = $this->getCacheKey();
            //mark page as cached
            $debugInfo = sprintf(
            	"<!-- %s from %s on %s] -->
",
            	$key,
            	static::DRIVER,
            	date('Y-m-d H:i:s'));
			
            $output = $debugInfo . $params['output'];
            $this->fast_cache->set($key, $output, static::CACHE_TTL);		
        }
    }
	
	public function hookActionEmptySmartyCache($params) {
		$this->fast_cache->clean();
	}

    /**
     * Check if should use cache
	 * checks for: dev mode, profilling, front controller, maintenance mode?, customer, shopping cart, AJAX and POST requests
     * @return boolean
     */
    private function isActive($params)
	{
        //turn off on debug mode
        if (_PS_MODE_DEV_ || _PS_DEBUG_PROFILING_)
            return false;
        
		//turn off if it is not front office
		if(!("front" === $this->context->controller->controller_type))
			return false;
		
		//disable on ajax and non-GET requests
        //$active = !Tools::getValue('ajax', false);
        $active = !(isset($this->ajax) ? $this->ajax : false);
        $active = $active && $_SERVER['REQUEST_METHOD'] === 'GET';
		if (!$active)
			return false;
		
		// are we going to check if maintenance mode is on?
		// if enabled, during maintenance mode there will be no cache
		if (static::CHECK_FOR_MAINTENANCE && !((bool)Configuration::get('PS_SHOP_ENABLE', true)))
			return false;
		
        //check that customer is not logged in
        $customer = $this->context->customer;
        if ($customer && $customer instanceof Customer && $customer->id > 0)
            return false;
        
        //for guest checkout, check that cart is empty
		//global $cookie;
		//$cart = new Cart($cookie->id_cart);
		if (isset($this->context->cart))
		{
			$cart = $this->context->cart;
			if ($cart && $cart instanceof Cart && $cart->nbProducts() > 0)
				return false;
        }
		
        return true;
    }
    
    /**
     * Get cache engine
     * @return BasePhpFastCache 
     */
    private function getFastCache() {
        phpFastCache::setup('path', __DIR__.DS.'xcache');
        return phpFastCache(static::DRIVER);
    }
    
    /**
     * Map url to cache key
     * @return string 
     */
    private function getCacheKey($url=NULL)
	{
        if ($url === NULL)
            $url = $_SERVER['REQUEST_URI'];
        
		if (static::SEPARATE_MOBILE_AND_DESKTOP)
			$url = 'device-'.$this->context->getDevice().
					'-lang-'.$this->context->language->id.
					'-shop-'.$this->context->shop->id.'-'.
					$url;
		else
			$url = 'lang-'.$this->context->language->id.
					'-shop-'.$this->context->shop->id.'-'.
					$url;
        
        $url = md5($url);
        return $url;
    }
}
