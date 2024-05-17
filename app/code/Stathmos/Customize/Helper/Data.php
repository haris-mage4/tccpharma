<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Stathmos\Customize\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Size;

/**
 * ImportExport data helper
 *
 * @api
 * @since 100.0.2
 */
class Data extends AbstractHelper
{
	protected ProductRepositoryInterface $productRepository;
    private Size $_fileSize;

    public function __construct(
        Context $context,
        Size $fileSize,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_fileSize = $fileSize;
        $this->productRepository = $productRepository;
        parent::__construct(
            $context
        );
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getProduct($productId)
    {
      	return $this->productRepository->getById($productId);
    }
}
