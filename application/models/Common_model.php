<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class common_model extends CI_Model {

	public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function check_user_authorization($token) 
	{
		$this->db->select('*');
		$this->db->from('user');
		$this->db->where('user_token',$token);	
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {			
			if($query->result()[0]->user_login_end_time >= date("Y-m-d h:i:s"))
			{
		    	return L;
			} 
			else{
		    	return M;
			}			
		} else {
			return B;
		}
	}


	public function check_authorization($token,$type="admin") 
	{
		$this->db->select('*');
		if($type == "admin"){
			$this->db->from('admin');
			$this->db->where('admin_token',$token);	
		}else{
			$this->db->from('user');
			$this->db->where('user_token',$token);	
		}		
		$query = $this->db->get();	
		//echo $this->db->last_query();
		if ($query->num_rows() > 0) {
			if($type == "admin"){
				if($query->result()[0]->admin_login_end_time >= date("Y-m-d h:i:s"))
				{
			    	return L;
				} 
				else{
			    	return M;
				}
			}else{
				if($query->result()[0]->user_login_end_time >= date("Y-m-d h:i:s"))
				{
			    	return L;
				} 
				else{
			    	return M;
				}
			} 
		} else {
			return B;
		}
	}

	

	public function user_login($username,$password) 
	{
		$this->db->select('*');
		$this->db->from('user');
		$this->db->where('user_email',$username);
		$this->db->where('user_password',md5($password));
		$this->db->or_where('user_uname',$username);
		$this->db->where('user_password',md5($password));
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
				$user_data = $query->result()[0];		

				$data['user_token'] = $this->generateRandomString(100);
				$effectiveDate = strtotime("+24 hours", strtotime(date("Y-m-d h:i:s")));
				$data['user_login_end_time'] = date("Y-m-d h:i:s",$effectiveDate);

				$this->db->where('user_email',$username);
				$this->db->where('user_password',md5($password));
				$this->db->or_where('user_uname',$username);
				$this->db->where('user_password',md5($password));
		    	$this->db->update('user', $data);		    	

		    	$sql = "SELECT ".$user_data->user_type.".*, user.* FROM ".$user_data->user_type.", user WHERE ".$user_data->user_type.".user_id = user.user_id and ".$user_data->user_type.".user_id = ".$user_data->user_id."";
		    	$queryss = $this->db->query($sql);

				return $queryss->result_array();
				
		} else {
			return B;
		}
	}

	public function check_exist($table,$field,$value){
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where($field,$value);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return J;	
		} else {
			return B;
		}
	}

	public function insert($table, $data) {
		$this->db->insert($table, $data);	
		$inserted_id = $this->db->insert_id();
		if($inserted_id != "" && $inserted_id > 0){
			return $inserted_id;
		}else{
			return D;
		}
	}


	public function update($table,$where_column,$where_value,$data) {

		$this->db->where($where_column,$where_value);
		$this->db->update($table,$data);
	    if($this->db->affected_rows() > 0) {			
			return H;
		} else {
			return D;
		}
	}


	public function delete($table,$where_column,$where_value) 
	{
		$this->db->where($where_column,$where_value);
		$this->db->delete($table);
	    if($this->db->affected_rows() > 0) {			
			return O;
		} else {
			return D;
		}
	}

	public function check_where($table,$where_token,$token) 
	{
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where($where_token,$token);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return J;			
		} else {
			return B;
		}
	}

	public function get_where($table,$where_field,$where_value) 
	{
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where($where_field,$where_value);
		$query = $this->db->get();	
		//echo $this->db->last_query();
		if ($query->num_rows() > 0) {
			return $query->result();	
		} else {
			return B;
		}
	}


	public function get_field_value_where($table,$field,$where_field,$where_value) 
	{
		$this->db->select($field);
		$this->db->from($table);
		$this->db->where($where_field,$where_value);
		$query = $this->db->get();	
		//echo $this->db->last_query();
		//echo $query->num_rows(); echo "<br>";
		if ($query->num_rows() > 0) {
			return $query->result();	
		} else {
			return B;
		}
	}


	public function get_where_in($table,$where_column,$where_val_arr) 
	{
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where_in($where_column,$where_val_arr);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return $query->result();	
		} else {
			return B;
		}
	}


	public function get_count_where($table,$where_column,$where_val_arr) 
	{
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where_in($where_column,$where_val_arr);
		$query = $this->db->get();	
		return $query->num_rows();
	}


	public function get_all($table) 
	{
		$this->db->select('*');
		$this->db->from($table);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return $query->result();	
		} else {
			return B;
		}
	}

	public function get_state_by_cid($cid) 
	{
		$this->db->select('*');
		$this->db->from('states');
		$this->db->where('country_id', $cid);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return $query->result();	
		} else {
			return B;
		}
	}

	public function get_cities_by_sid($sid) 
	{
		$this->db->select('*');
		$this->db->from('cities');
		$this->db->where('state_id', $sid);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return $query->result();	
		} else {
			return B;
		}
	}

	public function user_logout($token,$userId){
		$this->db->where('user_token', $token);
		$this->db->where('user_id', $userId);
	    $this->db->update('user', array("user_token" => ""));		
		if($this->db->affected_rows() > 0) {			
			return I;
		} else {
			return D;
		}
	}

	public function get_formatted_data($data){
		$data_arr = json_decode($data);
		if( sizeof($data_arr) > 0 && !empty($data_arr) ) {
			$temp = array();
			foreach ($data_arr as $key => $value) {
				$temp[] = $value->itemName; 
			}
			return implode(", ", $temp);
		}
	}


	public function send_email($to,$subject,$msg,$from="damini.jaiswal@techinfini.com")
	{
		if(empty($to) && empty($from) && empty($msg) && empty($sub)) 
		{
			return 0;
		}else{
			$config = array(
			    'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
			    'smtp_host' => 'smtp.sendgrid.net',
			    'smtp_port' => 25,
			    'smtp_user' => 'himanshuintranet',
			    'smtp_pass' => 'TechAdmin@811',
			    'smtp_crypto' => 'security', //can be 'ssl' or 'tls' for example
			    'mailtype' => 'html', //plaintext 'text' mails or 'html'
			    'smtp_timeout' => '4', //in seconds
			    'charset' => 'iso-8859-1',
			    'wordwrap' => TRUE
			);	

			$this->load->library('email', $config);
			
			$this->email->set_newline("\r\n");
			$this->email->from($from); // change it to yours
			$this->email->to($to);// change it to yours
			$this->email->subject($subject);
			$this->email->message($msg);
			if($this->email->send())
			{
				return 1;
			}
			else
			{
				return 0;
			}			
		}
	}
}