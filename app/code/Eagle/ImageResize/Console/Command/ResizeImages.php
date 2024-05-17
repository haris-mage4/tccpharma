<?php
declare(strict_types=1);

namespace Eagle\ImageResize\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\DB\Query\BatchIteratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Service\ImageResize;
use Magento\MediaStorage\Service\ImageResizeScheduler;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Catalog\Model\ResourceModel\Product\Image as ProductImage;

/**
 * Resizes product images according to theme view definitions.
 */

class ResizeImages extends Command
{
    /**
     * Asynchronous image resize mode
     */
    const ASYNC_RESIZE = 'async';

    /**
     * @var ImageResizeScheduler
     */
    private $imageResizeScheduler;

    /**
     * @var ImageResize
     */
    private $imageResize;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ProgressBarFactory
     */

    private $progressBarFactory;

    /**
     * @var ProductImage
     */
    private $productImage;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var Generator
     */
    private $batchQueryGenerator;

    /**
     * @param State $appState
     * @param ImageResize $imageResize
     * @param ImageResizeScheduler $imageResizeScheduler
     * @param ProgressBarFactory $progressBarFactory
     * @param ProductImage $productImage
     * @param Generator $generator
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        State $appState,
        ImageResize $imageResize,
        ImageResizeScheduler $imageResizeScheduler,
        ProgressBarFactory $progressBarFactory,
        ProductImage $productImage,
        Generator $generator,
        ResourceConnection $resourceConnection
    )
    {
        parent::__construct();
        $this->appState = $appState;
        $this->imageResize = $imageResize;
        $this->imageResizeScheduler = $imageResizeScheduler;
        $this->progressBarFactory = $progressBarFactory;
        $this->productImage = $productImage;
        $this->batchQueryGenerator = $generator;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('eagle:product:resize')
            ->setDefinition($this->getOptionsList());
        $this->addOption(
            'product_id',
            null,
            InputOption::VALUE_REQUIRED,
            'Add Product ID'
        );
    }

    /**
     * Image resize command options list
     *
     * @return array
     */
    private function getOptionsList() : array
    {
        return [
            new InputOption(
                self::ASYNC_RESIZE,
                'a',
                InputOption::VALUE_NONE,
                'Resize image in asynchronous mode'
            ),
        ];
    }

    /**
     * @inheritdoc
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $product_id = $input->getOption('product_id');
        return $this->executeAsync($output, $product_id);
    }

    /**
     * Schedule asynchronous image resizing
     *
     * @param OutputInterface $output
     * @param $product_id
     */
    private function executeAsync(OutputInterface $output, $product_id)
    {
        try
        {
            $errors = [];
            $this->appState->setAreaCode(Area::AREA_GLOBAL);

            $progress = $this->progressBarFactory->create(
                [
                    'output' => $output,
                    'max' => $this->getCountUsedProductImages($product_id)
                ]
            );
            $progress->setFormat(
                "%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% \t| <info>%message%</info>"
            );
            if ($output->getVerbosity() !== OutputInterface::VERBOSITY_NORMAL)
            {
                $progress->setOverwrite(false);
            }
            $productImages = $this->getUsedProductImages($product_id);
            foreach ($productImages as $image)
            {
                $result = $this->imageResizeScheduler->schedule($image['filepath']);
                if (!$result)
                {
                    $errors[$image['filepath']] = 'Error image scheduling: ' . $image['filepath'];
                }
                $progress->setMessage($image['filepath']);
                $progress->advance();
            }
        }
        catch (\Exception $e)
        {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Cli::RETURN_FAILURE;
        }
        $output->write(PHP_EOL);
        if (count($errors))
        {
            $output->writeln("<info>Product images resized with er-rors:</info>");
            foreach ($errors as $error)
            {
                $output->writeln("<error>{$error}</error>");
            }
        }
        else
        {
            $output->writeln("<info>Product images scheduled successful-ly</info>");
        }
        return Cli::RETURN_SUCCESS;
    }

    /**
     * Get used product images.
     *
     * @param int $product_id
     * @return \Generator
     * @throws LocalizedException
     */
    private function getUsedProductImages($product_id): \Generator
    {
        $batchSelectIterator = $this->batchQueryGenerator->generate(
            'value_id',
            $this->getUsedImagesSelect($product_id),
            100,
            BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
        );
        foreach ($batchSelectIterator as $select)
        {
            foreach ($this->resourceConnection->getConnection()->fetchAll($select) as $key => $value)
            {
                yield $key => $value;
            }
        }
    }

    /**
     * Return select to fetch all used product images.
     *
     * @param int $product_id
     * @return Select
     */
    private function getUsedImagesSelect($product_id): Select
    {
        $query = 'images.disabled = 0 AND image_value.disabled = 0 AND image_value.entity_id = '.$product_id;
        return $this->resourceConnection->getConnection()->select()->distinct()
            ->from(
                ['images' => $this->resourceConnection->getTableName(Gallery::GALLERY_TABLE)],
                'value as filepath'
            )->joinInner(
                ['image_value' => $this->resourceConnection->getTableName(Gallery::GALLERY_VALUE_TABLE)],
                'images.value_id = image_value.value_id',
                []
            )->where($query);
    }

    /**
     * Get the number of unique and used images of products.
     *
     * @param $product_id
     * @return int
     */
    private function getCountUsedProductImages($product_id)
    {
        $select = $this->getUsedImagesSelect($product_id)
            ->reset('columns')
            ->reset('distinct')
            ->columns(
                new \Zend_Db_Expr('count(distinct value)')
            );
        return (int) $this->resourceConnection->getConnection()->fetchOne($select);
    }
}

