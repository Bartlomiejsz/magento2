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

class WebsiteName extends Column
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

            $websiteIndex = 0;
            $websiteIndexArr = [];
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($websiteIndexArr[$item['website_id']])) {
                    $websiteIndexArr[$item['website_id']] = $websiteIndex++;
                }

                $item['website_index'] = $websiteIndexArr[$item['website_id']];

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
            'adminhtml/system_store/editWebsite',
            ['website_id' => $item['website_id']]
        );
        $isDefault = (bool)$item['is_default'];

        $html = '<a title="' . $this->escaper->escapeHtmlAttr(__('Edit Website')) . '" href="' . $url . '">';

        if ($isDefault) {
            $html .= '<strong>';
        }

        $html .= $item[$fieldName];

        if ($isDefault) {
            $html .= '</strong>';
        }

        $html .= '</a><br />(' . $this->escaper->escapeHtml(__('Code')) . ': ' . $item['code'] . ')';

        return $html;
    }
}
