<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Request a Quote Attributes for Magento 2 (System)
 */

use Amasty\QuoteAttributes\Model\Attribute\Command\SaveInterface;
use Amasty\QuoteAttributes\Model\Attribute\Data\Validator\Codes;
use Amasty\QuoteAttributes\Model\Attribute\Query\GetNewInterface;
use Amasty\QuoteAttributes\Model\Source\Attribute\FrontendInput;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

/** @var GetNewInterface $getNew */
$getNew = Bootstrap::getObjectManager()->create(GetNewInterface::class);
/** @var SaveInterface $saveCommand */
$saveCommand = Bootstrap::getObjectManager()->create(SaveInterface::class);

$attribute1 = $getNew->execute();
$attribute1->setAttributeCode('amasty_quote_attribute_1');
$attribute1->setFrontendInput(FrontendInput::TEXT);
$attribute1->getExtensionAttributes()->setAmastyStores([Store::DEFAULT_STORE_ID]);
$saveCommand->execute($attribute1);

$attribute2 = $getNew->execute();
$attribute2->setAttributeCode('amasty_quote_attribute_2');
$attribute2->setFrontendInput(FrontendInput::TEXT);
$attribute2->setValidateRules(['input_validation' => Codes::DECIMAL]);
$attribute2->setDataModel(\Amasty\QuoteAttributes\Model\Attribute\Data\Text::class);
$attribute2->getExtensionAttributes()->setAmastyStores([Store::DEFAULT_STORE_ID]);
$saveCommand->execute($attribute2);

$attribute3 = $getNew->execute();
$attribute3->setAttributeCode('amasty_quote_attribute_3');
$attribute3->setFrontendInput(FrontendInput::TEXT);
$attribute3->setValidateRules(['input_validation' => Codes::EMAIL]);
$attribute3->getExtensionAttributes()->setAmastyStores([Store::DEFAULT_STORE_ID]);
$saveCommand->execute($attribute3);

$attribute4 = $getNew->execute();
$attribute4->setAttributeCode('amasty_quote_attribute_4');
$attribute4->setFrontendInput(FrontendInput::TEXT);
$attribute4->setIsRequired(true);
$attribute4->getExtensionAttributes()->setAmastyStores([Store::DEFAULT_STORE_ID]);
$saveCommand->execute($attribute4);

$attribute5 = $getNew->execute();
$attribute5->setAttributeCode('amasty_quote_attribute_5');
$attribute5->setFrontendInput(FrontendInput::SELECT);
$attribute5->setIsRequired(true);
$attribute5->getExtensionAttributes()->setAmastyStores([Store::DEFAULT_STORE_ID]);
$saveCommand->execute($attribute5);

$attribute6 = $getNew->execute();
$attribute6->setAttributeCode('amasty_quote_attribute_6');
$attribute6->setFrontendInput(FrontendInput::TEXT);
$attribute6->setValidateRules(['input_validation' => Codes::ALPHA_NUMERIC]);
$attribute6->getExtensionAttributes()->setAmastyStores([Store::DEFAULT_STORE_ID]);
$saveCommand->execute($attribute6);

$attribute7 = $getNew->execute();
$attribute7->setAttributeCode('amasty_quote_attribute_7');
$attribute7->setFrontendInput(FrontendInput::TEXT);
$attribute7->setValidateRules(['input_validation' => Codes::URL]);
$saveCommand->execute($attribute7);
