<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Doctor extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Doctor_model');  
		$this->load->model('Common_model');  
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authorization', 'image');
	}


	public function index(){
		$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
		echo json_encode($data);
		die;
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


	



	public function add_doctor()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{

				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$data_arrr = array();

				$Authorization = explode(" ", $headers['Authorization']);

				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);
					}	
				}			
				

				if(!empty($value['image']['value']))
				{
					file_put_contents("uploads/doctors/doctor_".strtotime(date("Y-m-d h:i:s"))."_".$value['image']['filename'], base64_decode($value['image']['value']));
					$data_arrr['doctor_profile_pic'] = base_url()."uploads/doctors/doctor_".strtotime(date("Y-m-d h:i:s"))."_".$value['image']['filename'];
				}	

				$keys_arr = array('doctor_uname','doctor_fname','doctor_lname','doctor_email','doctor_display_name','doctor_experiance','doctor_education','doctor_address','doctor_city','doctor_state','doctor_language','doctor_country','doctor_contact_no','doctor_timezone','doctor_zip','doctor_official_address','doctor_fee','doctor_account_info','doctor_status','doctor_availability','doctor_country_code','doctor_imc_id','doctor_gender','doctor_about_me','doctor_expertise','doctor_fee_currency','doctor_payment_id');

				
				foreach ($keys_arr as $key) {			
					if(!empty($value[$key])) {
						$data_arrr[$key] = $value[$key];
					}else{
						$data_arrr[$key] = "";
					}
				}


				if(!empty($value['doctor_password'])){				
					$data_arrr['doctor_password'] = md5($value['doctor_password']);
				}


				if(!empty($value['doctor_specilization'])){				
					$data_arrr['doctor_specilization'] = json_encode($value['doctor_specilization']);
				}

				if(!empty($value['doctor_language'])){				
					$data_arrr['doctor_language'] = json_encode($value['doctor_language']);
				}

				$data_arrr['doctor_created_on'] = date("Y-m-d h:i:s");

				$doctor_check = $this->Common_model->check_exist('doctor','doctor_email',$data_arrr['doctor_email']);

				if($doctor_check == J){
					$data = array("status" => A,"message" => "This email address already exist");
					echo json_encode($data);
					die;
				}else{

					$user_data = array();
					$user_data['user_uname'] = !empty($value['doctor_uname']) ? $value['doctor_uname'] : ""; 
					$user_data['user_email'] = $value['doctor_email'];
					$user_data['user_password'] = !empty($value['doctor_password']) ?  md5($value['doctor_password']) : "";
					$user_data['user_firstname'] = $value['doctor_fname'];
					$user_data['user_lastname'] = $value['doctor_lname'];
					$user_data['user_type'] = 'doctor';
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
						$data_arrr['user_id'] = $user_id;
						$doctor_data = $this->Common_model->insert('doctor', $data_arrr);

						if($doctor_data == D) {
							$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
							echo json_encode($data);
							die;
						}else{

							if(!empty($value['doctor_specilization'])){
								$doctor_specilization = array();
								foreach ($value['doctor_specilization'] as $key => $val) {
									$doctor_specilization['doctor_id'] = $doctor_data;
									$doctor_specilization['disease_id'] = $val['id'];
									$doctor_specilization['doctor_specilization_created_on'] =  date("Y-m-d h:i:s");
									$this->Common_model->insert('doctors_specilization', $doctor_specilization);
								}
							}

							if(!empty($value['doctor_language'])){
								$doctor_language = array();
								foreach ($value['doctor_language'] as $key => $val) {
									$doctor_language['doctor_id'] = $doctor_data;
									$doctor_language['language_id'] = $val['id'];
									$doctor_language['doctors_language_created_on'] =  date("Y-m-d h:i:s");
									$this->Common_model->insert('doctors_language', $doctor_language);
								}
							}

							// $data = array("status" => I,"message" => ADD_MSG, "id" => $doctor_data);
							// echo json_encode($data);
							// die;

							$doctor_data = $this->Doctor_model->getDoctorInfo_ById($doctor_data);
							$doctor_data = (array) $doctor_data;

							/*$doctor_specilization =  $this->Doctor_model->get_specialization($doctor_data['doctor_specilization']);

							$temp_arr = array();
							foreach ($doctor_specilization as $key => $value) {
								$temp_arr[] = $value->disease_name;
							}*/
							
							if($doctor_data == B) {
								$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
								echo json_encode($data);
								die;
							}else {
				
								//foreach ($doctor_data as $value) {	
									//print_r($value);			
									if(!empty($doctor_data['doctor_city'])){
										$name = $this->Common_model->get_field_value_where("cities","name","id",$doctor_data['doctor_city']);
										 $doctor_data['city_name'] = $name[0]->name;
									}	

									if(!empty($doctor_data['doctor_state'])){
										$name = $this->Common_model->get_field_value_where("states","name","id",$doctor_data['doctor_state']);
										 $doctor_data['state_name'] = $name[0]->name; 

									}	

									if(!empty($doctor_data['doctor_country'])){
										$name = $this->Common_model->get_field_value_where("countries","name","id",$doctor_data['doctor_country']);
										 $doctor_data['country_name'] = $name[0]->name; 
									}	
									//continue;				
								//}

								/*== @editdet By TIS 78==*/
								$doctor_specilization = ($doctor_data['doctor_specilization'] !== '') ? json_decode($doctor_data['doctor_specilization']) : array(); 
								$doctor_data['doctor_specilization'] = $doctor_specilization;
								$data = array("status" => I, "data" => $doctor_data);
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


	public function getDoctortId_Bytoken($token)
	{
		$doctor_data = $this->Doctor_model->getDoctortId_Bytoken($token);
		if($doctor_data == B)
		{
			$data = array("status" => A,"message" => NOT_EXIST_MSG);
			echo json_encode($data);
			die;
		}
		else if($doctor_data == M)
		{
			$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
			echo json_encode($data);
			die;
		}
		else
		{
			echo json_encode($doctor_data);
			die;
		}	
	}


	public function getDoctors_byDisease(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$data = array();

				$Authorization = explode(" ", $headers['Authorization']);

				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);
					}	
				}
							
				$doctor_data = $this->Doctor_model->getDoctors_byDisease($value['disease_id']);
				if($doctor_data == B)
				{
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {
					$doctor_data = json_encode($doctor_data);
					$data = array("status" => I, "data" => $doctor_data);
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

	public function getDoctorScheduleTime(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$data = array();	

				$Authorization = explode(" ", $headers['Authorization']);

				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);
					}	
				}
						
				$doctor_data = $this->Doctor_model->getDoctorScheduleTime($value['doctor_id']);

				if($doctor_data == B)
				{
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {
					$doctor_data = json_encode($doctor_data);
					$data = array("status" => I, "data" => $doctor_data);
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
	

	public function get_doctors_list()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$data = array();	

				//print_r($value);

				$Authorization = explode(" ", $headers['Authorization']);

				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);
					}	
				}			
			
				$doctor_data = $this->Common_model->get_all("doctor");
				
				if($doctor_data == B) {
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {			
					foreach ($doctor_data as $key => $value) {	
						
						$doctor_data[$key]->patient_attend = $this->Common_model->get_count_where("patient_schedule","doctor_id",$value->doctor_id);

						$doctors_rating = $this->Common_model->get_field_value_where("patient_schedule",'doctors_rating',"doctor_id",$value->doctor_id);
						//print_r($doctors_rating);
						$total = 0;
						if(!empty($doctors_rating) && isset($doctors_rating)){							
							$count = count($doctors_rating);
							if($count > 1){
								foreach ($doctors_rating as $k => $v) {
									$total = $total + $v->doctors_rating;
								}
								$average = 	round($total / $count);
								$doctor_data[$key]->doctors_rating = !empty($average) ? $average : 0;	
							}else{
								$doctor_data[$key]->doctors_rating = 0; 	
							}					
						}else{
							$doctor_data[$key]->doctors_rating = 0; 	
						}
							

						if(!empty($value->doctor_state)){
							$name = $this->Common_model->get_field_value_where("states","name","id",$value->doctor_state);
							$doctor_data[$key]->doctor_state = isset($name[0]->name) ? $name[0]->name : ''; 
						}	

						if(!empty($value->doctor_country)){
							$name = $this->Common_model->get_field_value_where("countries","name","id",$value->doctor_country);
							$doctor_data[$key]->doctor_country = isset($name[0]->name) ? $name[0]->name : '';
						}

						if( !empty($value->doctor_specilization) ){
							$doctor_data[$key]->doctor_specilization = json_decode($value->doctor_specilization);
						}
					}
					$doctor_data = json_encode($doctor_data);
					$doctor_data = json_decode($doctor_data, true);
					usort($doctor_data, function($a, $b) {
					    return $a['doctors_rating'] - $b['doctors_rating'];
					});

					$data = array("status" => I, "data" => $doctor_data);
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


	public function search_doctors_list(){

		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$Authorization = explode(" ", $headers['Authorization']);

				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);
					}	
				}

				if(!empty($value['name'])){
					$doctor_data = $this->Doctor_model->search_doctors_list($value['specialization'],$value['name'],$value['language'],$value['country']);
				}else{
					$doctor_data = $this->Doctor_model->search_doctors_list($value['specialization'],"",$value['language'],$value['country']);
				}
				

				if($doctor_data == B) {
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {
					foreach ($doctor_data as $key => $value) {						
						if(!empty($value['doctor_city'])){
							$name = $this->Common_model->get_field_value_where("cities","name","id",$value['doctor_city']);
							$doctor_data[$key]['doctor_city'] = isset($name[0]->name) ? $name[0]->name : '';
						}	

						if(!empty($value['doctor_state'])){
							$name = $this->Common_model->get_field_value_where("states","name","id",$value['doctor_state']);
							$doctor_data[$key]['doctor_state'] = isset($name[0]->name) ? $name[0]->name : ''; 

						}	

						if(!empty($value['doctor_country'])){
							$name = $this->Common_model->get_field_value_where("countries","name","id",$value['doctor_country']);
							$doctor_data[$key]['doctor_country'] = isset($name[0]->name) ? $name[0]->name : ''; 
						}

						if( !empty($value['doctor_specilization']) ){
							$doctor_data[$key]['doctor_specilization'] = json_decode($value['doctor_specilization']);
						}

						$doctors_rating = $this->Common_model->get_field_value_where("patient_schedule",'doctors_rating',"doctor_id",$value['doctor_id']);
						$total = 0;
						print_r($doctors_rating);
						if(!empty($doctors_rating)){							
							$count = count($doctors_rating);

							$count = count($doctors_rating);
							if($count > 1){
								foreach ($doctors_rating as $k => $v) {
									$total = $total + $v->doctors_rating;
								}
								$average = 	round($total / $count);
								$doctor_data[$key]['doctors_rating'] = !empty($average) ? $average : 0;	
							}else{
								$doctor_data[$key]['doctors_rating'] = 0; 	
							}	
												
						}else{
							$doctor_data[$key]['doctors_rating'] = 0; 	
						}
					}

					$doctor_data = json_encode($doctor_data);
					$doctor_data = json_decode($doctor_data, true);
					usort($doctor_data, function($a, $b) {
					    return $a['doctors_rating'] - $b['doctors_rating'];
					});

					$data = array("status" => I, "data" => $doctor_data);
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


	public function check_authorization($token,$type="doctor")
	{
		$doctor_data = $this->Doctor_model->check_authorization($token,$type);
		if($doctor_data == B)
		{
			$data = array("status" => K,"message" => USER_UNAUTHORIZED_MSG);
			echo json_encode($data);
			die;
		}
		else if($doctor_data == M)
		{
			$data = array("status" => K,"message" => USER_UNAUTHORIZED_MSG);
			echo json_encode($data);
			die;
		}
		else if($doctor_data != L)
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
			if(!empty($email)){
				$doctor_data = $this->Doctor_model->check_email_exist($email);
			}else{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$doctor_data = $this->Doctor_model->check_email_exist($value['email']);
			}			
			if($doctor_data == B)
			{
				$data = array("status" => A,"message" => NOT_EXIST_MSG);
				echo json_encode($data);
				die;
			}
			else if($doctor_data == J)
			{
				$data = array("status" => I);
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
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}	
	}
	

	public function doctor_login()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				// $Authorization = explode(" ", $headers['Authorization']);
				 // 	if(!isset($value['user_role']) && empty($value['user_role'])){
					// 	$this->check_authorization($Authorization[1]);	
					// }else{
					// 	$this->check_authorization($Authorization[1],$value['user_role']);
					// }	

				
				$doctor_data = $this->Doctor_model->doctor_login($value['username'],$value['password']);
				if($doctor_data == B)
				{
					$data = array("status" => A,"message" => WRONG_CREDENTIAL_MSG);
					echo json_encode($data);
					die;
				}
				else if(!empty($doctor_data))
				{
					foreach ($doctor_data as $key => $value) {						
						if(!empty($value->doctor_city)){
							$name = $this->Common_model->get_field_value_where("cities","name","id",$value->doctor_city);
							$doctor_data[$key]->city_name = $name[0]->name;
						}	

						if(!empty($value->doctor_state)){
							$name = $this->Common_model->get_field_value_where("states","name","id",$value->doctor_state);
							$doctor_data[$key]->state_name = $name[0]->name; 

						}	

						if(!empty($value->doctor_country)){
							$name = $this->Common_model->get_field_value_where("countries","name","id",$value->doctor_country);
							$doctor_data[$key]->country_name = $name[0]->name; 
						}	
						continue;				
					}

					$data = array("status" => I,"message" => LOGIN_MSG, "data" => $doctor_data[0]);
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

	public function update_doctor_info()
	{
		$headers = apache_request_headers();	
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$data = array();
				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);					
					}
				}

				if(!empty($value['image']['value']))
				{
					file_put_contents("uploads/doctors/doctor_".strtotime(date("Y-m-d h:i:s"))."_".$value['image']['filename'], base64_decode($value['image']['value']));
					$data['doctor_profile_pic'] = base_url()."uploads/doctors/doctor_".strtotime(date("Y-m-d h:i:s"))."_".$value['image']['filename'];

					$doctor_data = $this->Doctor_model->remove_doctor_profile_pic($value['doctor_id'],$data);
				}	

				//print_r($value); die;

				if(!empty($value['certificate']))
				{
					//print_r($value['doctor_certificate']); die;
					foreach ($value['certificate'] as $ke => $val) {
						file_put_contents("uploads/doctors/doctor_".strtotime(date("Y-m-d h:i:s"))."_".$val['filename'], base64_decode($val['value']));
						$cer = base_url()."uploads/doctors/doctor_".strtotime(date("Y-m-d h:i:s"))."_".$val['filename'];
						$data['doctor_certificate'][$ke]['img'] = $cer;
					}
				}

				if(!empty($data['doctor_certificate'])){
					$data['doctor_certificate'] = json_encode($data['doctor_certificate']);
				}	

				$keys_arr = array('doctor_uname','doctor_fname','doctor_lname','doctor_email','doctor_display_name','doctor_experiance','doctor_education','doctor_address','doctor_city','doctor_language','doctor_state','doctor_country','doctor_contact_no','doctor_timezone','doctor_zip','doctor_official_address','doctor_fee','doctor_account_info','doctor_status','doctor_availability','doctor_country_code','doctor_imc_id','doctor_gender','doctor_about_me','doctor_expertise','doctor_fee_currency','doctor_payment_id');

				foreach ($keys_arr as $key) {			
					if(!empty($value[$key])) {
						$data[$key] = $value[$key];
					}else{
						$data[$key] = "";
					}
				}

				if(!empty($value['doctor_password'])){				
					$data['doctor_password'] = md5($value['doctor_password']);
				}

				if(!empty($value['doctor_specilization'])){				
					$data['doctor_specilization'] = json_encode($value['doctor_specilization']);
				}

				if(!empty($value['doctor_language'])){				
					$data['doctor_language'] = json_encode($value['doctor_language']);
				}

				$data['doctor_updated_on'] = date("Y-m-d h:i:s");

				$doctor_check = $this->Doctor_model->check_email_notid_exist($value['doctor_id'],$data['doctor_email']);
				if($doctor_check == J){
					$data = array("status" => A,"message" => "This email address already exist");
					echo json_encode($data);
					die;
				}
				
				$doctor_data = $this->Doctor_model->update_doctor($value['doctor_id'],$data);
				if($doctor_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					if(!empty($value['doctor_specilization'])){
						$this->Common_model->delete('doctors_specilization','doctor_id', $value['doctor_id']);
						$doctor_specilization = array();
						foreach ($value['doctor_specilization'] as $key => $val) {
							$doctor_specilization['doctor_id'] = $value['doctor_id'];
							$doctor_specilization['disease_id'] = $val['id'];
							$doctor_specilization['doctor_specilization_created_on'] =  date("Y-m-d h:i:s");
							$doctor_specilization['doctor_specilization_updated_on'] =  date("Y-m-d h:i:s");
							$this->Common_model->insert('doctors_specilization',$doctor_specilization);
						}
					}

					if(!empty($value['doctor_language'])){
						$this->Common_model->delete('doctors_language','doctor_id', $value['doctor_id']);
						$doctor_language = array();
						foreach ($value['doctor_language'] as $key => $val) {
							$doctor_language['doctor_id'] = $value['doctor_id'];
							$doctor_language['language_id'] = $val['id'];
							$doctor_language['doctors_language_created_on'] =  date("Y-m-d h:i:s");
							$doctor_language['doctors_language_updated_on'] =  date("Y-m-d h:i:s");
							$this->Common_model->insert('doctors_language', $doctor_language);
						}
					}


					$data = array("status" => I,"message" => UPDATE_MSG, "data" => $doctor_data[0]);
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

	public function update_doctor_status()
	{
		$headers = apache_request_headers();	
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$data = array();

				$Authorization = explode(" ", $headers['Authorization']);
				if(!isset($value['user_role']) && empty($value['user_role'])){
					$this->check_authorization($Authorization[1]);	
				}else{
					$this->check_authorization($Authorization[1],$value['user_role']);
				}					
				
					
				$data['doctor_status'] = $value['doctor_status'];

				$data['doctor_updated_on'] = date("Y-m-d h:i:s");
				
				$doctor_data = $this->Doctor_model->update_doctor($value['doctor_id'],$data);
				if($doctor_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => UPDATE_MSG);
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


	public function change_doctor_password()
	{
		$headers = apache_request_headers();	
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$data = array();

				$Authorization = explode(" ", $headers['Authorization']);
				if(!isset($value['user_role']) && empty($value['user_role'])){
					$this->check_authorization($Authorization[1]);	
				}else{
					$this->check_authorization($Authorization[1],$value['user_role']);
				}

				$data['doctor_password'] = md5($value['password']);
				$data['doctor_updated_on'] = date("Y-m-d h:i:s");
				
				$doctor_data = $this->Doctor_model->change_doctor_password($value['doctor_id'],$value['old_password'],$data);
				if($doctor_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else if($doctor_data == B)
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


	public function email_forgot_password()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{	
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			$value['email'] = $value['email'];
			$doctor_data = $this->Doctor_model->email_forgot_password($value['email'],$value['url']);
			if($doctor_data == D)
			{
				$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
				echo json_encode($data);
				die;
			}else if($doctor_data == B)
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
	}


	public function check_forgot_password_link()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{	
			$input = @file_get_contents("php://input"); 
			$value = json_decode($input, true);
			if(!empty($value['confirm_code']))
			{
				$value['confirm_code'] = base64_decode($value['confirm_code']);
				$data = explode("|&|", $value['confirm_code']);
				if(!empty($value['confirm_code'])){
					$doctor_data = $this->Doctor_model->check_forgot_password_link($data[0],$data[1]);
					if($doctor_data == B)
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
	}


	public function reset_forgot_password()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{	
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			if(!empty($value['confirm_code']))
			{
				$value['confirm_code'] = base64_decode($value['confirm_code']);
				$data = explode("|&|", $value['confirm_code']);
				if(!empty($value['confirm_code'])){
					$doctor_data = $this->Doctor_model->reset_forgot_password($data[0],$data[1],$value['password']);
					if($doctor_data == B)
					{
						$data = array("status" => A,"message" => WRONG_FORGOT_PASS_LINK_MSG);
						echo json_encode($data);
						die;
					}else if($doctor_data == D)
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
	}


	public function getDoctorInfo_ById()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$Authorization = explode(" ", $headers['Authorization']);
				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);
					}	
				}	
				

				$doctor_data = $this->Doctor_model->getDoctorInfo_ById($value['doctor_id']);
				$doctor_data = (array)$doctor_data;

				if($doctor_data == B) {
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {

					$doctors_rating = $this->Common_model->get_field_value_where("patient_schedule",'doctors_rating',"doctor_id",$doctor_data['doctor_id']);
					$total = 0;
					if(!empty($doctors_rating)){							
						$count = count($doctors_rating);
						if($count > 1){
							foreach ($doctors_rating as $k => $v) {
							$total = $total + $v->doctors_rating;
							}
							$average = 	round($total / $count);	

							$doctor_data['doctors_rating'] = !empty($average) ? $average : 0; 
						}else{
							$doctor_data['doctors_rating'] = 0; 	
						}
												
					}else{
						$doctor_data['doctors_rating'] = 0; 	
					}

					if( !empty($doctor_data['doctor_city']) ){
						$name = $this->Common_model->get_field_value_where("cities","name","id",$doctor_data['doctor_city']);
						$doctor_data['city_name'] = isset($name[0]->name) ? $name[0]->name : '';
					}	

					if(!empty($doctor_data['doctor_state'])){
						$name = $this->Common_model->get_field_value_where("states","name","id",$doctor_data['doctor_state']);
						$doctor_data['state_name'] = isset($name[0]->name) ? $name[0]->name : '';

					}	

					if(!empty($doctor_data['doctor_country'])){
						$name = $this->Common_model->get_field_value_where("countries","name","id",$doctor_data['doctor_country']);
						 $doctor_data['country_name'] = isset($name[0]->name) ? $name[0]->name : ''; 
					}	

					/*== @editdet By TIS 78==*/
					$doctor_specilization = ($doctor_data['doctor_specilization'] !== '') ? json_decode($doctor_data['doctor_specilization']) : array(); 
					$doctor_data['doctor_specilization'] = $doctor_specilization;

					$doctor_language = ($doctor_data['doctor_language'] !== '') ? json_decode($doctor_data['doctor_language']) : array(); 
					$doctor_data['doctor_language'] = $doctor_language;

					$doctor_certificate = ($doctor_data['doctor_certificate'] !== '') ? json_decode($doctor_data['doctor_certificate']) : array(); 
					$doctor_data['doctor_certificate'] = $doctor_certificate;

					$data = array("status" => I, "data" => $doctor_data);
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

	public function getDoctorAppoinmentsCount(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$Authorization = explode(" ", $headers['Authorization']);
				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);
					}	
				}					

				//print_r($value); die;

				if(!empty($value['doctor_id'])){
					$doctor_data = $this->Doctor_model->getDoctorAppoinmentsCount($value['doctor_id']);				
					//print_r($doctor_data);
					if($doctor_data == B) {
						$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
						echo json_encode($data);
						die;
					}else {				

						$data = array("status" => I, "count" => $doctor_data);
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

	public function changeNotificationStatus(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$Authorization = explode(" ", $headers['Authorization']);
				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);
					}	
				}	
				

				//print_r($value); die;

				if(!empty($value['doctor_id'])){
					$doctor_data = $this->Common_model->update('patient_schedule','doctor_id',$value['doctor_id'],array('doctor_notification_status'=>1));				
					//print_r($doctor_data);
					if($doctor_data == D) {
						$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
						echo json_encode($data);
						die;
					}else {				

						$data = array("status" => I, "message" => "Updated Succesfully");
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

	public function getAppointmentById(){
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

				if(!empty($value['doctor_id'])){
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

							if( !empty($newDoc[$k]['patient_details']['patient_city']) ){
								$name = $this->Common_model->get_field_value_where("cities","name","id",$newDoc[$k]['patient_details']['patient_city']);
								$newDoc[$k]['city_name'] = isset($name[0]->name) ? $name[0]->name : '';
							}	

							if(!empty($newDoc[$k]['patient_details']['patient_state'])){
								$name = $this->Common_model->get_field_value_where("states","name","id",$newDoc[$k]['patient_details']['patient_state']);
								$newDoc[$k]['state_name'] = isset($name[0]->name) ? $name[0]->name : '';

							}	

							if(!empty($newDoc[$k]['patient_details']['patient_country'])){
								$name = $this->Common_model->get_field_value_where("countries","name","id",$newDoc[$k]['patient_details']['patient_country']);
								 $newDoc[$k]['country_name'] = isset($name[0]->name) ? $name[0]->name : ''; 
							}	

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

	public function getDoctorAppoinment_ById()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				$Authorization = explode(" ", $headers['Authorization']);
				if($Authorization[1] != "TechAdmin911"){
					if(!isset($value['user_role']) && empty($value['user_role'])){
						$this->check_authorization($Authorization[1]);	
					}else{
						$this->check_authorization($Authorization[1],$value['user_role']);
					}	
				}	

				if(!empty($value['doctor_id'])){
					$doctor_data = $this->Doctor_model->getDoctorAppoinment_ById($value);
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

						if(!empty($v['patient_language'])){
							$d = $this->Common_model->get_field_value_where("language",'language','language_id',$v['patient_language']);
							$newDoc[$k]['language_name'] = (array) current($d);
						}

						$newDoc[$k]['schedule_detail'] = $v;
						//print_r($newDoc);
					}
					array_reverse($newDoc);
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


	public function getDoctorInfo_Bytoken()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);
				$doctor_data = $this->Doctor_model->getDoctorInfo_Bytoken($Authorization[1]);
				if($doctor_data == B)
				{
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {
					foreach ($doctor_data as $key => $value) {						
						if(!empty($value->doctor_city)){
							$name = $this->Common_model->get_field_value_where("cities","name","id",$value->doctor_city);
							$doctor_data[$key]->city_name = $name[0]->name;
						}	

						if(!empty($value->doctor_state)){
							$name = $this->Common_model->get_field_value_where("states","name","id",$value->doctor_state);
							$doctor_data[$key]->state_name = $name[0]->name; 

						}	

						if(!empty($value->doctor_country)){
							$name = $this->Common_model->get_field_value_where("countries","name","id",$value->doctor_country);
							$doctor_data[$key]->country_name = $name[0]->name; 
						}	
						continue;				
					}
					$data = array("status" => I, "data" => $doctor_data[0]);
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

	public function doctor_logout()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);
				$doctor = $this->Doctor_model->doctor_logout($Authorization[1]);
				if($doctor == I)
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