<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Time_schedule_model extends CI_Model 
{
	public function get_restro_byId($doctorId)
	{
		$this->db->select('*');
		$this->db->from('doctor_time_schedule');
		$this->db->where('doctor_id',$doctorId);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return B;
		}
	}	


	public function update_doctor_availablility($doctor_data){
		$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
		//print_r($doctor_data); die;
		foreach ($days as $key => $value) {
			if(!empty($doctor_data[$value]))
			{
				$hours[$value] = $doctor_data[$value];
			}else{
				$hours[$value] = false;
			}
		}
		
		$hours['open_time'] = $doctor_data['open_time'];
		$hours['close_time'] = $doctor_data['close_time'];

		$check_data['doctor_time_schedule'] = $hours;

		//print_r($check_data); die;

		$new_data['doctor_time_schedule'] = json_encode($hours);
		
		$dd = $this->check_doctor_availablility($doctor_data['doctor_id'], $check_data, $doctor_data['doctor_time_schedule_id']);
		if($dd == 1){
			$this->db->where('doctor_id',$doctor_data['doctor_id']);
			$this->db->where('doctor_time_schedule_id',$doctor_data['doctor_time_schedule_id']);
			$this->db->update('doctor_time_schedule', $new_data);	
			return $doctor_data['doctor_id'];
		}else{
			return $dd;
			exit;
		}
	}	


	public function check_doctor_availablility($doctorId, $new_data, $doctor_time_schedule_id="")
	{
		$this->db->select('*');
		$this->db->from('doctor_time_schedule');
		$this->db->where('doctor_id',$doctorId);
		if($doctor_time_schedule_id){
			$this->db->where('doctor_time_schedule_id!=',$doctor_time_schedule_id);
		}
		$query = $this->db->get();
		$data = $query->result();
		//echo $this->db->last_query();
		foreach($data as $k => $v){
			$v = json_encode($v);
			$v = json_decode($v,true);
			//print_r($v);

			if(!empty($v['doctor_time_schedule'])){
				$hours_data = json_decode($v['doctor_time_schedule'],true);
				//print_r($hours_data);

				//echo $hours_data_open_time = date("G:i", strtotime($hours_data['open_time'])); echo "<br>";
				//echo $hours_data_close_time = date("G:i", strtotime($hours_data['close_time']));  echo "<br>";

				$hours_data_open_time = date("G:i", strtotime($hours_data['open_time'])); 
				$hours_data_close_time = date("G:i", strtotime($hours_data['close_time'])); 
				//print_r($new_data['doctor_availability']); die;
				$new_hours = $new_data['doctor_time_schedule'];
				//print_r($new_hours); die;
				// echo $new_hours_open_time = date("G:i", strtotime($new_hours['open_time']));  echo "<br>";
				// echo $new_hours_close_time = date("G:i", strtotime($new_hours['close_time']));

				$new_hours_open_time = date("G:i", strtotime($new_hours['open_time'])); 
				$new_hours_close_time = date("G:i", strtotime($new_hours['close_time']));

				$hours_opn = str_replace(":", "", $hours_data_open_time);
				$hours_cls = str_replace(":", "", $hours_data_close_time);

				$new_opn = str_replace(":", "", $new_hours_open_time);
				$new_cls = str_replace(":", "", $new_hours_close_time);
				if($new_cls < $new_opn){
					return "Sorry! You can not select close time less than open time.";
					exit;	
				}

				if($new_cls == $new_opn){
					return "Sorry! Open and close time can not be same.";
					exit;	
				}

				foreach($new_hours as $kk => $vv){
					if(in_array($kk,$hours_data)){ //echo //$vv."=====".$hours_data[$kk]; die;
						if($hours_data[$kk] == $vv && $vv == 1){
							if(($new_opn < $hours_opn && ($new_cls == $hours_opn || $new_cls < $hours_opn)) || (($new_opn > $hours_cls || $new_opn == $hours_cls) && $new_cls > $hours_cls)){
								
							}else{
								return "You have already filled the time for ".$kk." between ".$new_hours['open_time']." to ".$new_hours['close_time'];
								exit;
							}
							// if(($new_hours_open_time < $hours_data_open_time && $new_hours_close_time < $hours_data_close_time) || ($new_hours_open_time > $hours_data_close_time && $new_hours_close_time > $hours_data_close_time)){

							// }
						}
					}				
				}
			}
		}
		return 1;
	}

	public function add_doctor_availability($new_data)
	{		
		$this->db->insert('doctor_time_schedule', $new_data);	
		$inserted_id = $this->db->insert_id();
		if($inserted_id != "" && $inserted_id > 0){
			return $inserted_id;
		}else{
			return D;
		}
	}	


	public function update_restro_basic($userId,$restro_meta_data){
		$this->db->select('restro_id');
		$this->db->from('restro');
		$this->db->where('user_id',$userId);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$restro_id = $query->result()[0]->restro_id;
			$this->db->where('restro_id', $restro_id);
		    $del=$this->db->delete('restro');  
		    if($del)
		    {
				$this->db->insert_batch('restro_meta', $restro_meta_data);
				return $restro_id;
			}
		}
	}
	
}