<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class doctor_model extends CI_Model 
{
	public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function search_doctors_list($specialization,$name,$language,$country){

		$SQL = "select * from doctor ";

		if($specialization != "" || $name != "" || $language != "" || $country != "") {
			$where = "where"; 
		}

		if($specialization != "") {
			$diseases = json_encode($specialization);
			$diseases = json_decode($diseases,true);
			if(is_array($diseases) && !empty($diseases)){				
				$disease_Arr = implode(',', $diseases);
				$where = "where"; 
				$SQL .= $where." doctor_id in (select doctor_id from doctors_specilization where disease_id in (".$disease_Arr."))";
			}			 
		}

		if($name != "") {
			if(!empty($where)){
				$SQL .= " and (doctor_fname like '%".$name."%' OR doctor_lname like '%".$name."%' OR doctor_display_name like '%".$name."%')"; 		
			}else{
				$where = "where"; 
				$SQL .= $where." (doctor_fname like '%".$name."%' OR doctor_lname like '%".$name."%' OR doctor_display_name like '%".$name."%')"; 		
			}				
		}

		if($language != "") {
			if(!empty($where)){
				$SQL .= " and doctor_id in (select doctor_id from doctors_language where language_id = ".$language.")"; 	
			}else{
				$where = "where"; 
				$SQL .= $where." doctor_id in (select doctor_id from doctors_language where language_id = ".$language.")"; 
			}
		}

		if($country != "") {
			if(!empty($where)){
				$SQL .= " and doctor_country = ".$country.""; 		
			}else{
				$where = "where"; 
				$SQL .= $where." doctor_country = ".$country.""; 		
			}	
		}

		$query = $this->db->query($SQL);
		//echo $this->db->last_query();
		return $query->result_array();
			
	}


	public function doctor_login($username,$password) 
	{
		$this->db->select('*');
		$this->db->from('doctor');
		$this->db->where('doctor_email',$username);
		$this->db->where('doctor_password',md5($password));
		$this->db->or_where('doctor_uname',$username);
		$this->db->where('doctor_password',md5($password));
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {		
				$data['doctor_token'] = $this->generateRandomString(100);
				$effectiveDate = strtotime("+24 hours", strtotime(date("Y-m-d h:i:s")));
				$data['doctor_login_end_time'] = date("Y-m-d h:i:s",$effectiveDate);
				$this->db->where('doctor_email',$username);
				$this->db->where('doctor_password',md5($password));
				$this->db->or_where('doctor_uname',$username);
				$this->db->where('doctor_password',md5($password));
		    	$this->db->update('doctor', $data);

		    	$this->db->select('*');
				$this->db->from('doctor');
				$this->db->where('doctor_email',$username);
				$this->db->where('doctor_password',md5($password));
				$this->db->or_where('doctor_uname',$username);
				$this->db->where('doctor_password',md5($password));
				$query = $this->db->get();
				return $query->result();
		} else {
			return B;
		}
	}


	public function getDoctors_byDisease($disease_Arr) 
	{
		$disease_Arr = implode(',', $disease_Arr);
		$SQL = "select doctor_display_name from doctor where doctor_id in (select doctor_id from doctors_specilization where disease_id in (".$disease_Arr."))"; 
		$query = $this->db->query($SQL);
		return $query->result_array();
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


	public function check_email_notid_exist($id,$email){
		$this->db->select('*');
		$this->db->from("doctor");
		$this->db->where("doctor_id!=",$id);
		$this->db->where("doctor_email",$email);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return J;	
		} else {
			return B;
		}
	}


	public function getDoctorAppoinmentsCount($doctorId,$status=""){
		$this->db->select('*');
		$this->db->from("patient_schedule");
		$this->db->where("doctor_id=",$doctorId);
		//if($status != ""){
			$this->db->where("doctor_notification_status",0);
		//}
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		} else {
			return B;
		}
	}

	public function getDoctorScheduleTime($doctorId){
		$this->db->select('schedule_time');
		$this->db->from("patient_schedule");
		$this->db->where("doctor_id=",$doctorId);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			$temp = array();
			foreach ($query->result() as $row) {
		       	$temp[] = $row->schedule_time;
			}
			return $temp;
		} else {
			return B;
		}
	}


	public function getDoctorAppoinment_ById($data){
		$this->db->select('*');
		$this->db->from("patient_schedule");
		if(!empty($data['doctor_id'])){	
			$this->db->where("doctor_id",$data['doctor_id']);
		}
		if(!empty($data['status'])){	
			$this->db->where("schedule_status",$data['status']);
		}
		$query = $this->db->get();	
		//echo $this->db->last_query();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return B;
		}
	}

	public function getDoctorInfo_Bytoken($token){
		$this->db->select('*');
		$this->db->from('doctor');
		$this->db->where('doctor_token',$token);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return $query->result()[0];
		} else {
			return B;
		}
	}


	public function get_specialization($disease_id){
		$diseases = json_decode($disease_id,true);
		$this->db->select('disease_name');
		$this->db->from('disease');
		$this->db->where_in('disease_id', $diseases);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return B;
		}
	}


	public function getDoctorInfo_ById($doctorId){
		$sql = "SELECT doctor.*, user.* FROM doctor, user WHERE doctor.user_id = user.user_id and doctor.doctor_id = ".$doctorId."";
    	$queryss = $this->db->query($sql);
		$resutl_data = $queryss->result_array();
		return current($resutl_data);
	}

	public function change_doctor_password($doctorId,$oldPass,$data){
		$this->db->select('*');
		$this->db->from('doctor');
		$this->db->where('doctor_id',$doctorId);
		$this->db->where('doctor_password',md5($oldPass));
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			$this->db->where('doctor_id',$doctorId);
			$this->db->where('doctor_password',md5($oldPass));
		    $this->db->update('doctor', $data);
		    if($this->db->affected_rows() > 0) {			
				return H;
			} else {
				return D;
			}
		} else {
			return B;
		}
	}


	public function check_email_exist($email) 
	{
		$this->db->select('doctor_email');
		$this->db->from('doctor');
		$this->db->where('doctor_email',$email);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {			
		    return J;
		} else {
			return B;
		}
	}

	public function check_forgot_password_link($email,$confirmation_hash){
		$this->db->select('*');
		$this->db->from('doctor');
		$this->db->where('doctor_email',$email);
		$this->db->where('doctor_confirmation_hash',md5($confirmation_hash.''.$email));
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {			
		    return J;
		} else {
			return B;
		}
	}


	public function reset_forgot_password($email,$confirmation_hash,$password){
		$check_data = $this->check_forgot_password_link($email,$confirmation_hash);
		if($check_data == J){
			$this->db->where('doctor_email',$email);
			$this->db->where('doctor_confirmation_hash',md5($confirmation_hash.''.$email));
		    $this->db->update('doctor', array('doctor_confirmation_hash' => "",'doctor_password' => md5($password)));
		    if($this->db->affected_rows() > 0) {			
				return H;
			} else {
				return D;
			}
		}else {
			return B;
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

	public function remove_doctor_profile_pic($doctor_id){
		$this->db->where('doctor_id', $doctor_id);
		$this->db->update('doctor', array("doctor_profile_pic" => ""));		
	}


	public function email_forgot_password($email,$url)
	{
		$email_exist = $this->check_email_exist($email);
		if($email_exist == J)
		{
			$doctor_confirmation_hash = $this->generateRandomString(10);
			$this->db->where('doctor_email', $email);
		    $this->db->update('doctor', array("doctor_confirmation_hash" => md5($doctor_confirmation_hash.''.$email)));		
				if($this->db->affected_rows() > 0) 
				{
					$this->db->select('*');
					$this->db->from('doctor');
					$this->db->where('doctor_email',$email);
					$query = $this->db->get();		
					if ($query->num_rows() > 0) 
					{
						$data = json_encode($query->result()[0]);
						$data = json_decode($data,true);			
						$sub = "DR. consulting change password";
						$temp = $email."|&|".$doctor_confirmation_hash;

						$msg = '<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF">
										<tr>
											<td align="center" valign="top">
												<table border="0" cellpadding="30" cellspacing="0"  class="flexibleContainer" width="100%">
													<tr>
														<td align="center" valign="top"  class="flexibleContainerCell">
															<table border="0" cellpadding="0" cellspacing="0" width="100%">
																<tr>
																	<td align="center" valign="top">
																		<table border="0" cellpadding="0" cellspacing="0" width="100%">
																			<tr>
																				<td valign="top" class="textContent">
																					<h3 mc:edit="header" style="color:#5F5F5F;line-height:125%;font-family:Helvetica,Arial,sans-serif;font-size:20px;font-weight:normal;margin-top:0;margin-bottom:3px;text-align:left;">Hi '.$data["doctor_fname"].' '.$data["doctor_lname"].',</h3>
																					<div mc:edit="body" style="text-align:left;font-family:Helvetica,Arial,sans-serif;font-size:15px;margin-bottom:0;color:#5F5F5F;line-height:135%;">Please click the link below to change your password.</div>
																				</td>
																			</tr>
																		</table>

																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr style="padding-top:0;">
											<td align="center" valign="top">
												<table border="0" cellpadding="30" cellspacing="0"  class="flexibleContainer">
													<tr>
														<td style="padding-top:0;" align="center" valign="top"  class="flexibleContainerCell">

															<table border="0" cellpadding="0" cellspacing="0" width="100%" class="emailButton" style="background-color: #f4bb1d;">
																<tr>
																	<td align="center" valign="middle" class="buttonContent" style="padding-top:15px;padding-bottom:15px;padding-right:15px;padding-left:15px;">
																		<a style="color:#FFFFFF;text-decoration:none;font-family:Helvetica,Arial,sans-serif;font-size:20px;line-height:135%;" href="'.$url.'/'.base64_encode($temp).'" target="_blank">Change Password</a>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td align="center" valign="top">
												<table border="0" cellpadding="0" cellspacing="0"  class="flexibleContainer">
													<tr>
														<td align="center" valign="top"  class="flexibleContainerCell">
															<table border="0" cellpadding="30" cellspacing="0" width="100%">
																<tr>
																	<td align="center" valign="top">
																		<table border="0" cellpadding="0" cellspacing="0" width="100%">
																			<tr>
																				<td valign="top" class="textContent">
																					<h3 style="color:#5F5F5F;line-height:125%;font-family:Helvetica,Arial,sans-serif;font-size:20px;font-weight:normal;margin-top:0;margin-bottom:3px;text-align:left; display: none;">Message Title</h3>
																					<div style="text-align:left;font-family:Helvetica,Arial,sans-serif;font-size:15px;margin-bottom:0;margin-top:3px;color:#5F5F5F;line-height:135%;">If you can\'t click on the web address above, copy and paste '.$url.'/'.base64_encode($temp).' into your browser.</div>
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>';

				$msg = EMAIL_HEADER_TEMPLATE.$msg.EMAIL_FOOTER_TEMPLATE;
				$to = $email;
				$from = FROM_EMAIL;
				$mail = $this->send_email($to,$sub,$msg,$from);
				if($mail == 1){
					return N;	
				}
				} else {
					return D;
				}
			} else {
				return D;
			}
		}
		else {
			return B;
		}
	}


	public function getDoctorId_Bytoken($token) 
	{
		$this->db->select('doctor_id');
		$this->db->from('doctor');
		$this->db->where('doctor_token',$token);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			if($query->result()[0]->doctor_id)
			{
		    	return $query->result()[0]->doctor_id;
			} 
			else{
		    	return M;
			} 
		} else {
			return B;
		}
	}

	public function doctor_logout($token) 
	{
		$this->db->where('doctor_token', $token);
	    $this->db->update('doctor', array("doctor_token" => ""));		
		if($this->db->affected_rows() > 0) {			
			return I;
		} else {
			return D;
		}
	}

	public function update_doctor($doctorId,$data) 
	{		
		$this->db->where('doctor_id', $doctorId);
	    $this->db->update('doctor', $data);
	    if($this->db->affected_rows() > 0) {			
			// $this->db->select('*');
			// $this->db->from('doctor');
			// $this->db->where('doctor_id', $doctorId);
			// $query = $this->db->get();
			// return $query->result();

			$SQL = "SELECT *
			FROM doctor JOIN user ON doctor.user_id=user.user_id  where doctor.doctor_id=".$doctorId; 
			$query = $this->db->query($SQL);
			return $query->result_array();
		} else {
			return D;
		}
	}
}