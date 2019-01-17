<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Admin_model'); 
		$this->load->model('Common_model');   
		
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authorization', 'image');
	}

	public function index(){
		$url = 'https://api.infermedica.com/v2/symptoms/';

		$ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","app_id: fbba825f", "app_key: 40e55f37855ecf432808f8a807db90b4")); 
        $head = curl_exec($ch); 
        curl_close ($ch);

		if(!empty($head))
		{
			$res = json_decode($head);
			echo "<pre>";
            echo count($res);
            print_r($res);
            echo "</pre>"; die;

			$new_res = array_column($res,'name', 'id');
			$data = array("status" => I,"data" => $res);
			echo json_encode($data);
			die;
		}else{
			$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_symptoms_list()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$url = 'https://api.infermedica.com/v2/symptoms';

				$ch = curl_init(); 
		        curl_setopt($ch, CURLOPT_URL, $url); 
		        curl_setopt($ch, CURLOPT_HEADER, false); 
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
		        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","app_id: fbba825f", "app_key: 40e55f37855ecf432808f8a807db90b4")); 
		        $head = curl_exec($ch); 
		        curl_close ($ch);

				if(!empty($head))
				{
					$res = json_decode($head);
					$new_res = array_column($res,'name', 'id');
					$data = array("status" => I,"data" => $new_res);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}

	public function check_user_authorization()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$Authorization = explode(" ", $headers['Authorization']);

				$status = $this->Common_model->check_authorization($Authorization[1],$value['user_type']);
				
				if($status == B)
				{
					$data = array("status" => K,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}
				else if($status == M)
				{
					$data = array("status" => K,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}
				else if($status != L)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}	
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
	

	public function get_language_list()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$language = $this->Common_model->get_all("language");
				if($language == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$language = json_encode($language);
					$language = json_decode($language,true);
					array_walk($language, function (& $item) {
					   $item['id'] = $item['language_id'];
					   unset($item['language_id']);
					   $item['itemName'] = $item['language'];
					   unset($item['language']);
					});

					$data = array('status' => I, "data" => $language);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_refund_payment_list()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$refund_payment = $this->Common_model->get_all("refund_payment");
				if($refund_payment == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					foreach ($refund_payment as $key => $v) {
						$name = $this->Common_model->get_field_value_where("patient","patient_fname, patient_lname","patient_id",$v->patient_id);
						if($name != B)
						$refund_payment[$key]->patient_name = $name[0]->patient_fname." ".$name[0]->patient_lname;

						$list = $this->Common_model->get_field_value_where("patient_schedule","*","patient_schedule_id",$v->patient_schedule_id);
						if($list != B){
							$refund_payment[$key]->schedule_details = $list;

							$patient_paymet = $this->Common_model->get_field_value_where("patient_pum_paymet","*","patient_pum_paymet_id",$list[0]->payment_id);
							if($patient_paymet != B)
							$refund_payment[$key]->patient_paymet_details = $patient_paymet[0];
						}
					}

					$data = array('status' => I, "data" => $refund_payment);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_admin_refund_payemts_list(){
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$admin_payments = $this->Common_model->get_all("admin_refund_payments");
				if($admin_payments == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					foreach ($admin_payments as $key => $v) {
						$name = $this->Common_model->get_field_value_where("patient","patient_fname, patient_lname","patient_id",$v->patient_id);
						if($name != B)
						$admin_payments[$key]->patient_name = $name[0]->patient_fname." ".$name[0]->patient_lname;
					}
					$data = array('status' => I, "data" => $admin_payments);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_payment_status()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$status = $this->Common_model->get_field_value_where("patient_pum_paymet","patient_pum_paymet_status","patient_pum_paymet_id",$value['payment_id']);
				$status = $status[0]->patient_pum_paymet_status;

				if($status == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array('status' => I, "data" => $status);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function add_contact_us()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{

				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$data_arrr = array();				

				$keys_arr = array('contact_name','contact_email','contact_message');

				
				foreach ($keys_arr as $key) {			
					if(!empty($value[$key])) {
						$data_arrr[$key] = $value[$key];
					}else{
						$data_arrr[$key] = "";
					}
				}

				$data_arrr['created_on'] = date("Y-m-d h:i:s");

				$contact_id = $this->Common_model->insert('contact_us',$data_arrr);

				if($contact_id == D) {
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"data" => $contact_id);
					echo json_encode($data);
					die;
				}
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
	

	public function user_login()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			$admin_data = $this->Common_model->user_login($value['username'],$value['password']);
			if($admin_data == B)
			{
				$data = array("status" => A,"message" => WRONG_CREDENTIAL_MSG);
				echo json_encode($data);
				die;
			}
			else if(!empty($admin_data))
			{
				$data = array("status" => I,"message" => LOGIN_MSG, "data" => $admin_data[0]);
				echo json_encode($data);
				die;
			}else{				
				$data = array("status" => A,"message" => "Sorry! This username and password does not exist.");
				echo json_encode($data);
				die;
			}			
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function verify_username(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$admin_data = $this->Common_model->check_exist('user','user_uname',$value['doctor_uname']);
				if($admin_data == J)
				{
					$data = array("status" => I,"message" => J);
					echo json_encode($data);
					die;
				}
				else if($admin_data == B)
				{
					$data = array("status" => I,"message" => B);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}		
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_payment_byId(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$payment = $this->Common_model->get_where('patient_pum_paymet','patient_pum_paymet_id',$value['id']);
				if($payment == B)
				{
					$data = array("status" => I,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}
				else
				{
					foreach ($payment as $key => $v) {
						$name = $this->Common_model->get_field_value_where("patient","patient_fname, patient_lname","patient_id",$v->patient_id);
						if($name != B)
						$payment[$key]->patient_name = $name[0]->patient_fname." ".$name[0]->patient_lname;

						$name = $this->Common_model->get_field_value_where("doctor","doctor_fname, doctor_lname","doctor_id",$v->doctor_id);
						if($name != B)
						$payment[$key]->doctor_name = $name[0]->doctor_fname." ".$name[0]->doctor_lname;
					}
					
					$data = array("status" => I,"data" => $payment);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}		
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_refund_payment_byId(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$payment = $this->Common_model->get_where('refund_payment','refund_payment_id',$value['id']);
				//print_r($payment);
				if($payment == B)
				{
					$data = array("status" => I,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}
				else
				{
					foreach ($payment as $key => $v) {
						$name = $this->Common_model->get_field_value_where("patient","patient_fname, patient_lname","patient_id",$v->patient_id);
						if($name != B)
						$payment[$key]->patient_name = $name[0]->patient_fname." ".$name[0]->patient_lname;

						$patient_schedule = $this->Common_model->get_field_value_where("patient_schedule","*","patient_schedule_id",$v->patient_schedule_id);
						if($patient_schedule != B){
							$payment[$key]->schedule_details = $patient_schedule;

							$doctor = $this->Common_model->get_field_value_where("doctor","doctor_fname, doctor_lname","doctor_id",$patient_schedule[0]->doctor_id);
							if($doctor != B)
							$payment[$key]->doctor_name = $doctor[0]->doctor_fname." ".$doctor[0]->doctor_lname;

							$patient_paymet = $this->Common_model->get_field_value_where("patient_pum_paymet","*","patient_pum_paymet_id",$patient_schedule[0]->payment_id);
							if($patient_paymet != B)
							$payment[$key]->patient_paymet_details = $patient_paymet;
						}
					}
					
					$data = array("status" => I,"data" => $payment);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}		
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_doctors_payment_details_byId(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$payment = $this->Common_model->get_where('admin_payments','payment_id',$value['id']);
				if($payment == B)
				{
					$data = array("status" => I,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}
				else
				{
					foreach ($payment as $key => $v) {	
						$name = $this->Common_model->get_field_value_where("doctor","doctor_fname, doctor_lname","doctor_id",$v->doctor_id);
						if($name != B)
						$payment[$key]->doctor_name = $name[0]->doctor_fname." ".$name[0]->doctor_lname;
					}
					
					$data = array("status" => I,"data" => $payment);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}		
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_rating_details_byId(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$schedul = $this->Common_model->get_where('patient_schedule','patient_schedule_id',$value['id']);
				if($schedul == B)
				{
					$data = array("status" => I,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}
				else
				{
					foreach ($schedul as $key => $v) {
						$name = $this->Common_model->get_field_value_where("patient","patient_fname, patient_lname","patient_id",$v->patient_id);
						if($name != B)
						$schedul[$key]->patient_name = $name[0]->patient_fname." ".$name[0]->patient_lname;

						$name = $this->Common_model->get_field_value_where("doctor","doctor_fname, doctor_lname","doctor_id",$v->doctor_id);
						if($name != B)
						$schedul[$key]->doctor_name = $name[0]->doctor_fname." ".$name[0]->doctor_lname;

						$name = $this->Common_model->get_field_value_where("language","language","language_id",$v->patient_language);
						if($name != B)
						$schedul[$key]->patient_language = $name[0]->language;
					}
					
					$data = array("status" => I,"data" => $schedul);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}		
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}



	public function user_logout()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$Authorization = explode(" ", $headers['Authorization']);
				$admin = $this->Common_model->user_logout($Authorization[1], $value['user_id']);
				if($admin == I)
				{
					$data = array("status" => I,"message" => LOGOUT_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}	
		

	public function get_cities_list()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$cities = $this->Common_model->get_all("cities");
				if($cities == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array('status' => I, "data" => $cities);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}

	public function get_payments_list()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$payment = $this->Common_model->get_all("patient_pum_paymet");				
				if($payment == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					foreach ($payment as $key => $v) {
						$name = $this->Common_model->get_field_value_where("patient","patient_fname, patient_lname","patient_id",$v->patient_id);
						if($name != B)
						$payment[$key]->patient_name = $name[0]->patient_fname." ".$name[0]->patient_lname;

						$name = $this->Common_model->get_field_value_where("doctor","doctor_fname, doctor_lname","doctor_id",$v->doctor_id);
						if($name != B)
						$payment[$key]->doctor_name = $name[0]->doctor_fname." ".$name[0]->doctor_lname;
					}
					$data = array('status' => I, "data" => $payment);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_rating_list()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$schedule = $this->Common_model->get_all("patient_schedule");				
				if($schedule == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					foreach ($schedule as $key => $v) {
						$name = $this->Common_model->get_field_value_where("patient","patient_fname, patient_lname","patient_id",$v->patient_id);
						if($name != B)
						$schedule[$key]->patient_name = $name[0]->patient_fname." ".$name[0]->patient_lname;

						$name = $this->Common_model->get_field_value_where("doctor","doctor_fname, doctor_lname","doctor_id",$v->doctor_id);
						if($name != B)
						$schedule[$key]->doctor_name = $name[0]->doctor_fname." ".$name[0]->doctor_lname;
					}
					$data = array('status' => I, "data" => $schedule);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function get_states_list()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$states = $this->Common_model->get_all("states");
				if($states == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array('status' => I, "data" => $states);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}

	public function get_countries_list()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$countries = $this->Common_model->get_all("countries");
				if($countries == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array('status' => I, "data" => $countries);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}	

	public function get_state_by_code()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json') {			
			if(!empty($headers['Authorization']) || !empty($headers['authorization'])) {

				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$states = $this->Common_model->get_state_by_cid($value);
				if($states == B) {
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($states);
					die;
				}else{
					$data = array('status' => I, "data" => $states);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}

	public function get_cities_by_code()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json') {			
			if(!empty($headers['Authorization']) || !empty($headers['authorization'])) {

				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$cities = $this->Common_model->get_cities_by_sid($value);
				if($cities == B) {
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($cities);
					die;
				}else{
					$data = array('status' => I, "data" => $cities);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}

	public function get_doctors_payemts_byAdmin(){
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$admin_payments = $this->Common_model->get_all("admin_payments");
				if($admin_payments == B)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					foreach ($admin_payments as $key => $v) {
						$name = $this->Common_model->get_field_value_where("doctor","doctor_fname, doctor_lname","doctor_id",$v->doctor_id);
						if($name != B)
						$admin_payments[$key]->doctor_name = $name[0]->doctor_fname." ".$name[0]->doctor_lname;
					}
					$data = array('status' => I, "data" => $admin_payments);
					echo json_encode($data);
					die;
				}	
			}else{
				$data = array('status' => A, "message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}

		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}
}