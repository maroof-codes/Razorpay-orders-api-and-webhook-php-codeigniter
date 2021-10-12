<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH."libraries/razorpay/razorpay-php/Razorpay.php");

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class Razorpay extends CI_Controller {


	public function index(){
		echo "<a href = '".base_url('Razorpay/pay')."' >PAY NOW</a>";
	}

	/**
	 * This function creates order and loads the payment methods
	 * @constant RAZOR_KEY 					your razorpay api key
	 * @constant RAZOR_SECRET_KEY			your razorpay secret key
	 */
	
	public function pay(){
		$api = new Api(RAZOR_KEY, RAZOR_SECRET_KEY);
		/**
		 * You can calculate payment amount as per your logic
		 * Always set the amount from backend for security reasons
		 */
		$totalPayableAmt = 1;
		$razorpayOrder = $api->order->create(array(
			'receipt'         => "2021000001", // add a fancy but descriptive reciept number
			'amount'          => $totalPayableAmt * 100, // 1000 rupees in paise
			'currency'        => 'INR',
			'payment_capture' => 1 // auto capture
		));

		$razorpayOrderId = $razorpayOrder['id'];
		$_SESSION['razorpay_order_id'] = $razorpayOrderId;
		$data = $this->prepareData($totalPayableAmt,$razorpayOrderId);
		$this->load->view('razorpay_view',array('data' => $data,'amount'=>$totalPayableAmt));
	}

	/**
	 * This function verifies the payment,after successful payment
	 */
	public function verify()
	{
		$success = true;
		$amount="";
		
		$error = "payment_failed";
		if (empty($_POST['razorpay_payment_id']) === false) {
			$api = new Api(RAZOR_KEY, RAZOR_SECRET_KEY);
			try {
				$attributes = array(
					'razorpay_order_id'  => $_SESSION['razorpay_order_id'],
					'razorpay_payment_id' => $_POST['razorpay_payment_id'],
					'razorpay_signature' => $_POST['razorpay_signature'],
				);
				$api->utility->verifyPaymentSignature($attributes);
			} catch(SignatureVerificationError $e) {
				$success = false;
				$error = 'Razorpay_Error : ' . $e->getMessage();
				print_r($error);exit;
			}

			if ($success === true) {
				print_r("payment successfull");
			// apply your business logic

			}
		}
		else {
			// if failed redirect to your error page
			redirect('redirect-url');
		}

	}

	/**
	 * This function preprares payment parameters
	 * @param $amount
	 * @param $razorpayOrderId
	 * @return array
	 */
	public function prepareData($amount,$razorpayOrderId){	
		$data = array(
			"key" => RAZOR_KEY,
			"amount" => $amount,
			"name" => "Maroof",
			"description" => "Razorpay sample code",
			"image" => "your image source",
			"prefill" => array(
				"name"  => "Name",
				"email"  => "abc@gmail.com",
				"contact" => "9876543210",
				
			),
			"notes"  => array(
				"address"  => "Karnataka",
				"merchant_order_id" => "Your merchant id",
				"custom_data1" => 'custom_value1',
				"custom_data2" => 'custom_value2'
			),
			"theme"  => array(
				"color"  => "#F37254"
			),
			"order_id" => $razorpayOrderId,
		);
		return $data;
	}
}?>