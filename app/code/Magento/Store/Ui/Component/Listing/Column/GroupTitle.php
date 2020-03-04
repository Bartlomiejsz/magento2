<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Ui\Component\Listing\Column;

use Magento\Catalog\Model\Config\Source\Category;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class GroupTitle extends Column
{
    /**
     * @var Category
     */
    private $category;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var string[]
     */
    private $rootCategories = [];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Category $category
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Category $category,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->category = $category;
        $this->escaper = $escaper;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $this->prepareTitle($item);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @inheritDoc
     */
    private function prepareTitle(array $item)
    {
        $fieldName = $this->getData('name');
        $url = $this->context->getUrl(
            'adminhtml/system_store/editGroup',
            ['group_id' => $item['group_id']]
        );
        $isDefault = (int)$item['default_group_id'] === (int)$item['group_id'];

        $html = '<a title="' . $this->escaper->escapeHtmlAttr(__('Edit Store')) . '" href="' . $url . '">';

        if ($isDefault) {
            $html .= '<strong>';
        }

        $html .= $item[$fieldName];

        if ($isDefault) {
            $html .= '</strong>';
        }

        $html .= '</a><br />(' . $this->escaper->escapeHtml(__('Root Category')) . ': '
            . $this->getRootCategory((int)$item['root_category_id']) . ')';

        return $html;
    }

    /**
     * @param int $rootCategoryId
     *
     * @return string
     */
    private function getRootCategory(int $rootCategoryId): string
    {
        if (!$this->rootCategories) {
            $categoryOptions = $this->category->toOptionArray(false);

            foreach ($categoryOptions as $categoryOption) {
                $this->rootCategories[(int)$categoryOption['value']] = $categoryOption['label'];
            }
        }

        return $this->rootCategories[$rootCategoryId] ?? '';
    }

}
