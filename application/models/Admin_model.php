<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admin_model extends CI_Model 
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

	public function admin_login($username,$password) 
	{
		$this->db->select('*');
		$this->db->from('admin');
		$this->db->where('admin_email',$username);
		$this->db->where('admin_password',md5($password));
		$this->db->or_where('admin_uname',$username);
		$this->db->where('admin_password',md5($password));
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {		
				$data['admin_token'] = $this->generateRandomString(100);
				$effectiveDate = strtotime("+24 hours", strtotime(date("Y-m-d h:i:s")));
				$data['admin_login_end_time'] = date("Y-m-d h:i:s",$effectiveDate);
				$this->db->where('admin_email',$username);
				$this->db->where('admin_password',md5($password));
				$this->db->or_where('admin_uname',$username);
				$this->db->where('admin_password',md5($password));
		    	$this->db->update('admin', $data);

		    	$this->db->select('*');
				$this->db->from('admin');
				$this->db->where('admin_email',$username);
				$this->db->where('admin_password',md5($password));
				$this->db->or_where('admin_uname',$username);
				$this->db->where('admin_password',md5($password));
				$query = $this->db->get();
				return $query->result();
		} else {
			return B;
		}
	}

	public function check_authorization($token) 
	{
		$this->db->select('*');
		$this->db->from('admin');
		// $this->db->where('admin_email',$user);
		// $this->db->where('admin_token',$token);
		// $this->db->or_where('admin_uname',$user);
		$this->db->where('admin_token',$token);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			if($query->result()[0]->admin_login_end_time >= date("Y-m-d h:i:s"))
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

	public function getAdminInfo_Bytoken($token){
		$this->db->select('*');
		$this->db->from('admin');
		$this->db->where('admin_token',$token);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			return $query->result()[0];
		} else {
			return B;
		}
	}

	public function change_admin_password($adminId,$oldPass,$data){
		$this->db->select('*');
		$this->db->from('admin');
		$this->db->where('admin_id',$adminId);
		$this->db->where('admin_password',md5($oldPass));
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			$this->db->where('admin_id',$adminId);
			$this->db->where('admin_password',md5($oldPass));
		    $this->db->update('admin', $data);
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
		$this->db->select('admin_email');
		$this->db->from('admin');
		$this->db->where('admin_email',$email);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {			
		    return J;
		} else {
			return B;
		}
	}

	public function check_forgot_password_link($email,$confirmation_hash){
		$this->db->select('*');
		$this->db->from('admin');
		$this->db->where('admin_email',$email);
		$this->db->where('admin_confirmation_hash',md5($confirmation_hash.''.$email));
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
			$this->db->where('admin_email',$email);
			$this->db->where('admin_confirmation_hash',md5($confirmation_hash.''.$email));
		    $this->db->update('admin', array('admin_confirmation_hash' => "",'admin_password' => md5($password)));
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


	public function email_forgot_password($email,$url)
	{
		$email_exist = $this->check_email_exist($email);
		if($email_exist == J)
		{
			$admin_confirmation_hash = $this->generateRandomString(10);
			$this->db->where('admin_email', $email);
		    $this->db->update('admin', array("admin_confirmation_hash" => md5($admin_confirmation_hash.''.$email)));		
				if($this->db->affected_rows() > 0) 
				{
					$this->db->select('*');
					$this->db->from('admin');
					$this->db->where('admin_email',$email);
					$query = $this->db->get();		
					if ($query->num_rows() > 0) 
					{
						$data = json_encode($query->result()[0]);
						$data = json_decode($data,true);			
						$sub = "DR. consulting change password";
						$temp = $email."|&|".$admin_confirmation_hash;

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
																					<h3 mc:edit="header" style="color:#5F5F5F;line-height:125%;font-family:Helvetica,Arial,sans-serif;font-size:20px;font-weight:normal;margin-top:0;margin-bottom:3px;text-align:left;">Hi '.$data["admin_fname"].' '.$data["admin_lname"].',</h3>
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


	public function getAdminId_Bytoken($token) 
	{
		$this->db->select('admin_id');
		$this->db->from('admin');
		$this->db->where('admin_token',$token);
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			if($query->result()[0]->admin_id)
			{
		    	return $query->result()[0]->admin_id;
			} 
			else{
		    	return M;
			} 
		} else {
			return B;
		}
	}

	public function admin_logout($token) 
	{
		$this->db->where('admin_token', $token);
	    $this->db->update('admin', array("admin_token" => ""));		
		if($this->db->affected_rows() > 0) {			
			return I;
		} else {
			return D;
		}
	}

	public function update_admin($adminId,$data) 
	{		
		$this->db->where('admin_id', $adminId);
	    $this->db->update('admin', $data);
	    if($this->db->affected_rows() > 0) {			
			$this->db->select('*');
			$this->db->from('admin');
			$this->db->where('admin_id', $adminId);
			$query = $this->db->get();
			return $query->result();
		} else {
			return D;
		}
	}
}