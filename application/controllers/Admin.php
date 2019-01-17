<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

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
		$data = array("status" => E,"message" => HEADER_TYPE_ERROR_MSG);
		echo json_encode($data);
		die;
	}


	public function add_disease()
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

				$keys_arr = array('disease_name','disease_description');

				foreach ($keys_arr as $key) {			
					if(!empty($value[$key]))
					{
						$data[$key] = $value[$key];
					}else{
						$data[$key] = "";
					}
				}

				
				$data['disease_created_on'] = date("Y-m-d h:i:s");
				
				$disease_data = $this->Common_model->insert('disease',$data);
				if($disease_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => ADD_MSG, "id" => $disease_data);
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

	public function get_diseaseby_id()
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

				$disease_data = $this->Common_model->get_where_in('disease', 'disease_id', $value);		
				$disease_data = current($disease_data);

				if(empty($disease_data)) {
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => ADD_MSG, "disease_data" => $disease_data);
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


	public function get_symptomsby_id()
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
			

				$url = 'https://api.infermedica.com/v2/symptoms/'.$value['id'];

				$ch = curl_init(); 
		        curl_setopt($ch, CURLOPT_URL, $url); 
		        curl_setopt($ch, CURLOPT_HEADER, false); 
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
		        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","app_id: fbba825f", "app_key: 40e55f37855ecf432808f8a807db90b4")); 
		        $symptoms = curl_exec($ch); 
		        curl_close ($ch);
		        $symptoms_res = json_decode($symptoms,TRUE);

				if(empty($symptoms_res)) {
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I, "disease_data" => $symptoms_res);
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


	public function update_disease()
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

				$keys_arr = array('disease_name','disease_description');

				foreach ($keys_arr as $key) {			
					if(!empty($value[$key]))
					{
						$data[$key] = $value[$key];
					}else{
						$data[$key] = "";
					}
				}

				
				$data['disease_updated_on'] = date("Y-m-d h:i:s");
				
				$disease_data = $this->Common_model->update('disease','disease_id',$value['disease_id'],$data);

				if($disease_data == D)
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


	public function get_disease_list()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);

				if($Authorization[1] != "TechAdmin911"){
					//$this->check_authorization($Authorization[1]);	
				}
				
				$disease_data = $this->Common_model->get_all('disease');

				$disease_data = json_encode($disease_data);
				$disease_data = json_decode($disease_data,true);
				array_walk($disease_data, function (& $item) {
				   $item['id'] = $item['disease_id'];
				   unset($item['disease_id']);
				    $item['itemName'] = $item['disease_name'];
				   unset($item['disease_name']);
				});

				if($disease_data == D)
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => I,"message" => ADD_MSG, "data" => $disease_data);
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

	public function get_symptoms_list()
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
				
				$url = 'https://api.infermedica.com/v2/symptoms';

				$ch = curl_init(); 
		        curl_setopt($ch, CURLOPT_URL, $url); 
		        curl_setopt($ch, CURLOPT_HEADER, false); 
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
		        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","app_id: fbba825f", "app_key: 40e55f37855ecf432808f8a807db90b4")); 
		        $symptoms = curl_exec($ch); 
		        curl_close ($ch);
		        $symptoms_res = json_decode($symptoms,TRUE);
				array_walk($symptoms_res, function (& $item) {
				   $item['id'] = $item['id'];
				   //unset($item['id']);
				    $item['itemName'] = $item['name'];
				   unset($item['name']);
				});

				if(!empty($symptoms_res))
				{
					$data = array("status" => I, "data" => $symptoms_res);
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


	public function getAdminId_Bytoken($token)
	{
		$admin_data = $this->Admin_model->getAdminId_Bytoken($token);
		if($admin_data == B)
		{
			$data = array("status" => A,"message" => NOT_EXIST_MSG);
			echo json_encode($data);
			die;
		}
		else if($admin_data == M)
		{
			$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
			echo json_encode($data);
			die;
		}
		else
		{
			echo json_encode($admin_data);
			die;
		}	
	}





	public function check_authorization($token)
	{
		$admin_data = $this->Admin_model->check_authorization($token);
		if($admin_data == B)
		{
			$data = array("status" => A,"message" => NOT_EXIST_MSG);
			echo json_encode($data);
			die;
		}
		else if($admin_data == M)
		{
			$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
			echo json_encode($data);
			die;
		}
		else if($admin_data != L)
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
				$admin_data = $this->Admin_model->check_email_exist($email);
			}else{
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				$admin_data = $this->Admin_model->check_email_exist($value['email']);
			}			
			if($admin_data == B)
			{
				$data = array("status" => A,"message" => NOT_EXIST_MSG);
				echo json_encode($data);
				die;
			}
			else if($admin_data == J)
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
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}	
	}
	

	public function admin_login()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			$admin_data = $this->Admin_model->admin_login($value['username'],$value['password']);
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

	public function update_admin_info()
	{
		$headers = apache_request_headers();	
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);
				$this->check_authorization($Authorization[1]);	
			}	
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			$data = array();

			if(!empty($value['image']['value']))
			{
				file_put_contents("uploads/admin/owner_".$value['admin_id']."_".$value['image']['filename'], base64_decode($value['image']['value']));
				$data['admin_profile_pic'] = base_url()."uploads/admin/owner_".$value['admin_id']."_".$value['image']['filename'];
			}	

			$keys_arr = array('admin_uname','admin_fname','admin_lname','admin_email');

			foreach ($keys_arr as $key) {			
				if(!empty($value[$key]))
				{
					$data[$key] = $value[$key];
				}else{
					$data[$key] = "";
				}
			}

			$data['admin_updated_on'] = date("Y-m-d h:i:s");
			
			$admin_data = $this->Admin_model->update_admin($value['admin_id'],$data);
			if($admin_data == D)
			{
				$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
				echo json_encode($data);
				die;
			}else{
				$data = array("status" => I,"data" => $admin_data);
				echo json_encode($data);
				die;
			}
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}	


	public function change_admin_password()
	{
		$headers = apache_request_headers();	
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);
				$this->check_authorization($Authorization[1]);	
			}	
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			$data = array();

			$data['admin_password'] = md5($value['password']);
			$data['admin_updated_on'] = date("Y-m-d h:i:s");
			
			$admin_data = $this->Admin_model->change_admin_password($value['admin_id'],$value['old_password'],$data);
			if($admin_data == D)
			{
				$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
				echo json_encode($data);
				die;
			}else if($admin_data == B)
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
	}	


	public function email_forgot_password()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{	
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			$value['email'] = $value['email'];
			$admin_data = $this->Admin_model->email_forgot_password($value['email'],$value['url']);
			if($admin_data == D)
			{
				$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
				echo json_encode($data);
				die;
			}else if($admin_data == B)
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
					$admin_data = $this->Admin_model->check_forgot_password_link($data[0],$data[1]);
					if($admin_data == B)
					{
						$data = array("status" => A,"message" => EMAIL_NOT_EXIST_MSG);
						echo json_encode($data);
						die;
					}else{
						$data = array("status" => J);
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
			if(isset($value['confirm_code']))
			{
				$value['confirm_code'] = base64_decode($value['confirm_code']);
				$data = explode("|&|", $value['confirm_code']);
				if(!empty($value['confirm_code'])){
					$admin_data = $this->Admin_model->reset_forgot_password($data[0],$data[1],$value['password']);
					if($admin_data == B)
					{
						$data = array("status" => A,"message" => WRONG_FORGOT_PASS_LINK_MSG);
						echo json_encode($data);
						die;
					}else if($admin_data == D)
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
			}else{
				$data = array("status" => A,"message" => "URL confirm code is missing ");
				echo json_encode($data);
				die;
			}			
		}else{
			$data = array("status" => A,"message" => HEADER_TYPE_ERROR_MSG);
			echo json_encode($data);
			die;
		}
	}


	public function getAdminInfo_Bytoken()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);
				$admin_data = $this->Admin_model->getAdminInfo_Bytoken($Authorization[1]);
				if($admin_data == B)
				{
					$data = array("status" => A,"message" => USER_UNAUTHORIZED_MSG);
					echo json_encode($data);
					die;
				}else {
					echo json_encode($admin_data);
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

	public function admin_logout()
	{
		$headers = apache_request_headers();
		$headers = getallheaders();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{			
			if(!empty($headers['Authorization']) || !empty($headers['authorization']))
			{
				$Authorization = explode(" ", $headers['Authorization']);
				$admin = $this->Admin_model->admin_logout($Authorization[1]);
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
}