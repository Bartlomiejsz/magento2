<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class StoreTitle extends Column
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            'adminhtml/system_store/editStore',
            ['store_id' => $item['store_id']]
        );
        $isDefault = (int)$item['default_store_id'] === (int)$item['store_id'];
        $textStyle = '';
        if (!(bool)$item['store_active']) {
            $textStyle = ' style="text-decoration: line-through;"';
        }

        $html = '<span' . $textStyle . '>';
        $html .= '<a title="' . $this->escaper->escapeHtmlAttr(__('Edit Store View')) . '" href="' . $url . '">';

        if ($isDefault) {
            $html .= '<strong>';
        }

        $html .= $item[$fieldName];

        if ($isDefault) {
            $html .= '</strong>';
        }

        $html .= '</a><br />(' . $this->escaper->escapeHtml(__('Code')) . ': ' . $item['store_code'] . ')';
        $html .= '</span>';

        return $html;
    }
}
