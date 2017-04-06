<?php
/**
 * Clean & regenerate specific product page by product ID
 * @author Pavol Durko
 * @license MIT
 */

header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', false);
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Pragma: no-cache'); // HTTP/1.0
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

include dirname(__FILE__).'/../../config/config.inc.php';
require __DIR__ . DS . 'vendor' . DS . 'phpfastcache.php';
require __DIR__ . DS . '/config.php';

$product_id = (int)Tools::getValue('pid');

if (!isset($_GET[SECRET_KEY]) || $product_id < 1)
    die();

$manage = new ManageCache();

$deleted_from_cache = $manage->delete_from_cache_by_product_id($product_id) ? true : false;

if (isset($_GET['regenerate']) && $deleted_from_cache)
{
    if($manage->regenerate_last())
        echo 'deleted and regenerated';
    else
        echo 'deleted, but cant regenerate';
}
else
    if ($deleted_from_cache)
        echo 'deleted';
    else
        echo 'cant delete';






class ManageCache
{
    private $last_deleted_url;
    
    private $product;
    private $category;
    private $link;
    private $fast_cache;
    private $base_url;
    private $context;
    
    public function __construct()
    {
        $this->fast_cache = $this->getFastCache();
        $this->last_deleted_url = null;
        $this->link = new Link();
        $this->home_url = substr(Tools::getHttpHost(true).__PS_BASE_URI__,0, -1);
        $this->context = Context::getContext();
    }
    
    public function delete_from_cache_by_product_id($product_id)
    {    
        $this->product = new Product($product_id);
        $this->category = new Category((int)$this->product->id_category_default, (int)$this->context->language->id);
        
        $url = $this->get_relative_url(
                $this->link->getProductLink(
                    $this->product, null, $this->category->link_rewrite
                    )
            );

        if (strlen($url) > 0)
        {
            $key = $this->getCacheKey($url);
            if ($this->fast_cache->delete($key))
            {
                $this->last_deleted_url = $url;
                return true;
            }
            else
            {
                $this->last_deleted_url = null;
                return false;
            }
        }
        return false;
    }
    
    public function regenerate_last()
    {
        if (strlen($this->last_deleted_url) > 0)
        {
            $url = $this->home_url . $this->last_deleted_url;
            return (Tools::file_get_contents($url) === false) ? false : true;
        }
        return false;
    }

    private function get_relative_url($absolute_url)
    {
        return str_replace($this->home_url, '', $absolute_url);
    }

    /**
     * Get cache engine
     * @return BasePhpFastCache 
     */
    private function getFastCache()
    {
        phpFastCache::setup('path', __DIR__ . DS . CACHE_DIR);
        return phpFastCache(DRIVER);
    }

    /**
     * Map url to cache key
     * @return string 
     */
    private function getCacheKey($url)
    {
        if (SEPARATE_MOBILE_AND_DESKTOP)
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
