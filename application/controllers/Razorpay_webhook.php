<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH."libraries/razorpay/razorpay-php/Razorpay.php");
use Razorpay\Api\Api;
use Razorpay\Api\Errors;
use Razorpay\Api\Errors\SignatureVerificationError;

class Razorpay_webhook extends CI_Controller {
	const PAYMENT_AUTHORIZED    = 'payment.authorized';
	const PAYMENT_FAILED        = 'payment.failed';
	const ORDER_PAID            = 'order.paid';
	
	public function webhookReceiver()
	{
		$json = file_get_contents('php://input');
		$api = new Api(RAZOR_KEY, RAZOR_SECRET_KEY);
		try
		{
			$api->utility->verifyWebhookSignature($json, $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'], WEBHOOK_SECRET);
		}
		catch (Errors\SignatureVerificationError $e)
		{
			$this->sendEmail(json_encode($e->getMessage()));
			exit;
		}
		$responseArray = json_decode($json);
		$payload = $responseArray->payload;
		$payment = $payload->payment;
		$entity = $payment->entity;
		$notes = $entity->notes;

		// apply your business logic below based on your conditions

		if($responseArray->event == 'payment.captured' || $responseArray->event == 'payment.authorized'){
		// Business logic
		}

		exit;
	}

	public function generateRandomString($n){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $n; $i++) { 
			$index = rand(0, strlen($characters) - 1); 
			$randomString .= $characters[$index]; 
		}
		return $randomString; 
	}
}
?>