<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Patient extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Patient_model');  
		$this->load->model('Common_model');  
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authorization', 'image');
	}


	public function index(){

		$patient_data = $this->Patient_model->getPatientInfo_ById(1);
		$patient_data = (array) $patient_data;

		if($patient_data == B) {
			$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
			echo json_encode($data);
			die;
		}else {					
			$data = array("status" => I, "data" => $patient_data);
			echo json_encode($data);
			die;
		}		
		$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
		echo json_encode($data);
		die;
	}


	public function get_patients_list(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);

				if($Authorization[1] != "TechAdmin911"){
					//$this->check_authorization($Authorization[1]);	
				}			
			
				$patient_data = $this->Common_model->get_all("patient");
				if($patient_data == B) {
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {

					foreach ($patient_data as $key => $value) {	
						$patient_data[$key]->doctor_attend = $this->Common_model->get_count_where("patient_schedule","patient_id",$value->patient_id);					
						if(!empty($value->patient_city)){
							$name = $this->Common_model->get_field_value_where("cities","name","id",$value->patient_city);
							$patient_data[$key]->city_name = $name[0]->name;
						}	

						if(!empty($value->patient_state)){
							$name = $this->Common_model->get_field_value_where("states","name","id",$value->patient_state);
							$patient_data[$key]->state_name = $name[0]->name; 

						}	

						if(!empty($value->patient_country)){
							$name = $this->Common_model->get_field_value_where("countries","name","id",$value->patient_country);
							$patient_data[$key]->country_name = $name[0]->name; 
						}	
						continue;				
					}

					$data = array("status" => I, "data" => $patient_data);
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

	public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}


	public function add_refund_application()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{

				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$data_arrr = array();				

				$keys_arr = array('patient_id','patient_schedule_id','refund_payment_reason','patient_paypal_id');

				
				foreach ($keys_arr as $key) {			
					if(!empty($value[$key])) {
						$data_arrr[$key] = $value[$key];
					}else{
						$data_arrr[$key] = "";
					}
				}

				$data_arrr['refund_payment_status'] = 0;
				$data_arrr['refund_payment_created_date'] = date("Y-m-d h:i:s");

				$refund_payment_id = $this->Common_model->insert('refund_payment',$data_arrr);

				if($refund_payment_id == D) {
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"data" => $refund_payment_id);
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


	public function add_patient()
	{
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

				$data = array();

				$keys_arr = array('patient_fname','patient_lname','patient_display_name','patient_uname','patient_email','patient_address','patient_city','patient_state','patient_country','patient_contact_no','patient_account_info','patient_subscription_id','patient_payment_id','patient_status','patient_age','patient_height','patient_weight','patient_gender');

				foreach ($keys_arr as $key) {			
					if(!empty($value[$key]))
					{
						$data[$key] = $value[$key];
					}else{
						$data[$key] = "";
					}
				}

				if(!empty($value['patient_password'])){				
					$data['patient_password'] = isset($value['patient_password']) ? md5($value['patient_password']) : '';
				}

				$data['patient_created_on'] = date("Y-m-d h:i:s");

				$patient_check = $this->Common_model->check_exist('patient','patient_email',$data['patient_email']);
				if($patient_check == J){
					$data = array("status" => A,"message" => "This email address already exist");
					echo json_encode($data);
					die;
				}else{
						$user_data = array();
						$user_data['user_uname'] = isset($value['patient_uname']) ? $value['patient_uname'] : '';
						$user_data['user_email'] = isset($value['patient_email']) ? $value['patient_email'] : '';
						$user_data['user_password'] = isset($value['patient_password']) ? md5($value['patient_password']) : '';
						$user_data['user_firstname'] = isset($value['patient_fname']) ? $value['patient_fname'] : '';
						$user_data['user_lastname'] = isset($value['patient_lname']) ? $value['patient_lname'] : '';
						$user_data['user_type'] = 'patient';
						$user_data['user_gender'] = "";
						$user_data['user_token'] = $this->generateRandomString(100);
						$effectiveDate = strtotime("+24 hours", strtotime(date("Y-m-d h:i:s")));
						$user_data['user_login_end_time'] = date("Y-m-d h:i:s",$effectiveDate);
						$user_data['user_created_on'] = date("Y-m-d h:i:s");

						$user_id = $this->Common_model->insert('user', $user_data);
						if($user_id == D) {
							$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
							echo json_encode($data);
							die;
						}else{
							$data['user_id'] = $user_id;
				
							$patient_data = $this->Common_model->insert('patient',$data);
							if($patient_data == D)
							{
								$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
								echo json_encode($data);
								die;
							}else{
								$datas = array(); 
								if(!empty($value['reports']['value']))
								{
									file_put_contents("uploads/patient/patient_".$value['patient_id']."_".$value['reports']['filename'], base64_decode($value['reports']['value']));
									$datas['patient_disease_medical_reports'] = base_url()."uploads/patient/patient_".$value['patient_id']."_".$value['reports']['filename'];
								}	

								$keys_arr = array('patient_disease_discription','patient_disease_symptoms','patient_disease_current_medicine','patient_disease_duration','patient_disease_from','patient_disease_to','patient_disease_previous_consultant');
								foreach ($keys_arr as $key) {			
									if(!empty($value[$key]))
									{
										$datas[$key] = $value[$key];
									}else{
										$datas[$key] = "";
									}
								}

								$datas['patient_id'] = $patient_data;								
								$patient_disease_data = $this->Common_model->insert('patient_disease',$datas);

								$patient_data = $this->Patient_model->getPatientInfo_ById($datas['patient_id']);
								$patient_data = !empty($patient_data) ? current($patient_data) : array();

								if($patient_disease_data == D)
								{
									$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
									echo json_encode($data);
									die;
								}else{
									$data = array("status" => I,"message" => ADD_MSG, "data" => $patient_data);
									echo json_encode($data);
									die;
								}				
							}
						}
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


	public function get_patients_by_id()
	{
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
				$patient_data = $this->Patient_model->getPatientInfo_ById($value['patient_id']);
				$patient_data = current($patient_data);

				if($patient_data == B) {
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {
					if( $patient_data['patient_city'] !== '' ){
						$name = $this->Common_model->get_field_value_where("cities","name","id",$patient_data['patient_city']);
						$patient_data['city_name'] = $name[0]->name;
					}	

					if( $patient_data['patient_state'] !== '' ){
						$name = $this->Common_model->get_field_value_where("states","name","id",$patient_data['patient_state']);
						$patient_data['state_name'] = $name[0]->name; 

					}	

					if( $patient_data['patient_country'] !== ''){
						$name = $this->Common_model->get_field_value_where("countries","name","id",$patient_data['patient_country']);
						$patient_data['country_name'] = $name[0]->name; 
					}	

					$data = array("status" => I, "data" => $patient_data);
					echo json_encode($data);
					die;
				}			
			}else{
				$data = array("status" => A,"message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}			
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}	


	public function getPatientSchedule_ById()
	{
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
				$patient_schedules = $this->Patient_model->getPatientSchedule_ById($value['patient_id']);
				$patient_schedules = (array)$patient_schedules;

				$newDoc = array();

				foreach ($patient_schedules as $k => $v) {
					$v =  (array) $v; 
					if(!empty($v['specialization_id'])){
						$d = $this->Common_model->get_field_value_where("disease",'disease_name','disease_id',$v['specialization_id']);
						$newDoc[$k]['specialization_name'] = (is_array($d)) ? (array) current($d) : array();
					}

					if(!empty($v['doctor_id'])){ //echo $v['patient_id'];
						$field = "doctor_fname, doctor_lname, doctor_profile_pic, doctor_specilization, doctor_language, doctor_education, doctor_city, doctor_state, doctor_country"; 
						$d = $this->Common_model->get_field_value_where("doctor",$field,'doctor_id',$v['doctor_id']);

						$doctor_details = (is_array($d)) ? (array) current($d) : array();

						if( isset($doctor_details['doctor_specilization']) && sizeof($doctor_details['doctor_specilization']) > 0 ) {
							$doctor_details['doctor_specilization'] = json_decode($doctor_details['doctor_specilization']);
						}

						if( isset($doctor_details['doctor_language']) && sizeof($doctor_details['doctor_language']) > 0 ) {
							$doctor_details['doctor_language'] = json_decode($doctor_details['doctor_language']);
						}

						$newDoc[$k]['doctor_details'] = $doctor_details;
					}

					if(!empty($v['patient_language'])){
						$d = $this->Common_model->get_field_value_where("language",'language','language_id',$v['patient_language']);
						$newDoc[$k]['language_name'] = (array) current($d);
					}

					$newDoc[$k]['schedule_detail'] = $v;
					//print_r($newDoc);
				}

				
				if($patient_schedules == B) {					
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {					

					$data = array("status" => I, "data" => $newDoc);
					echo json_encode($data);
					die;
				}			
			}else{
				$data = array("status" => A,"message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}			
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}

	public function getMyAppoinment_ById()
	{
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


				$patient_data = $this->patient_model->getAppoinment_ById($value['patient_id']);
				$patient_data = (array)$patient_data;

				if($patient_data == B) {
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {				

					$data = array("status" => I, "data" => $patient_data);
					echo json_encode($data);
					die;
				}			
			}else{
				$data = array("status" => A,"message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}			
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function getAppointmentDetailsById(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				//echo $id.'-'.$status; die;
				/*$Authorization = explode(" ", $headers['Authorization']);
				if($Authorization[1] != "TechAdmin911"){
					$this->check_authorization($Authorization[1],'doctor');	
				}	*/
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				//print_r($value); die;

				if(!empty($value['patient_id'])){
					$doctor_data = $this->Common_model->get_field_value_where("patient_schedule",'*','patient_schedule_id',$value['schedule']);
					$doctor_data = (array)$doctor_data;
					$newDoc = array();
					foreach ($doctor_data as $k => $v) {
						$v =  (array) $v; 
						if(!empty($v['specialization_id'])){
							$d = $this->Common_model->get_field_value_where("disease",'disease_name','disease_id',$v['specialization_id']);
							$newDoc[$k]['specialization_name'] = (array) current($d);
						}

						if(!empty($v['patient_id'])){ //echo $v['patient_id'];
							$d = $this->Common_model->get_field_value_where("patient",'*','patient_id',$v['patient_id']);
							$newDoc[$k]['patient_details'] = (array) current($d);
						}

						if(!empty($v['doctor_id'])){ //echo $v['patient_id'];
							$field = "doctor_fname, doctor_lname, doctor_profile_pic, doctor_specilization, doctor_language, doctor_education, doctor_city, doctor_state, doctor_country"; 
							$d = $this->Common_model->get_field_value_where("doctor",$field,'doctor_id',$v['doctor_id']);

							$doctor_details = (is_array($d)) ? (array) current($d) : array();

							$newDoc[$k]['doctor_details'] = (array) current($d);
							if( isset($doctor_details['doctor_specilization']) && sizeof($doctor_details['doctor_specilization']) > 0 ) {
								$newDoc[$k]['doctor_details']['doctor_specilization'] = json_decode($doctor_details['doctor_specilization']);
							}

							if( isset($doctor_details['doctor_language']) && sizeof($doctor_details['doctor_language']) > 0 ) {
								$newDoc[$k]['doctor_details']['doctor_language'] = json_decode($doctor_details['doctor_language']);
							}

							if( $doctor_details['doctor_city'] !== '' ){
								$name = $this->Common_model->get_field_value_where("cities","name","id",$doctor_details['doctor_city']);
								$newDoc[$k]['doctor_details']['city_name'] = $name[0]->name;
							}	

							if( $doctor_details['doctor_state'] !== '' ){
								$name = $this->Common_model->get_field_value_where("states","name","id",$doctor_details['doctor_state']);
								$newDoc[$k]['doctor_details']['state_name'] = $name[0]->name; 

							}	

							if( $doctor_details['doctor_country'] !== ''){
								$name = $this->Common_model->get_field_value_where("countries","name","id",$doctor_details['doctor_country']);
								$newDoc[$k]['doctor_details']['country_name'] = $name[0]->name; 
							}	

							//$newDoc[$k]['doctor_details'] = $doctor_details;
							
						}

						if(!empty($v['patient_language'])){
							$d = $this->Common_model->get_field_value_where("language",'language','language_id',$v['patient_language']);
							$newDoc[$k]['language_name'] = (array) current($d);
						}

						$newDoc[$k]['schedule_detail'] = $v;
						//print_r($newDoc);
					}
					//print_r($doctor_data);
					if($doctor_data == B) {
						$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
						echo json_encode($data);
						die;
					}else {				

						$data = array("status" => I, "data" => $newDoc);
						echo json_encode($data);
						die;
					}
				}else{
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}			
			}else{
				$data = array("status" => A,"message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}			
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}



	public function add_patient_disease()
	{
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
				$data = array();

				if(!empty($value['reports']['value']))
				{
					file_put_contents("uploads/patient/patient_".$value['patient_id']."_".$value['reports']['filename'], base64_decode($value['reports']['value']));
					$data['patient_disease_medical_reports'] = base_url()."uploads/patient/patient_".$value['patient_id']."_".$value['reports']['filename'];
				}	

				$keys_arr = array('patient_id','patient_disease_discription','patient_disease_symptoms','patient_disease_current_medicine','patient_disease_duration','patient_disease_from','patient_disease_to','patient_disease_previous_consultant','patient_zip');
				foreach ($keys_arr as $key) {			
					if(!empty($value[$key]))
					{
						$data[$key] = $value[$key];
					}else{
						$data[$key] = "";
					}
				}
				
				$patient_data = $this->Common_model->insert('patient_disease',$data);
				if($patient_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => ADD_MSG, "id" => $patient_data);
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


	public function add_patient_schedule()
	{
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
				$data = array();

				if(!empty($value['patient_document']) && isset($value['patient_document']) ) {
					foreach ($value['patient_document'] as $ke => $val) {
						$file_name = strtotime(date("Y-m-d h:i:s"))."_".$val['filename'];
						file_put_contents("uploads/patients/patient_".$file_name, base64_decode($val['value']));
						$cer = base_url()."uploads/patients/patient_".$file_name;
						$data['patient_document'][$ke]['img'] = $cer;
					}
				}

				if(!empty($data['patient_document'])){
					$data['patient_document'] = json_encode($data['patient_document']);
				}	

				$keys_arr = array('patient_id','specialization_id','doctor_id','schedule_date','schedule_time','patient_query','patient_timezone','patient_language','payment_id');

				foreach ($keys_arr as $key) {			
					if(!empty($value[$key]))
					{
						$data[$key] = $value[$key];
					}else{
						$data[$key] = "";
					}
				}

				if (!empty($value['schedule_date'])) {
					$arr = explode("T", $value['schedule_date']);
					if (!empty($arr[0])) {
					    $data['schedule_date_only'] = $arr[0];
					}
				}

				$timestamp = strtotime($value['schedule_time']) + 60*60;
				$data['schedule_end_time'] = date("G:i" , $timestamp);
				$data['schedule_created_on'] = date("Y-m-d");
				$data['schedule_status'] = 0;
				
				$patient_data = $this->Common_model->insert('patient_schedule',$data);
				if($patient_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => ADD_MSG, "id" => $patient_data);
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



	public function getPatientId_Bytoken($token)
	{
		$patient_data = $this->Patient_model->getPatientId_Bytoken($token);
		if($patient_data == B)
		{
			$data = array("status" => A,"message" => NOT_EXIST_MSG);
			echo json_encode($data);
			die;
		}
		else if($patient_data == M)
		{
			$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
			echo json_encode($data);
			die;
		}
		else
		{
			echo json_encode($patient_data);
			die;
		}	
	}


	public function check_authorization($token,$type="admin")
	{
		$patient_data = $this->Patient_model->check_authorization($token,$type);
		if($patient_data == B)
		{
			$data = array("status" => A,"message" => NOT_EXIST_MSG);
			echo json_encode($data);
			die;
		}
		else if($patient_data == M)
		{
			$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
			echo json_encode($data);
			die;
		}
		else if($patient_data != L)
		{
			$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
			echo json_encode($data);
			die;
		}	
	}

	public function check_email_exist($email="")
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);

				if($Authorization[1] != "TechAdmin911"){
					$this->check_authorization($Authorization[1]);	
				}
				if(!empty($email)){
					$patient_data = $this->Patient_model->check_email_exist($email);
				}else{
					$input = @file_get_contents("php://input");
					$value = json_decode($input, true);
					$patient_data = $this->Patient_model->check_email_exist($value['email']);
				}			
				if($patient_data == B)
				{
					$data = array("status" => A,"message" => NOT_EXIST_MSG);
					echo json_encode($data);
					die;
				}
				else if($patient_data == J)
				{
					$data = array("status" => J);
					echo json_encode($data);
					die;
				}
				else
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


	public function get_refund_payment_schedules()
	{
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

				if(!empty($value['patient_id'])){
					$patient_data = $this->Patient_model->get_refund_payment_schedules($value['patient_id']);
				
					if($patient_data == B)
					{
						$data = array("status" => A,"message" => NOT_EXIST_MSG);
						echo json_encode($data);
						die;
					}
					else 
					{
						$data = array("status" => I, "data" => $patient_data);
						echo json_encode($data);
						die;
					}
				}else{
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
	

	public function patient_login()
	{
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
				$patient_data = $this->Patient_model->patient_login($value['username'],$value['password']);
				if($patient_data == B)
				{
					$data = array("status" => A,"message" => WRONG_CREDENTIAL_MSG);
					echo json_encode($data);
					die;
				}
				else if(!empty($patient_data))
				{
					$data = array("status" => I,"message" => LOGIN_MSG, "data" => $patient_data[0]);
					echo json_encode($data);
					die;
				}else{				
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

	public function update_patient_info()
	{
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
				$data = array();

				if(!empty($value['image']['value']))
				{
					file_put_contents("uploads/patient/patient_".$value['patient_id']."_".$value['image']['filename'], base64_decode($value['image']['value']));
					$data['patient_profile_pic'] = base_url()."uploads/patient/patient_".$value['patient_id']."_".$value['image']['filename'];
				}	

				$keys_arr = array('patient_fname','patient_lname','patient_display_name','patient_uname','patient_email','patient_address','patient_city','patient_state','patient_country','patient_contact_no','patient_account_info','patient_subscription_id','patient_payment_id','patient_status','patient_age','patient_height','patient_weight','patient_gender','patient_zip','patient_country_code');

				foreach ($keys_arr as $key) {			
					if(!empty($value[$key]))
					{
						$data[$key] = $value[$key];
					}else{
						$data[$key] = "";
					}
				}

				if(!empty($value['patient_password'])){				
					$data['patient_password'] = md5($value['patient_password']);
				}
				$data['patient_updated_on'] = date("Y-m-d h:i:s");
				$patient_data = $this->Patient_model->update_patient($value['patient_id'],$data);
				if($patient_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => UPDATE_MSG, "data" => $patient_data[0]);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
				echo json_encode($data);
				die;
			}
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}	

	public function update_patient_medicle_info()
	{
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
				$data = array();

				$keys_arr = array('blood_group','patient_height','patient_weight','born_disease','suger','blood_pressure');

				foreach ($keys_arr as $key) {			
					if(!empty($value[$key]))
					{
						$data[$key] = $value[$key];
					}else{
						$data[$key] = "";
					}
				}

				$patient_data = $this->Patient_model->update_patient($value['patient_id'], $data);
				if($patient_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => UPDATE_MSG, "data" => $patient_data[0]);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
				echo json_encode($data);
				die;
			}
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}	


	public function change_patient_password()
	{
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
				$data = array();

				$data['patient_password'] = md5($value['password']);
				$data['patient_updated_on'] = date("Y-m-d h:i:s");
				
				$patient_data = $this->Patient_model->change_patient_password($value['patient_id'],$value['old_password'],$data);
				if($patient_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else if($patient_data == B)
				{
					$data = array("status" => A,"message" => WRONG_OLD_PASSWORD_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => PASSWORD_UPDATE_MSG);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
				echo json_encode($data);
				die;
			}
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}	


	public function email_forgot_password()
	{
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
				$value['email'] = $value['email'];
				$patient_data = $this->Patient_model->email_forgot_password($value['email'],$value['url']);
				if($patient_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else if($patient_data == B)
				{
					$data = array("status" => A,"message" => EMAIL_NOT_EXIST_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => FORGOT_EMAIL_SENT_MSG);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
				echo json_encode($data);
				die;
			}
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function check_forgot_password_link()
	{
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
				if(!empty($value['confirm_code']))
				{
					$value['confirm_code'] = base64_decode($value['confirm_code']);
					$data = explode("|&|", $value['confirm_code']);
					if(!empty($value['confirm_code'])){
						$patient_data = $this->Patient_model->check_forgot_password_link($data[0],$data[1]);
						if($patient_data == B)
						{
							$data = array("status" => A,"message" => EMAIL_NOT_EXIST_MSG);
							echo json_encode($data);
							die;
						}else{
							$data = array("status" => I);
							echo json_encode($data);
							die;
						}
					}
				}	
			}else{
				$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
				echo json_encode($data);
				die;
			}		
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function reset_forgot_password()
	{
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
				if(!empty($value['confirm_code']))
				{
					$value['confirm_code'] = base64_decode($value['confirm_code']);
					$data = explode("|&|", $value['confirm_code']);
					if(!empty($value['confirm_code'])){
						$patient_data = $this->Patient_model->reset_forgot_password($data[0],$data[1],$value['password']);
						if($patient_data == B)
						{
							$data = array("status" => A,"message" => WRONG_FORGOT_PASS_LINK_MSG);
							echo json_encode($data);
							die;
						}else if($patient_data == D)
						{
							$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
							echo json_encode($data);
							die;
						}else{
							$data = array("status" => I,"message" => PASSWORD_UPDATE_MSG);
							echo json_encode($data);
							die;
						}
					}
				}
			}else{
				$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
				echo json_encode($data);
				die;
			}				
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function getPatientInfo_Bytoken()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);
				$patient_data = $this->Patient_model->getPatientInfo_Bytoken($Authorization[1]);
				if($patient_data == B)
				{
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {					
					foreach ($patient_data as $key => $value) {						
						if(!empty($value->patient_city)){
							$name = $this->Common_model->get_field_value_where("cities","name","id",$value->patient_city);
							$patient_data[$key]->city_name = $name[0]->name;
						}	

						if(!empty($value->patient_state)){
							$name = $this->Common_model->get_field_value_where("states","name","id",$value->patient_state);
							$patient_data[$key]->state_name = $name[0]->name; 

						}	

						if(!empty($value->patient_country)){
							$name = $this->Common_model->get_field_value_where("countries","name","id",$value->patient_country);
							$patient_data[$key]->country_name = $name[0]->name; 
						}	
						continue;				
					}

					$data = array("status" => I, "data" => $patient_data[0]);
					echo json_encode($data);
					die;
				}		
			}else{
				$data = array("status" => A,"message" => TOKEN_NOT_AVAILABLE_MSG);
				echo json_encode($data);
				die;
			}			
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}	

	public function patient_logout()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);
				$patient = $this->Patient_model->patient_logout($Authorization[1]);
				if($patient == I)
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
}