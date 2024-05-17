<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_MassProductActions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\MassProductActions\Plugin\Component;

use Magento\Framework\App\RequestInterface;
use Mageplaza\MassProductActions\Helper\Data as HelperData;
use Mageplaza\MassProductActions\Model\Config\Source\System\Actions;

/**
 * Class AbstractMassAction
 * @package Mageplaza\MassProductActions\Plugin\Component
 */
class AbstractMassAction
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Actions
     */
    protected $_massActions;

    /**
     * MassAction constructor.
     *
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param Actions $massAction
     */
    public function __construct(
        RequestInterface $request,
        HelperData $helperData,
        Actions $massAction
    ) {
        $this->_request     = $request;
        $this->_helperData  = $helperData;
        $this->_massActions = $massAction;
    }

    /**
     * @param \Magento\Catalog\Ui\Component\Product\MassAction|\Magento\Ui\Component\MassAction $massAction
     */
    public function addMassActions($massAction)
    {
        $massActions       = $this->_massActions->toOptionArray();
        $additionalActions = [];
        foreach ($massActions as $action) {
            $additionalActions[$action['type']] = [
                'component' => 'uiComponent',
                'type'      => $action['type'],
                'label'     => $action['label']
            ];
        }
        $config = $massAction->getData('config');

        if (isset($config['actions']) && $config['actions']) {
            $this->sortMassActions($config, $additionalActions, $massAction);
        }
    }

    /**
     * @param array $config
     * @param array $additionalActions
     * @param \Magento\Catalog\Ui\Component\Product\MassAction|\Magento\Ui\Component\MassAction $massAction
     */
    public function sortMassActions($config, $additionalActions, $massAction)
    {
        $actionsConfig   = $this->_helperData->getActionsConfig();
        $selectedActions = $actionsConfig['selected_actions'];
        $actionPositions = $actionsConfig['action_positions'];
        foreach (array_keys($selectedActions) as $selectedAction) {
            $config['actions'][] = $additionalActions[$selectedAction];
        }
        uasort($actionPositions, function ($oldArray, $newArray) {
            return $oldArray - $newArray;
        });
        $count = 0;
        /** @var array $actionPositions */
        foreach ($actionPositions as $actionType => $position) {
            if (!isset($selectedActions[$actionType])) {
                continue;
            }
            /** @var $config mixed[][] */
            foreach ($config['actions'] as $key => $action) {
                if ($action['type'] === $actionType) {
                    $newPosition = max((int) $position, $count);
                    $this->moveElement($config['actions'], $key, $newPosition);
                    break;
                }
            }
            $count++;
        }
        $massAction->setData('config', $config);
    }

    /**
     * @param array $actions
     * @param int $oldPosition
     * @param int $newPosition
     */
    public function moveElement(&$actions, $oldPosition, $newPosition)
    {
        $outputArray = array_splice($actions, $oldPosition, 1);
        array_splice($actions, $newPosition, 0, $outputArray);
    }
}
