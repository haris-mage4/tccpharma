<?php
namespace Stathmos\Customize\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Download extends Action
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $file_url = 'http://www.tccpharma.com/pdf/TCC%20Credit%20Application%20(New)%20-%202018%20(Fillable).pdf';
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\"");
        readfile($file_url);
    }
}
