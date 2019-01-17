<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Time_schedule extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Time_schedule_model'); 
		$this->load->model('Common_model');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authorization', 'image');
	}

	public function index()
	{

	   // $result = $this->Time_schedule_model->get_doctors_slots_byDay(57,3); 
		$value['schedule_date'] = "2018-01-31T10:24:49.298Z";
		$value['doctor_id'] = 57;
		$value['week_day'] = 3;
		if(!empty($value['doctor_id']))
			{	
				$today_date = 0;
				$dat = date("Y-m-d");
				$arr = explode("T", $value['schedule_date']);
				
				if ($arr[0] == $dat) {
				     $today_date = 1;
				}

				$result = $this->Time_schedule_model->get_doctors_slots_byDay($value['doctor_id'],$value['week_day']); 
				
				if($result == B){
					$data = array("status" => A, "message" => "Doctors time slots are not available.");
					echo json_encode($data);
					die;
				}
				else {
					$doctor_data = json_encode($result);
					$doctor_data = json_decode($doctor_data, true);
					$timeSlot = array();
					$NewTimeSlot = array();
					$i = 0; $k = 0;
					foreach ($doctor_data as $key => $v) 
					{						
						$doctor_times = json_decode($doctor_data[$key]['doctor_time_schedule'],true);		
						$j=0;
						$newDate = strtotime($doctor_times['open_time']);
						$close_time = strtotime($doctor_times['close_time']);
						while ($newDate <= $close_time) {
							// echo $newDate."<br>";
							// echo strtotime($doctor_times['close_time'])."<br>";
							$timeSlot[$i]['open_time'] = $doctor_times['open_time'];
							$timeSlot[$i]['close_time'] = $doctor_times['close_time'];	
							if($i == 0 && $k == 0 || $j == 0){
								$k = 1; $j = 1;
								$check = $this->Time_schedule_model->chk_timeslot_avaibility($value['doctor_id'],$arr[0],$doctor_times['open_time']);
								if($check == 0){
									if($today_date == 0){
										echo $timeSlot[$i]['slot'] = $doctor_times['open_time'];
										$i++;
										echo $timeSlot[$i]['status'] = 1;
									}
									else if(strtotime($doctor_times['open_time']) >= time()+300){echo $timeSlot[$i]['slot'] = $doctor_times['open_time'];
										$i++;
										echo $timeSlot[$i]['status'] = 1;
									}else{
										echo $timeSlot[$i]['slot'] = $doctor_times['open_time'];
										$i++;										
										echo $timeSlot[$i]['status'] = 0;
									}
								}else{
									echo $timeSlot[$i]['slot'] = $doctor_times['open_time'];
									$i++;
									echo $timeSlot[$i]['status'] = 0;
								}
								
								//echo "<br>";
								
							}else{
								$newDate = strtotime("+30 minutes", $newDate);
								$NewTimeSlot = date('h:i A',$newDate);
								if(strtotime($doctor_times['close_time']) > strtotime($NewTimeSlot)){
									$check = $this->Time_schedule_model->chk_timeslot_avaibility($value['doctor_id'],$arr[0],$NewTimeSlot);
									if($check == 0){
										//echo strtotime($NewTimeSlot); echo "<br>"; echo strtotime(date('h:i A')); echo "<br>";
										//echo strtotime($NewTimeSlot)."*****".time()+300; echo "<br>";
										if($today_date == 0){
											//echo "a<br>";
											$timeSlot[$i]['slot'] = $NewTimeSlot;
											$i++;
											$timeSlot[$i]['status'] = 1;
										}
										else if(strtotime($NewTimeSlot) >= time()+300){	
											//echo "b<br>";
											$timeSlot[$i]['slot'] = $NewTimeSlot;
											$i++;
											$timeSlot[$i]['status'] = 1;
										}else{
											//echo "c<br>";
											$timeSlot[$i]['slot'] = $NewTimeSlot;
											$i++;
											$timeSlot[$i]['status'] = 0;
										}
									}else{
										//echo "d<br>";
										$timeSlot[$i]['slot'] = $NewTimeSlot;
										$i++;
										$timeSlot[$i]['status'] = 0;
									}
								}
								 //echo "<br>";
							}							
						}
					}
					if(!empty($timeSlot)){
						//array_pop($timeSlot);
						$data = array("status" => I, "data" => $timeSlot);
					}else{
						$data = array("status" => A, "data"=>"", "message" => "Doctors time slots are not available.");
					}
					
					echo json_encode($data);
					die;
				}	
			}
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


	public function get_doctor_time_slot($id){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			//$Authorization = explode(" ", $headers['authorization']);
			$doctor_data = $this->Time_schedule_model->get_doctor_availablility_byId($id);
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
					$data[$i]['doctor_time_schedule_status'] = $doctor_data[$key]['doctor_time_schedule_status'];
					$data[$i]['doctor_time_schedule_disable_from'] = $doctor_data[$key]['doctor_time_schedule_disable_from'];
					$data[$i]['doctor_time_schedule_disable_to'] = $doctor_data[$key]['doctor_time_schedule_disable_to'];
					$data[$i]['open_time'] = strtoupper($data[$i]['open_time']);
					$data[$i]['close_time'] = strtoupper($data[$i]['close_time']);
					$i++;
					//array_push($send_data, $data);
				}
				if(!empty($data)){
					array_reverse($data);
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

	public function get_doctor_time_scheduel($id){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			//$Authorization = explode(" ", $headers['authorization']);
			$doctor_data = $this->Time_schedule_model->get_doctor_availablility_byId($id);
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
					$data[$i]['doctor_time_schedule_status'] = $doctor_data[$key]['doctor_time_schedule_status'];
					$data[$i]['doctor_time_schedule_disable_from'] = $doctor_data[$key]['doctor_time_schedule_disable_from'];
					$data[$i]['doctor_time_schedule_disable_to'] = $doctor_data[$key]['doctor_time_schedule_disable_to'];
					$data[$i]['open_time'] = strtoupper($data[$i]['open_time']);
					$data[$i]['close_time'] = strtoupper($data[$i]['close_time']);
					$i++;
					//array_push($send_data, $data);
				}
				if(!empty($data)){
					array_reverse($data);
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

	public function enable_doctor_time_scheduel($id){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($id))
			{
				$doctor_data = $this->Common_model->update("doctor_time_schedule","doctor_time_schedule_id",$id,array("doctor_time_schedule_status"=>1));
				if($doctor_data == H){
					$data = array("status" => I,"message" => "Updated successfully");
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


	public function add_patients_feedback(){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			$input = @file_get_contents("php://input");			
			$value = json_decode($input, true);
			if(!empty($value['schedule_id']))
			{
				$doctor_data = $this->Common_model->update("patient_schedule","patient_schedule_id",$value['schedule_id'],array("doctors_rating"=>$value['rating'],"doctors_review"=>$value['review'],"schedule_updated_on"=>date("Y-m-d h:i:s")));
				if($doctor_data == H){
					$data = array("status" => I,"message" => "Updated successfully");
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
	

	public function add_doctor_time_scheduel()
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
					//$value['restro_id'] = $this->Time_schedule_model->get_restroId_byUser($value['doctor_id']);
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
							$check_status = $this->Time_schedule_model->check_doctor_availablility($value['doctor_id'],$new_data);
							if($check_status == 1)	{
								$new_data = array('doctor_time_schedule_status'=>1,'doctor_id'=>$value['doctor_id'],'doctor_time_schedule'=>json_encode($hours));
								$doctor_id = $this->Time_schedule_model->add_doctor_availability($new_data);	
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


	public function update_doctor_time_scheduel()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(isset($headers['Authorization'])){
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);
				if(isset($value['doctor_id']) && $value['doctor_id'] != "")
				{
					//$check_status = $this->Time_schedule_model->check_business_hours($new_data);
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

					
					$doctor_id = $this->Time_schedule_model->update_doctor_availablility($value); 
					if($doctor_id < 1){
						$data = array("status" => "time error", "message" => COMMON_ERROR_OCCURED_MSG);
						echo json_encode($data);
						die;
					}
					else if($doctor_id > 0){
						//return $this->get_restro_hours_byUser($value['hours_type'],$value['user_id']);
						$data = array("status" => I, "message" => "Updated successfully", "doctor_id" => $doctor_id);
						echo json_encode($data);
						die;
					}else 
					{
						$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
						echo json_encode($data);
						die;
					}		
				}else 
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
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


	public function add_doctors_holidays()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(isset($headers['Authorization'])){
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

					if(!empty($value['rangeDates']))
					{
						$doctor_holidays_to = explode("T", $value['rangeDates'][1]);

						//print_r($value['rangeDates']); //die;	

						$doctor_holidays_from = explode("T", $value['rangeDates'][0]);

						//print_r($doctor_holidays_to);	die;	

						if(isset($value['doctor_id']))
						{
							$data['doctor_id'] = $value['doctor_id'];	
							$data['doctor_holidays_from'] =  date('D j M Y',strtotime($doctor_holidays_from[0]));
							$data['doctor_holidays_to'] =  date('D j M Y',strtotime($doctor_holidays_to[0]));


							$result = $this->Time_schedule_model->chk_holidays_where($data);

							if($result == D){

								//echo "<pre>"; print_r($data); die;				

								$result = $this->Common_model->insert("doctor_holidays", $data); 

								if($result == D){
									$data = array("status" => A, "message" => COMMON_ERROR_OCCURED_MSG);
									echo json_encode($data);
									die;
								}
								else if($result > 0){
									$data = array("status" => I, "message" => "Added successfully");
									echo json_encode($data);
									die;
								}else 
								{
									$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
									echo json_encode($data);
									die;
								}
							}else{
								$data = array("status" => A,"message" => "Sorry! You have already used these dates for holidays.");
									echo json_encode($data);
									die;
							}		
						}else 
						{
							$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
							echo json_encode($data);
							die;
						}
					}else 
					{
						$data = array("status" => A,"message" => "Schedule disable start time or end time is empty.");
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
	


	public function delete_doctors_holidays($id)
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($id))
			{
				
				$result = $this->Common_model->delete("doctor_holidays", "doctor_holidays_id",$id); 

				if($result == D){
					$data = array("status" => A, "message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}
				else if($result == O){
					$data = array("status" => I, "message" => "Deleted successfully");
					echo json_encode($data);
					die;
				}else 
				{
					$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
					echo json_encode($data);
					die;
				}		
			}else 
			{
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

	public function update_patient_schedule_status($id){
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($id))
			{
				
				$result = $this->Common_model->update("patient_schedule", "patient_schedule_id",$id, array("schedule_status" => 1, "schedule_updated_on"=>date("Y-m-d h:i:s"))); 
				
				if($result == D){
					$data = array("status" => A, "message" => "Holidays not available.");
					echo json_encode($data);
					die;
				}
				else {
					$data = array("status" => I, "message" => "Updated successfully.");
					echo json_encode($data);
					die;
				}	
			}else 
			{
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


	public function get_doctors_holidays($id)
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($id))
			{
				
				$result = $this->Common_model->get_where("doctor_holidays", "doctor_id",$id); 
				
				if($result == B){
					$data = array("status" => A, "message" => "Holidays not available.");
					echo json_encode($data);
					die;
				}
				else {
					$result = (array)$result;
					$data = array("status" => I, "data" => $result);
					echo json_encode($data);
					die;
				}	
			}else 
			{
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

	public function get_video_call_time()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			if(!empty($value['patient_id']) || !empty($value['doctor_id']))
			{	
				$scheduleDate = date("Y-m-d",strtotime($value['schedule_date']));
				$minTime  = date("h:i A",strtotime("-1 hour", strtotime($value['schedule_date'])));			
				$maxTime = date("h:i A",strtotime($value['schedule_date']));

				if(isset($value['doctor_id']) && !empty($value['doctor_id'])){
					$result = $this->Time_schedule_model->get_video_call_time($value['doctor_id'],$scheduleDate,$minTime,$maxTime,"doctor"); 
				}else{
					$result = $this->Time_schedule_model->get_video_call_time($value['patient_id'],$scheduleDate,$minTime,$maxTime,"patient");
				}
				
				if($result == 0){
					$data = array("status" => I, "message" => "Appointment not available.");
					echo json_encode($data);
					die;
				}
				else {
					$data = array("status" => I, "data" => $result, "end_time" => date('h:i', strtotime($result[0]->schedule_end_time)));
					echo json_encode($data);
					die;
				}	
			}else 
			{
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

	public function get_patient_schedule_byId()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			if(!empty($value['schedule_id']))
			{
				if($value['type'] == "doctor"){
					$result = $this->Time_schedule_model->get_patient_schedule_byId($value['doctor_id'],$value['schedule_id'],"doctor"); 
				}else{
					$result = $this->Time_schedule_model->get_patient_schedule_byId($value['patient_id'],$value['schedule_id'],"patient"); 
				}
				
				if($result == 0){
					$data = array("status" => A, "message" => "Appointment not available.");
					echo json_encode($data);
					die;
				}
				else {
					$data = array("status" => I, "data" => $result, "end_time" => date('h:i', strtotime($result[0]->schedule_end_time)));
					echo json_encode($data);
					die;
				}	
			}else 
			{
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

	public function createDateRange($startDate, $endDate, $format = "D M j Y")
	{
	    $begin = new DateTime($startDate);
	    $end = new DateTime($endDate);

	    $interval = new DateInterval('P1D'); // 1 Day
	    $dateRange = new DatePeriod($begin, $interval, $end);

	    $range = [];
	    foreach ($dateRange as $date) {
	        $range[] = $date->format($format);
	    }

	    return $range;
	}

	public function get_doctors_invalid_dates($id)
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(!empty($id))
			{
				
				$result = $this->Common_model->get_where("doctor_holidays", "doctor_id",$id); 
				$result = (array)$result;
				//print_r($result);

				$i = 0; $newDates = "";
				foreach ($result as $key => $val) {
					//$val = json_decode($val,true);
					$startDate = date("D M j Y",strtotime($val->doctor_holidays_from));
					$endDate = date("D M j Y",strtotime($val->doctor_holidays_to));
					if(!empty($startDate) && !empty($endDate)){
						$createDateRange = $this->createDateRange($startDate, $endDate);
						if(!empty($createDateRange)){
							$newDates .= implode(",", $createDateRange);
						}else{
							$createDateRange = array($startDate, $endDate);
							$newDates .= implode(",", $createDateRange);
						}						
					}
				}				
				
				if($result == B){
					$data = array("status" => A, "message" => "Holidays not available.");
					echo json_encode($data);
					die;
				}
				else {
					$result = (array)$result;
					$data = array("status" => I, "data" => $newDates);
					echo json_encode($data);
					die;
				}	
			}else 
			{
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


	public function get_doctors_slots_byDay()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			$input = @file_get_contents("php://input");
			$value = json_decode($input, true);
			if(!empty($value['doctor_id']))
			{	
				$today_date = 0;
				$dat = date("Y-m-d");
				$arr = explode("T", $value['schedule_date']);
				
				if ($arr[0] == $dat) {
				     $today_date = 1;
				}

				$result = $this->Time_schedule_model->get_doctors_slots_byDay($value['doctor_id'],$value['week_day']); 
				
				if($result == B){
					$data = array("status" => A, "message" => "Doctors time slots are not available.");
					echo json_encode($data);
					die;
				}
				else {
					$doctor_data = json_encode($result);
					$doctor_data = json_decode($doctor_data, true);
					$timeSlot = array();
					$NewTimeSlot = array();
					$i = 0; $k = 0;
					foreach ($doctor_data as $key => $v) 
					{						
						$doctor_times = json_decode($doctor_data[$key]['doctor_time_schedule'],true);		
						$j=0;
						$newDate = strtotime($doctor_times['open_time']);
						$close_time = strtotime($doctor_times['close_time']);
						while ($newDate <= $close_time) {
							// echo $newDate."<br>";
							// echo strtotime($doctor_times['close_time'])."<br>";
							$timeSlot[$i]['open_time'] = $doctor_times['open_time'];
							$timeSlot[$i]['close_time'] = $doctor_times['close_time'];	
							if($i == 0 && $k == 0 || $j == 0){
								$k = 1; $j = 1;
								$check = $this->Time_schedule_model->chk_timeslot_avaibility($value['doctor_id'],$arr[0],$doctor_times['open_time']);
								if($check == 0){
									if($today_date == 0){
										$timeSlot[$i]['slot'] = $doctor_times['open_time'];		
										$timeSlot[$i]['status'] = 1;
										$i++;
									}
									else if(strtotime($doctor_times['open_time']) >= time()+300+60*60){
										$timeSlot[$i]['slot'] = $doctor_times['open_time'];	
										$timeSlot[$i]['status'] = 1;$i++;
									}else{
										$timeSlot[$i]['slot'] = $doctor_times['open_time'];
										$timeSlot[$i]['status'] = 0; $i++;						
									}
								}else{
									$timeSlot[$i]['slot'] = $doctor_times['open_time'];
									$timeSlot[$i]['status'] = 0; $i++;
								}
								
								//echo "<br>";
								
							}else{
								$newDate = strtotime("+30 minutes", $newDate);
								$NewTimeSlot = date('h:i A',$newDate);
								if(strtotime($doctor_times['close_time']) > strtotime($NewTimeSlot)){
									$check = $this->Time_schedule_model->chk_timeslot_avaibility($value['doctor_id'],$arr[0],$NewTimeSlot);
									if($check == 0){
										//echo strtotime($NewTimeSlot); echo "<br>"; echo strtotime(date('h:i A')); echo "<br>";
										//echo strtotime($NewTimeSlot)."*****".time()+300; echo "<br>";
										if($today_date == 0){
											//echo "a<br>";
											$timeSlot[$i]['slot'] = $NewTimeSlot;
											$timeSlot[$i]['status'] = 1; $i++;
										}
										else if(strtotime($NewTimeSlot) >= time()+300+60*60){	
											//echo "b<br>";
											$timeSlot[$i]['slot'] = $NewTimeSlot;
											$timeSlot[$i]['status'] = 1; $i++;
										}else{
											//echo "c<br>";
											$timeSlot[$i]['slot'] = $NewTimeSlot;
											$timeSlot[$i]['status'] = 0; $i++;
										}
									}else{
										//echo "d<br>";
										$timeSlot[$i]['slot'] = $NewTimeSlot;
										$timeSlot[$i]['status'] = 0; $i++;
									}
								}
								 //echo "<br>";
							}							
						}
					}
					if(!empty($timeSlot)){
						//array_pop($timeSlot);
						$data = array("status" => I, "data" => $timeSlot);
					}else{
						$data = array("status" => A, "data"=>"", "message" => "Doctors time slots are not available.");
					}
					
					echo json_encode($data);
					die;
				}	
			}else 
			{
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


	public function disable_doctor_time_scheduel()
	{
		$headers = apache_request_headers();
		if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' || isset($headers['content-type']) && $headers['content-type'] == 'application/json')
		{
			if(isset($headers['Authorization'])){
				$input = @file_get_contents("php://input");
				$value = json_decode($input, true);

				if(!empty($value['rangeDates'][0]) && !empty($value['rangeDates'][1]))
				{
					$doctor_time_schedule_disable_to = explode(" 00:00:00 ", $value['rangeDates'][0]);					

					$doctor_time_schedule_disable_from = explode(" 00:00:00 ", $value['rangeDates'][1]);

					if(isset($value['doctor_time_schedule_id']) && $value['doctor_time_schedule_id'] != "")
					{
						$data['doctor_time_schedule_status'] = 0;	
						$data['doctor_time_schedule_disable_from'] =  date('D j M Y',strtotime($doctor_time_schedule_disable_from[0]));
						$data['doctor_time_schedule_disable_to'] =  date('D j M Y',strtotime($doctor_time_schedule_disable_to[0]));

						//echo "<pre>"; print_r($data); die;				

						$result = $this->Time_schedule_model->disable_doctor_time_scheduel($data, $value['doctor_time_schedule_id']); 

						if($result == D){
							$data = array("status" => A, "message" => COMMON_ERROR_OCCURED_MSG);
							echo json_encode($data);
							die;
						}
						else if($result == H){
							$data = array("status" => I, "message" => "Updated successfully");
							echo json_encode($data);
							die;
						}else 
						{
							$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
							echo json_encode($data);
							die;
						}		
					}else 
					{
						$data = array("status" => A,"message" => COMMON_ERROR_OCCURED_MSG);
						echo json_encode($data);
						die;
					}
				}else 
				{
					$data = array("status" => A,"message" => "Schedule disable start time or end time is empty.");
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