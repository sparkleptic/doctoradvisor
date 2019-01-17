<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		//$this->load->view('welcome_message');
		 $data = array();                                                                    
		// $data_string = json_encode($data);  

		//$url = 'https://api.infermedica.com/v2/symptoms';
		$url = 'https://api.infermedica.com/v2/symptoms';

		$ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","app_id: fbba825f", "app_key: 40e55f37855ecf432808f8a807db90b4")); 
        $head = curl_exec($ch); 
        curl_close ($ch);
        $res = json_decode($head,TRUE);
        // echo "<pre>";
        // print_r($res); 
        // echo '</pre>';
        // $new = array();
       
        //$first_names = array_column($res,'name', 'id');
        //array_push($new, $first_names);
		echo "<pre>";
        print_r($res); 
        echo '</pre>';

        die;
	}
}
