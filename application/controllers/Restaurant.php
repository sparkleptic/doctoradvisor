<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Time_schedule extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Time_schedule'); 
		$this->load->model('Common_model');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authorization', 'image');
	}

	public function index()
	{

			echo "gklfdlgdf"; die; 
			$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
			foreach ($days as $key => $val) {
					if($val == 'wednesday'){
						$hours[$val] = true;
					}else{
						$hours[$val] = false;
					}
				
			}

				$value['restro_id'] = 15;
				$hours['open_time'] = "9:15 AM";
				$hours['close_time'] = "9:10 AM";
				$new_data = array('restro_id'=>intval($value['restro_id']),'restro_meta_key'=>"store_hours",'restro_meta_value'=>json_encode($hours));
				//echo json_encode($new_data); die;
				//if(isset($value['restro_id'])){
					echo $restro_id = $this->Restro_model->check_business_hours($new_data);	
					if($restro_id > 0){
						//$this->get_restro_hours_byUser($value['hours_type'],$value['user_id']);
						$data = array("restro_id" => $restro_id);
						echo json_encode($data);
						die;
					}else 
					{
						$data = array("status" => 0);
						echo json_encode($data);
						die;
					}
				//}
				
			
		

		die;
		$data['domain'] = "test";
		if(!is_dir('./home/sketch/public_html/projects/digitalbistro/subdomain/'.$data['domain'])){
			mkdir('./home/sketch/public_html/projects/digitalbistro/subdomain/'.$data['domain'], 0777, TRUE);
			if(file_exists('./home/sketch/public_html/projects/digitalbistro/subdomain/'.$data['domain'])){
				$srcfile='template/index.php';
				$dstfile='home/sketch/public_html/projects/digitalbistro/subdomain/'.$data['domain'].'/index.php';
				copy($srcfile, $dstfile);
			}
		}else{
			echo "kld;kfsd";
		}
		
	    
		die;

		$srcfile='uploads/Joe.txt';
		$dstfile='template/Joe.txt';
		// //mkdir(dirname($dstfile), 0777, true);
		copy($srcfile, $dstfile);

		$file = 'template/index.php';
		$file_contents = file_get_contents($file);

		$fh = fopen($file, "w");
		$file_contents = str_replace(0,5,$file_contents);
		$file_contents = str_replace(0,5,$file_contents);
		fwrite($fh, $file_contents);
		fclose($fh); die;

		$doctor_data = $this->Restro_model->get_restro_byId('37','store_hours');
			if($doctor_data != 0 && is_array($doctor_data))
			{
				$doctor_data = json_encode($doctor_data);
				$doctor_data = json_decode($doctor_data,true);
				// print_r($doctor_data); die;
				// $data = array();
				// $data =  json_decode($doctor_data[0]['restro_meta_value'], true);
				 //print_r($doctor_data); die;
				$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
				$new_data = array();
				$send_data = array();
				$i = 0;
				foreach ($doctor_data as $key => $value) 
				{
					$data[$i] = json_decode($value['restro_meta_value'],true);
				// 	echo "<pre>";
				// print_r($data);
					$new_data['data'] = $value;
					$str = ""; $last_true_day = ""; $j = true; $false_count = 0; $day_loop_count = 0; $true_count = 0;
					foreach ($days as $ke => $v) {
						$day_loop_count++;
						if($data[$i][$v] == true){
							if($j == true){
								if($true_count > 0 && $day_loop_count > 1)
								{
									$str .= ', ';
								}								
								$str .= $v;
								$j = false;
								$last_true_day = "";

								$true_count++;
							}else{
								$last_true_day = $v;
							}
							if(count($days) == $day_loop_count && !empty($last_true_day)){
								$str .= '-'.$last_true_day;
								$last_true_day = "";
							}							
						}else{
							if(!empty($last_true_day)){
								$str .= '-'.$last_true_day;
								$last_true_day = "";
							}				
							$j = true;
										
							$false_count++;
						}						
					}
					echo $str; echo "<br>";
					$str .= ': '.$data[$i]['open_time'].' -'.$data[$i]['close_time']; 
					$data[$i]['time_string'] = $str;
					$data[$i]['restro_id'] = $doctor_data[$key]['restro_id'];
					$data[$i]['restro_meta_id'] = $doctor_data[$key]['restro_meta_id'];
					$data[$i]['hours_type'] = $doctor_data[$key]['restro_meta_key'];
					$data[$i]['open_time'] = strtoupper($doctor_data[$key]['open_time']);
					$data[$i]['close_time'] = strtoupper($doctor_data[$key]['close_time']);
					$i++;
					//array_push($send_data, $data);
				}
				echo "<pre>";
				print_r($data);
				echo json_encode($data);
					die;
			}

			die;

		$options = '<option value="0000">12:00 am</option>';
		$new = explode("</option>", $options);
		$c =  array();


		foreach ($new as $key => $value) {
			$value = explode('">', $value);
			$data['time'] = trim($value[1]);
			$data['value'] = substr($value[0],16,19);
			array_push($c, $data);
		}
//echo "<pre>";
		echo	json_encode($c,true);
//		echo "</pre>";

	}


	function escape_str($str, $like = FALSE)
	{
	    if (is_array($str))
	    {
	        foreach ($str as $key => $val)
	        {
	            $str[$key] = $this->escape_str($val, $like);
	        }

	        return $str;
	    }

	    
	    $str = addslashes($str);

	    // escape LIKE condition wildcards
	    if ($like === TRUE)
	    {
	        $str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
	    }

	    return $str;
	}

	public function get_doctor_availability($id){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			//$Authorization = explode(" ", $headers['authorization']);
			$doctor_data = $this->Restro_model->get_restro_byId($id);
			if($doctor_data != 0 && is_array($doctor_data))
			{
				$doctor_data = json_encode($doctor_data);
				$doctor_data = json_decode($doctor_data,true);
				$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
				$new_data = array();
				$send_data = array();
				$i = 0;
				foreach ($doctor_data as $key => $value) 
				{
					$data[$i] = json_decode($value['doctor_time_schedule'],true);
					$new_data['data'] = $value;
					$str = ""; $last_true_day = ""; $j = true; $false_count = 0; $day_loop_count = 0; $true_count = 0;
					foreach ($days as $ke => $v) {
						$day_loop_count++;
						if($data[$i][$v] == true){
							if($j == true){
								if($true_count > 0 && $day_loop_count > 1)
								{
									$str .= ', ';
								}								
								$str .= ucfirst($v);
								$j = false;
								$last_true_day = "";

								$true_count++;
							}else{
								$last_true_day = ucfirst($v);
							}
							if(count($days) == $day_loop_count && !empty($last_true_day)){
								$str .= '-'.$last_true_day;
								$last_true_day = "";
							}							
						}else{
							if(!empty($last_true_day)){
								$str .= '-'.$last_true_day;
								$last_true_day = "";
							}				
							$j = true;
										
							$false_count++;
						}
					}
					//$str .= ': '.$data[$i]['open_time'].' -'.$data[$i]['close_time']; 
					$data[$i]['time_string'] = $str;
					$data[$i]['doctor_id'] = $doctor_data[$key]['doctor_id'];
					$data[$i]['doctor_time_schedule_id'] = $doctor_data[$key]['doctor_time_schedule_id'];
					$data[$i]['open_time'] = strtoupper($data[$i]['open_time']);
					$data[$i]['close_time'] = strtoupper($data[$i]['close_time']);
					$i++;
					//array_push($send_data, $data);
				}
				if(!empty($data)){
					echo json_encode($data);
					die;
				}else{
					$data['doctor_id'] = $doctor_data;	
					echo json_encode($data);
					die;
				}				
			}elseif ($doctor_data > 0) {
				$data['doctor_id'] = $doctor_data;	
				echo json_encode($data);
				die;
			}
			else{
				$data = array("status" => 0);
				echo json_encode($data);
				die;
			}
		}else{
			$data = array("status" => A,"message" => "Header type is not correct.");
			echo json_encode($data);
			die;
		}				

	}

	public function delete_doctor_time_scheduel($id){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($id))
			{
				$doctor_data = $this->Common_model->delete("doctor_time_schedule","doctor_time_schedule_id",$id);
				if($doctor_data == 0){
					$data = array("status" => I,"message" => "Removed successfully");
					echo json_encode($data);
					die;
				}else{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;	
				}
			}else{
				$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
				echo json_encode($data);
				die;
			}
		}else{
			$data = array("status" => A,"message" => "Header type is not correct.");
			echo json_encode($data);
			die;
		}	
		
	}
	

	public function add_doctor_availability()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			$input = @file_get_contents("php://input");			
			$value = json_decode($input, true);
			// echo json_encode($value);
			// 		die;
			if(isset($value)){
				$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
				foreach ($days as $key => $val) {
					if(!empty($value[$val]))
					{
						$hours[$val] = $value[$val];
					}else{
						$hours[$val] = false;
					}
				}
				if (strtotime($value['open_time']) > strtotime($value['close_time'])) {
					$data = array("status" => "time error", "message" => "Sorry! You can not select close time less than open time.");
					echo json_encode($data);
					die;
				}
				if(isset($value['open_time']) && isset($value['close_time']) && isset($value['doctor_id'])){
					//$value['restro_id'] = $this->Restro_model->get_restroId_byUser($value['doctor_id']);
					if(!empty($value['doctor_id']))
					{
						if (strpos($value['open_time'], 'PM') !== false && strpos($value['close_time'], 'AM') !== false) {
							$data = array("status" => "time error", "message" => "Sorry! You can not select close time after 12 AM.");
							echo json_encode($data);
							die;
						}
						$hours['open_time'] = $value['open_time'];
						$hours['close_time'] = $value['close_time'];
						$new_data = array('doctor_time_schedule'=>$hours);
						//echo json_encode($new_data); die;
						if(isset($value['doctor_id'])){							
							$check_status = $this->Restro_model->check_doctor_availablility($value['doctor_id'],$new_data);
							if($check_status == 1)	{
								$new_data = array('doctor_id'=>$value['doctor_id'],'doctor_time_schedule'=>json_encode($hours));
								$doctor_id = $this->Restro_model->add_doctor_availability($new_data);	
								if($doctor_id > 0){									
									$data = array("status" => I,"message" => "Added successfully","doctor_id" => $doctor_id);
									echo json_encode($data);
									die;
								}else 
								{
									$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
									echo json_encode($data);
									die;
								}
							}else{
								$data = array("status" => A, "message" => $check_status);
								echo json_encode($data);
								die;
							}							
						}
					}
					else{
						$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
						echo json_encode($data);
						die;
					}
				}else{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}
			}else{
				$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
				echo json_encode($data);
				die;
			}
		}else{
			$data = array("status" => A,"message" => "Header type is not correct.");
			echo json_encode($data);
			die;
		}	
				
	}	


	public function update_restro_business_hours()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(isset($headers['Authorization'])){
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				if(isset($value['doctor_id']) && $value['doctor_id'] != "")
				{
					//$check_status = $this->Restro_model->check_business_hours($new_data);
					if (strpos($value['open_time'], 'PM') !== false && strpos($value['close_time'], 'AM') !== false) {
						$data = array("status" => "time error", "msg" => "Sorry! You can not select close time after 12 AM.");
						echo json_encode($data);
						die;
					}

					if (strtotime($value['open_time']) > strtotime($value['close_time'])) {
						$data = array("status" => "time error", "msg" => "Sorry! You can not select close time less than open time.");
						echo json_encode($data);
						die;
					}

					
					$restro_id = $this->Restro_model->update_doctor_availablility($value); 
					if($restro_id < 1){
						$data = array("status" => "time error", "msg" => $restro_id);
						echo json_encode($data);
						die;
					}
					else if($restro_id > 0){
						//return $this->get_restro_hours_byUser($value['hours_type'],$value['user_id']);
						$data = array("restro_id" => $restro_id);
						echo json_encode($data);
						die;
					}else 
					{
						$data = array("status" => 0);
						echo json_encode($data);
						die;
					}		
				}else 
				{
					$data = array("status" => 0);
					echo json_encode($data);
					die;
				}	
			}
		}else{
			$data = array("status" => A,"message" => "Header type is not correct.");
			echo json_encode($data);
			die;
		}							
	}
}	