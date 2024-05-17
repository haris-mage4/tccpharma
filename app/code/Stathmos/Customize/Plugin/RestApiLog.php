<?php

namespace Stathmos\Customize\Plugin;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Webapi\Controller\Rest;

class RestApiLog
{
    private RestRequest $_request;
    private RestResponse $_response;

    public function __construct(
        RestRequest $request,
        RestResponse $response
    ) {
        $this->_request = $request;
        $this->_response = $response;
    }

    public function aroundDispatch(Rest $subject, callable $proceed, RequestInterface $request)
	{

		$result = $proceed($request);
		if (!file_exists(BP . '/var/log/APILOG-'.date("Y-m-d"))) {
		    mkdir(BP . '/var/log/APILOG-'.date("Y-m-d"), 0777, true);
		}
		if (strpos($request->getPathInfo(),'V1/products') !== false) {
		    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/APILOG-'.date("Y-m-d").'/Rest_products.log');
		}else{
			$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/APILOG-'.date("Y-m-d").'/Rest_other.log');
		}
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);

		$logger->info("[Path Info]");
		$logger->info(print_r($request->getPathInfo(), true));
		$logger->info("---------------------------------------------------");
		$logger->info("[Request]");
		$logger->info(print_r($this->_request->getBodyParams(), true));
		$logger->info("---------------------------------------------------");
		$logger->info("[Response]");
		$logger->info(print_r($result->getBody(), true));
		$logger->info("++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");
		return $result;
	}
}
