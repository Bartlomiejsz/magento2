<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Constraint\AssertProductInGrid;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert products data from csv import file and page are match.
 */
class AssertImportProduct extends AbstractConstraint
{
    /**
     * Product type.
     *
     * @var string
     */
    protected $productType = 'simple';

    /**
     * Needed product data.
     *
     * @var array
     */
    protected $neededKeys = [
        'sku',
        'name',
        'price',
        'qty',
        'url_key',
    ];

    /**
     * Import fixture.
     *
     * @var ImportData
     */
    private $import;

    /**
     * Edit page on backend
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Curl transport on webapi.
     *
     * @var WebapiDecorator
     */
    private $webApi;

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Assert imported products are correct.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductView $catalogProductView
     * @param AssertProductInGrid $assertProductInGrid
     * @param CatalogProductEdit $catalogProductEdit
     * @param WebapiDecorator $webApi
     * @param ImportData $import
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductView $catalogProductView,
        AssertProductInGrid $assertProductInGrid,
        CatalogProductEdit $catalogProductEdit,
        WebapiDecorator $webApi,
        ImportData $import
    ) {
        $this->import = $import;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->webApi = $webApi;
        $this->browser = $browser;

        $products = $this->import->getDataFieldConfig('import_file')['source']->getEntities();
        foreach ($products as $product) {
            if ($product->getDataConfig()['type_id'] === $this->productType) {
                // assert product in data grid
                $assertProductInGrid->processAssert($product, $catalogProductIndex);

                // assert product in store front
                $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
                \PHPUnit_Framework_Assert::assertEquals(
                    $catalogProductView->getViewBlock()->getProductName(),
                    $product->getName(),
                    "Can't find product in store front"
                );

                // assert product data from page and csv.
                $productsData = $this->getPrepareProductsData($product);
                $resultProductsData = $this->getResultProductsData($productsData);
                $resultCsvData = $this->getResultCsv($product->getSku());
                \PHPUnit_Framework_Assert::assertEquals(
                    $resultProductsData,
                    $resultCsvData,
                    'Products from page and csv are not match.'
                );
            }
        }
    }

    /**
     * Prepare configurable product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getPrepareProductsData(FixtureInterface $product)
    {
        $productId = $this->retrieveProductBySku($product)['id'];
        $this->catalogProductEdit->open(['id' => $productId]);
        $productData = $this->catalogProductEdit->getProductForm()->getData($product);

        return $productData;
    }

    /**
     * Prepare array from csv file.
     *
     * @param string $productSku
     * @return array
     */
    private function getResultCsv($productSku)
    {
        $csvData = $this->import->getDataFieldConfig('import_file')['source']->getCsv();

        $csvKeys = array_shift($csvData);
        foreach ($csvData as $data) {
            $data = array_combine($csvKeys, $data);
            if ($data['sku'] === $productSku) {
                return $this->deleteUnusedData($data);
            }
        }
        return [];
    }

    /**
     * Return prepared products data.
     *
     * @param array $productsData
     * @return array
     */
    private function getResultProductsData(array $productsData)
    {
        $resultProductsData = [];
        array_walk_recursive(
            $productsData,
            function ($value, $key) use (&$resultProductsData) {
                if (array_search($key, $this->neededKeys)) {
                    $resultProductsData[$key] = $value;
                }
            }
        );
        return $resultProductsData;
    }

    /**
     * Delete waste data from array.
     *
     * @param array $csvData
     * @return array
     */
    private function deleteUnusedData(array $csvData)
    {
        $data = [];
        foreach ($csvData as $key => $value) {
            if (array_search($key, $this->neededKeys)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Retrieve product by sku.
     *
     * @param FixtureInterface $product
     * @return mixed
     */
    public function retrieveProductBySku(FixtureInterface $product)
    {
        $url = $_ENV['app_frontend_url'] . 'rest/all/V1/products/' . $product->getSku();
        $this->webApi->write($url, [], WebapiDecorator::GET);
        $response = json_decode($this->webApi->read(), true);
        $this->webApi->close();
        return $response;
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Imported products are presents in Admin Data Grid and StoreFront';
    }
}
