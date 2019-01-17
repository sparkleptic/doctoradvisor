<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payumoney extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Doctor_model');  
		$this->load->model('Common_model'); 
		$this->return_url = ""; 
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authorization', 'image');
	}

	public function index() {
		if(!empty($_REQUEST['patient_id']) && !empty($_REQUEST['doctor_id']))
		{
			 $result = $this->Common_model->get_field_value_where("doctor","doctor_fee,doctor_fee_currency","doctor_id",$_REQUEST['doctor_id']);

			$this->return_url = base_url(); 
		     // all values are required

			if($result != B && !empty($result[0]->doctor_fee)){
                $currency = $result[0]->doctor_fee;
                $amount =  $result[0]->doctor_fee;
            }else{
                $amount = 100;
            }
	        
	        $product_info = $_REQUEST["product_info"];
	        $customer_name = $_REQUEST["customer_name"];
	        $customer_emial = $_REQUEST["customer_email"];
	        $customer_mobile = $_REQUEST["customer_mobile"];
	        $customer_address = $_REQUEST["customer_address"];

	        $MERCHANT_KEY = "gtKFFx"; //change  merchant with yours
	        $SALT = "eCwWELxi";  //change salt with yours 

	        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
	        //optional udf values 
	        $udf1 = $_REQUEST["patient_id"];
	        $udf2 = $_REQUEST["doctor_id"];
	        $udf3 = '';
	        $udf4 = '';
	        $udf5 = '';
	        
	         $hashstring = $MERCHANT_KEY . '|' . $txnid . '|' . $amount . '|' . $product_info . '|' . $customer_name . '|' . $customer_emial . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $SALT;
	         $hash = strtolower(hash('sha512', $hashstring));
	         
	        $success = base_url() . 'payumoney/status';  
	        $fail = base_url() . 'payumoney/status';
	        $cancel = base_url() . 'payumoney/status';
	        
	        
	         $data = array(
	            'mkey' => $MERCHANT_KEY,
	            'tid' => $txnid,
	            'hash' => $hash,
	            'amount' => $amount,           
	            'name' => $customer_name,
	            'productinfo' => $product_info,
	            'mailid' => $customer_emial,
	            'phoneno' => $customer_mobile,
	            'address' => $customer_address,
	            'action' => "https://test.payu.in", //for live change action  https://secure.payu.in
	            'sucess' => $success,
	            'failure' => $fail,
	            'cancel' => $cancel,
	            'udf1' => $udf1,
	            'udf2'  =>  $udf2       
	        );

	         $this->load->view('confirmation', $data); 
	     }else{
	     	 redirect(PROJECT_URL."home/paypal/?payment_id=0");
	     }  
	}


	public function pum_payment() {
	    $headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);

				if($Authorization[1] != "TechAdmin911"){
					$this->check_authorization($Authorization[1]);	
				}

				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$this->return_url = $value['return_url']; 

				 // all values are required
		        $amount =  $value['payble_amount'];
		        $product_info = $value['product_info'];
		        $customer_name = $value['customer_name'];
		        $customer_emial = $value['customer_email'];
		        $customer_mobile = $value['mobile_number'];
		        $customer_address = $value['customer_address'];
		        
		        //payumoney details
		    
		    
		        $MERCHANT_KEY = "gtKFFx"; //change  merchant with yours
		        $SALT = "eCwWELxi";  //change salt with yours 

		        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
		        //optional udf values 
		        $udf1 = $value['patient_id'];
		        $udf2 = $value['doctor_id'];
		        $udf3 = '1';
		        $udf4 = '2';
		        $udf5 = '';
		        
		         $hashstring = $MERCHANT_KEY . '|' . $txnid . '|' . $amount . '|' . $product_info . '|' . $customer_name . '|' . $customer_emial . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $SALT;
		         $hash = strtolower(hash('sha512', $hashstring));
		         
		        $success = base_url() . 'payumoney/status';  
		        $fail = base_url() . 'payumoney/status';
		        $cancel = base_url() . 'payumoney/status';
		        
		        
		         $data = array(
		            'mkey' => $MERCHANT_KEY,
		            'tid' => $txnid,
		            'hash' => $hash,
		            'amount' => $amount,           
		            'name' => $customer_name,
		            'productinfo' => $product_info,
		            'mailid' => $customer_emial,
		            'phoneno' => $customer_mobile,
		            'address' => $customer_address,
		            'action' => "https://test.payu.in", //for live change action  https://secure.payu.in
		            'sucess' => $success,
		            'failure' => $fail,
		            'cancel' => $cancel            
		        );
				
			    $send_data = array("status" => I,"data" => $data);
				echo json_encode($send_data);
				die;
			}else{
				$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
				echo json_encode($data);
				die;
			}
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}	
	}


	public function status(){
		$status = $this->input->post('status');
      	if (empty($status)) {
           redirect(PROJECT_URL."home/paypal/?payment_id=0");
        }
       
        $firstname = $this->input->post('firstname');
        $amount = $this->input->post('amount');
        $txnid = $this->input->post('txnid');
        $posted_hash = $this->input->post('hash');
        $key = $this->input->post('key');
        $productinfo = $this->input->post('productinfo');
        $email = $this->input->post('email');
        $salt = "dxmk9SZZ9y"; //  Your salt
        $add = $this->input->post('additionalCharges');
        If (isset($add)) {
            $additionalCharges = $this->input->post('additionalCharges');
            $retHashSeq = $additionalCharges . '|' . $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
        } else {

            $retHashSeq = $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
        }

        $data['patient_pum_paymet_status'] = $this->input->post("status");
	    $data['patient_name'] = $this->input->post("firstname");
	    $data['patient_pum_paymet_amount'] = $this->input->post("amount");
	    $data['patient_pum_paymet_txnid'] = $this->input->post("txnid");
	    $data['patient_pum_paymet_posted_hash'] = $this->input->post("hash");
	    $data['patient_pum_paymet_key'] = $this->input->post("key");
	    $data['patient_pum_paymet_additional_amount'] = $this->input->post("additionalCharges");
	    $data['patient_email'] = $this->input->post("email");
	    $data['patient_id'] = $this->input->post("udf1");
	    $data['doctor_id'] = $this->input->post("udf2");
	    $data['payment_type'] = "Payumoney";
	    $data['patient_pum_paymet_date'] = date("Y-m-d h:i:s");

	    $payment_data = $this->Common_model->insert('patient_pum_paymet',$data);
        redirect(PROJECT_URL."home/paypal/?payment_id=".$payment_data);
	}

	

	public function order_success() {
	    $status = $this->input->post("status");
	    $firstname = $this->input->post("firstname");
	    $amount = $this->input->post("amount");
	    $txnid = $this->input->post("txnid");
	    $posted_hash = $this->input->post("hash");
	    $key = $this->input->post("key");
	    $productinfo = $this->input->post("productinfo");
	    $email = $this->input->post("email");
	    $patient_id = $this->input->post("patient_id");
	    $doctor_id = $this->input->post("doctor_id");
	    $salt = "GQs7yium";

	    if ($this->input->post("additionalCharges")) {
	        $additionalCharges = $this->input->post("additionalCharges");
	        $retHashSeq = $additionalCharges . '|' . $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
	    } else {
	        $retHashSeq = $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
	    }
	    $hash = hash("sha512", $retHashSeq);

	    if ($hash != $posted_hash) {
	    	$data = array("status" => A,"message" => "Invalid Transaction. Please try again");
			echo json_encode($data);
			die;
	    } else {
	    	$data['patient_pum_paymet_status'] = $this->input->post("status");
		    $data['patient_name'] = $this->input->post("firstname");
		    $data['patient_pum_paymet_amount'] = $this->input->post("amount");
		    $data['patient_pum_paymet_txnid'] = $this->input->post("txnid");
		    $data['patient_pum_paymet_posted_hash'] = $this->input->post("hash");
		    $data['patient_pum_paymet_key'] = $this->input->post("key");
		    // $data['productinfo'] = $this->input->post("productinfo");
		    $data['patient_email'] = $this->input->post("email");
		    $data['patient_id'] = $this->input->post("patient_id");
		    $data['doctor_id'] = $this->input->post("doctor_id");
		    $data['payment_type'] = "Payumoney";
		    // $data['salt'] = "GQs7yium";
		    $payment_data = $this->Common_model->insert('patient_pum_paymet',$data);
		 
	    	$data['msg'] = "<h3>Thank You. Your order status is " . $status . ".</h3>";
	        $data['msg'] .= "<h4>Your Transaction ID for this transaction is " . $txnid . ".</h4>";
	        $data['msg'] .= "<h4>We have received a payment of Rs. " . $amount . ". Your order will soon be shipped.</h4>";
			$data = array("status" => I,"message" => $data['msg']);
			echo json_encode($data);
			die;
	    }
	}

	public function order_fail() {
	    $status = $this->input->post("status");
	    $firstname = $this->input->post("firstname");
	    $amount = $this->input->post("amount");
	    $txnid = $this->input->post("txnid");
	    $posted_hash = $this->input->post("hash");
	    $key = $this->input->post("key");
	    $productinfo = $this->input->post("productinfo");
	    $email = $this->input->post("email");
	    $salt = "fGxoywOg8S";
	    If ($this->input->post("additionalCharges")) {
	        $additionalCharges = $this->input->post("additionalCharges");
	        $retHashSeq = $additionalCharges . '|' . $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
	    } else {
	        $retHashSeq = $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
	    }
	    $hash = hash("sha512", $retHashSeq);
	    if ($hash != $posted_hash) {
	        $data = array("status" => A,"message" => "Invalid Transaction. Please try again");
			echo json_encode($data);
			die;
	    } else {
	    	$data = array("status" => A,"message" => "Your order status is " . $status . ".","try_again_url"=>"http://sforsuresh.in/", "transaction_id" => $txnid);
			echo json_encode($data);
			die;
	    }
	}
}