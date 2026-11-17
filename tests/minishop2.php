<?php
declare(strict_types=1);
// Uses the installed model's file layout and a fake database; no site data is read.
$core = $argv[1] ?? '';
if (!is_file($core . '/components/minishop2/model/minishop2/msproductdata.class.php')) { exit("Usage: php tests/minishop2.php /path/to/modx/core-with-minishop2\n"); }
define('MODX_CORE_PATH', rtrim($core, '/\\') . '/');
require __DIR__ . '/documents.php';
class ShopTestModx extends modX
{
    public $loaded = false;
    public function getOption($key, $options = null, $default = null) { return $default; }
    public function addPackage($name, $path) { $this->loaded = $name === 'minishop2' && is_dir($path); return $this->loaded; }
    public function getObject($class, $criteria) {
        if ($class === 'msProductData') {
            if (!$this->loaded || $criteria !== 2) { throw new RuntimeException('Wrong miniShop2 model or product ID'); }
            return new modResource(['price' => '125.50', 'article' => 'SKU-2']);
        }
        return parent::getObject($class, $criteria);
    }
}
$shop = new ShopTestModx();
$shop->rows[2] = new modResource(['id' => 2, 'context_key' => 'web', 'published' => 1, 'deleted' => 0, 'searchable' => 1, 'class_key' => 'msProduct', 'pagetitle' => 'Product', 'description' => '', 'content' => '<p>Product</p>']);
$documents = new LLMSForge\Documents($shop);
$text = $documents->markdown(['mode' => 'resource', 'resource_id' => 2], 'web');
if (strpos($text, '- price: 125.50') === false || strpos($text, '- article: SKU-2') === false) { throw new RuntimeException('Missing product fields'); }
$shop->rows[2]->data['published'] = 0;
if ($documents->markdown(['mode' => 'resource', 'resource_id' => 2], 'web') !== null) { throw new RuntimeException('Hidden product exported'); }
echo "miniShop2 model loading, fields and visibility checks passed\n";
