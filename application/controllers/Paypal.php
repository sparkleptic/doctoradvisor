<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Paypal_lib.php');
class Paypal extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Doctor_model');  
        $this->load->model('Common_model');  
        $this->load->library('session');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authorization', 'image');
        $p = new paypal_class;             // initiate an instance of the class
        $p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // testing paypal url
        //$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';     // paypal url
    }

    public function index(){
        //print_r($_REQUEST); die;
        if(!empty($_REQUEST['patient_id']) && !empty($_REQUEST['doctor_id'])){
            $this->session->set_userdata(array(
                'patient_id'  => $_REQUEST['patient_id'],
                'doctor_id' => $_REQUEST['doctor_id'],
                'payment_type' => "patient_to_admin"
            ));
            $result = $this->Common_model->get_field_value_where("doctor","doctor_fee,doctor_fee_currency","doctor_id",$_REQUEST['doctor_id']);
           // print_r($result); die;
            $p = new paypal_class;             // initiate an instance of the class
            $p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // testing paypal url
            //$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';     // paypal url
            $value['return_url'] = base_url()."paypal/paypal_success";
            $value['cancel_url'] = base_url()."paypal/paypal_cancel";
            $value['notify_url'] = base_url()."paypal/paypal_ipn";

            if($result != B && !empty($result[0]->doctor_fee)){
                $value['currency_code'] = $result[0]->doctor_fee;
                $value['amount'] = $result[0]->doctor_fee;
            }else{
                $value['amount'] = 100;
            }
            
            $p->add_field('business', 'sandbox4project@gmail.com');
            $p->add_field('return', $value['return_url']);
            $p->add_field('cancel_return', $value['cancel_url']);
            $p->add_field('notify_url', $value['notify_url']);
            $p->add_field('item_name', 'Paypal Test Transaction');
            $p->add_field('amount', $value['amount']);
            $p->add_field('patient', $_REQUEST['patient_id']);
            $p->add_field('doctor', $_REQUEST['doctor_id']);

            $p->submit_paypal_post(); // submit the fields to paypal
        } else{
            redirect(PROJECT_URL."home/paypal/?payment_id=0");
        }
        
        //$p->dump_fields();      // for debugging, output a table of all the fields
        //break;
    }

    public function paypal_process() {

        //Array ( [enabled] => yes [email] => george6024-facilitator@rediffmail.com [title] => PayPal [description] => Pay via PayPal; you can pay with your credit card if you don't have a PayPal account. [testmode] => yes [debug] => no [receiver_email] => [identity_token] => [invoice_prefix] => WC- [send_shipping] => no [address_override] => no [paymentaction] => sale [page_style] => [image_url] => [api_username] => george6024-facilitator_api1.rediffmail.com [api_password] => 1364564379 [api_signature] => AMhmeFc5KnxyrVkuxcdEpNC6xCHKAonNmujubeNqB9nGZOWarXnr2IQ2 )
       // print_r($_REQUEST); die;
        if(!empty($_REQUEST['doctor_id'])){

            $result = $this->Common_model->get_field_value_where("doctor","*","doctor_id",$_REQUEST['doctor_id']);


            if($result != B && !empty($result[0]->doctor_id)){

                $this->session->set_userdata(array(
                    'doctor_id' => $_REQUEST['doctor_id'],
                    'payment_type' => "admin_to_doctor"
                ));

                // Get PayPal API Credential.
                $paypal_credential['api_username'] = "george6024-facilitator_api1.rediffmail.com";
                $paypal_credential['api_password'] = "1364564379";
                $paypal_credential['api_signature'] = "AMhmeFc5KnxyrVkuxcdEpNC6xCHKAonNmujubeNqB9nGZOWarXnr2IQ2";
                $paypal_credential['email'] = "george6024-facilitator@rediffmail.com";

                //print_r($paypal_credential); die;

                $paypal_api_username = $paypal_credential['api_username'];
                $paypal_api_password = $paypal_credential['api_password'];
                $paypal_api_signature = $paypal_credential['api_signature'];
                $paypal_sender_email = $paypal_credential['email'];

                $header = array(
                  'X-PAYPAL-SECURITY-USERID : '.$paypal_api_username,
                  'X-PAYPAL-SECURITY-PASSWORD : '.$paypal_api_password,
                  'X-PAYPAL-SECURITY-SIGNATURE : '.$paypal_api_signature,
                  'X-PAYPAL-REQUEST-DATA-FORMAT : NV',
                  'X-PAYPAL-RESPONSE-DATA-FORMAT : JSON',
                  'X-PAYPAL-APPLICATION-ID : APP-80W284485P519543T',
                );

                $value['return_url'] = base_url()."paypal/paypal_success";
                $value['cancel_url'] = base_url()."paypal/paypal_cancel";
                $value['notify_url'] = base_url()."paypal/paypal_ipn";

                $amt_sendermailID = $paypal_sender_email;
                $prm_cancelurl = $value['cancel_url'];
                $prm_curr_code = 'USD';
                $amt_recev_emailID = 'sandbox4project@gmail.com';
                if(!empty($_REQUEST['amount'])){
                    $amt_recev_amt = $_REQUEST['amount'];
                }else{
                    $amt_recev_amt = $result[0]->doctor_fee;
                }                
                $prm_returnurl =  $value['return_url'];
                

                $args = 'actionType=PAY&senderEmail='.$amt_sendermailID.'&cancelUrl='.$prm_cancelurl.'&currencyCode='.$prm_curr_code.'&receiverList.receiver.email='.$amt_recev_emailID.'&receiverList.receiver.amount='.$amt_recev_amt.'&requestEnvelope.errorLanguage=en_US&returnUrl='.$prm_returnurl.'';         
                 
                 $ch = curl_init("https://svcs.sandbox.paypal.com/AdaptivePayments/Pay");
                 //See the cURL documentation for more information: http://curl.haxx.se/docs/sslcerts.html
                 //We recommend using this bundle: https://raw.githubusercontent.com/bagder/ca-bundle/master/ca-bundle.crt
                 // curl_setopt( $ch, CURLOPT_CAINFO, "ca-bundle.crt");
                 // curl_setopt( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                 curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
                 curl_setopt( $ch, CURLOPT_HEADER, 0);
                 curl_setopt( $ch, CURLOPT_POST, 1);
                 curl_setopt( $ch, CURLOPT_POSTFIELDS, $args);
                 // curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
                 // curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
                $response = json_decode(curl_exec($ch),true);

               // print_r($response); die;
                if(!empty($response)  && is_array($response)){
                    $data['doctor_id'] =  $this->session->userdata("doctor_id");
                    $data['payment_transaction_id'] = $response['paymentInfoList']['paymentInfo'][0]['transactionId'];
                    $data['payment_transaction_status'] = $response['paymentInfoList']['paymentInfo'][0]['transactionStatus'];
                    $data['sender_transaction_id'] = $response['paymentInfoList']['paymentInfo'][0]['senderTransactionId'];
                    $data['sender_transaction_status'] = $response['paymentInfoList']['paymentInfo'][0]['senderTransactionStatus'];
                    $data['payment_method'] = "Paypal";                
                    $data['receiver_accountId'] = $response['paymentInfoList']['paymentInfo'][0]['receiver']['accountId'];
                    $data['receiver_email'] = $response['paymentInfoList']['paymentInfo'][0]['receiver']['email'];
                    $data['payment_currency'] = "USD";
                    $data['payment_amount'] = $response['paymentInfoList']['paymentInfo'][0]['receiver']['amount'];
                    $data['payment_execution_status'] = $response['paymentExecStatus'];
                    $data['payment_key'] = $response['payKey'];
                    $data['created_on'] = $response['responseEnvelope']['timestamp'];
                    //$this->session->userdata("payment_date");
                    $payment_data = $this->Common_model->insert('admin_payments',$data);
                    if($payment_data > 0){
                        redirect(PROJECT_URL."admin/dashboard/payment/process?payment_id=".$payment_data);
                    }else{
                        redirect(PROJECT_URL."admin/dashboard/payment/process?payment_id=0");
                    }
                }else{
                    redirect(PROJECT_URL."admin/dashboard/payment/process?payment_id=0");
                }
            }else{
                redirect(PROJECT_URL."admin/dashboard/payment/process?payment_id=0");
            }
        }else{            
            redirect(PROJECT_URL."admin/dashboard/payment/process?payment_id=0");
        }
    }


    public function refund_paypal_process() {

        //Array ( [enabled] => yes [email] => george6024-facilitator@rediffmail.com [title] => PayPal [description] => Pay via PayPal; you can pay with your credit card if you don't have a PayPal account. [testmode] => yes [debug] => no [receiver_email] => [identity_token] => [invoice_prefix] => WC- [send_shipping] => no [address_override] => no [paymentaction] => sale [page_style] => [image_url] => [api_username] => george6024-facilitator_api1.rediffmail.com [api_password] => 1364564379 [api_signature] => AMhmeFc5KnxyrVkuxcdEpNC6xCHKAonNmujubeNqB9nGZOWarXnr2IQ2 )
       // print_r($_REQUEST); die;
        if(!empty($_REQUEST['patient_id'])){

            $result = $this->Common_model->get_field_value_where("patient","*","patient_id",$_REQUEST['patient_id']);

            // $result = $this->Common_model->get_field_value_where("refund_payment","*","patient_id",$_REQUEST['patient_id']);


            if($result != B && !empty($result[0]->patient_id)){

                $this->session->set_userdata(array(
                    'patient_id' => $_REQUEST['patient_id'],
                    'payment_type' => "refund_to_patient",
                     'refund_id' => $_REQUEST['refund_id']
                ));

                // Get PayPal API Credential.
                $paypal_credential['api_username'] = "george6024-facilitator_api1.rediffmail.com";
                $paypal_credential['api_password'] = "1364564379";
                $paypal_credential['api_signature'] = "AMhmeFc5KnxyrVkuxcdEpNC6xCHKAonNmujubeNqB9nGZOWarXnr2IQ2";
                $paypal_credential['email'] = "george6024-facilitator@rediffmail.com";

                //print_r($paypal_credential); die;

                $paypal_api_username = $paypal_credential['api_username'];
                $paypal_api_password = $paypal_credential['api_password'];
                $paypal_api_signature = $paypal_credential['api_signature'];
                $paypal_sender_email = $paypal_credential['email'];

                $header = array(
                  'X-PAYPAL-SECURITY-USERID : '.$paypal_api_username,
                  'X-PAYPAL-SECURITY-PASSWORD : '.$paypal_api_password,
                  'X-PAYPAL-SECURITY-SIGNATURE : '.$paypal_api_signature,
                  'X-PAYPAL-REQUEST-DATA-FORMAT : NV',
                  'X-PAYPAL-RESPONSE-DATA-FORMAT : JSON',
                  'X-PAYPAL-APPLICATION-ID : APP-80W284485P519543T',
                );

                $value['return_url'] = base_url()."paypal/paypal_success";
                $value['cancel_url'] = base_url()."paypal/paypal_cancel";
                $value['notify_url'] = base_url()."paypal/paypal_ipn";

                $amt_sendermailID = $paypal_sender_email;
                $prm_cancelurl = $value['cancel_url'];
                $prm_curr_code = 'USD';
                $amt_recev_emailID = 'sandbox4project@gmail.com';
                if(!empty($_REQUEST['amount'])){
                    $amt_recev_amt = $_REQUEST['amount'];
                }else{
                    $amt_recev_amt = 0;
                }                
                $prm_returnurl =  $value['return_url'];
                

                $args = 'actionType=PAY&senderEmail='.$amt_sendermailID.'&cancelUrl='.$prm_cancelurl.'&currencyCode='.$prm_curr_code.'&receiverList.receiver.email='.$amt_recev_emailID.'&receiverList.receiver.amount='.$amt_recev_amt.'&requestEnvelope.errorLanguage=en_US&returnUrl='.$prm_returnurl.'';         
                 
                 $ch = curl_init("https://svcs.sandbox.paypal.com/AdaptivePayments/Pay");
                 //See the cURL documentation for more information: http://curl.haxx.se/docs/sslcerts.html
                 //We recommend using this bundle: https://raw.githubusercontent.com/bagder/ca-bundle/master/ca-bundle.crt
                 // curl_setopt( $ch, CURLOPT_CAINFO, "ca-bundle.crt");
                 // curl_setopt( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                 curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
                 curl_setopt( $ch, CURLOPT_HEADER, 0);
                 curl_setopt( $ch, CURLOPT_POST, 1);
                 curl_setopt( $ch, CURLOPT_POSTFIELDS, $args);
                 // curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
                 // curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
                $response = json_decode(curl_exec($ch),true);

                //print_r($response); die;
                if(!empty($response)  && is_array($response)){
                    $data['patient_id'] =  $this->session->userdata("patient_id");
                    $data['patient_id'] =  $this->session->userdata("refund_id");
                    $data['refund_application_id'] = $response['paymentInfoList']['paymentInfo'][0]['transactionId'];
                    $data['payment_transaction_status'] = $response['paymentInfoList']['paymentInfo'][0]['transactionStatus'];
                    $data['sender_transaction_id'] = $response['paymentInfoList']['paymentInfo'][0]['senderTransactionId'];
                    $data['sender_transaction_status'] = $response['paymentInfoList']['paymentInfo'][0]['senderTransactionStatus'];
                    $data['payment_method'] = "Paypal";                
                    $data['receiver_accountId'] = $response['paymentInfoList']['paymentInfo'][0]['receiver']['accountId'];
                    $data['receiver_email'] = $response['paymentInfoList']['paymentInfo'][0]['receiver']['email'];
                    $data['payment_currency'] = "USD";
                    $data['payment_amount'] = $response['paymentInfoList']['paymentInfo'][0]['receiver']['amount'];
                    $data['payment_execution_status'] = $response['paymentExecStatus'];
                    $data['payment_key'] = $response['payKey'];
                    $data['created_on'] = $response['responseEnvelope']['timestamp'];
                    //$this->session->userdata("payment_date");
                    $payment_data = $this->Common_model->insert('admin_refund_payments',$data);
                    if($payment_data > 0){
                        redirect(TEST_PROJECT_URL."admin/dashboard/payment/refund-process?refund_payment_id=".$payment_data);
                    }else{
                        redirect(PROJECT_URL."admin/dashboard/payment/refund-process?refund_payment_id=0");
                    }
                }else{
                    redirect(PROJECT_URL."admin/dashboard/payment/refund-process?refund_payment_id=0");
                }
            }else{
                redirect(PROJECT_URL."admin/dashboard/payment/refund-process?refund_payment_id=0");
            }
        }else{            
            redirect(PROJECT_URL."admin/dashboard/payment/refund-process?refund_payment_id=0");
        }
    }


    public function paypal_success() {
        if($this->session->userdata("payment_type") == "patient_to_admin"){
            $data['patient_pum_paymet_status'] = $this->input->post("payment_status");
            $data['patient_name'] = $this->input->post("first_name");
            $data['patient_pum_paymet_amount'] = $this->input->post("payment_gross");
            $data['patient_pum_paymet_txnid'] = $this->input->post("txn_id");
            $data['patient_pum_paymet_posted_hash'] = $this->input->post("verify_sign");
            $data['patient_pum_paymet_key'] = $this->input->post("payer_id");
            $data['patient_email'] = $this->input->post("payer_email");
            $data['patient_id'] = $this->session->userdata("patient_id");
            $data['doctor_id'] = $this->session->userdata("doctor_id");
            $data['patient_pum_paymet_date'] = date("Y-m-d h:i:s");
            $data['payment_type'] = "Paypal";
            //$this->session->userdata("payment_date");
            $payment_data = $this->Common_model->insert('patient_pum_paymet',$data);
            redirect(PROJECT_URL."home/paypal/?payment_id=".$payment_data);
        }else{
            $data['doctors_id'] =  $this->session->userdata("doctor_id");
            $payment_data = $this->Common_model->insert('admin_payments',$data);
            redirect(PROJECT_URL."admin/dashboard/payment/?payment_id=".$payment_data);
        }
    }

    public function paypal_cancel() {
        redirect(PROJECT_URL."home/paypal/?payment_id=0");
    }

    public function paypal_ipn() {
       redirect(PROJECT_URL."home/paypal/?payment_id=0");
        // if ($p->validate_ipn()) {
          
        //      // Payment has been recieved and IPN is verified.  This is where you
        //      // update your database to activate or process the order, or setup
        //      // the database with the user's order details, email an administrator,
        //      // etc.  You can access a slew of information via the ipn_data() array.
      
        //      // Check the paypal documentation for specifics on what information
        //      // is available in the IPN POST variables.  Basically, all the POST vars
        //      // which paypal sends, which we send back for validation, are now stored
        //      // in the ipn_data() array.
      
        //      // For this example, we'll just email ourselves ALL the data.
        //      $subject = 'Instant Payment Notification - Recieved Payment';
        //      $to = 'YOUR EMAIL ADDRESS HERE';    //  your email
        //      $body =  "An instant payment notification was successfully recieved\n";
        //      $body .= "from ".$p->ipn_data['payer_email']." on ".date('m/d/Y');
        //      $body .= " at ".date('g:i A')."\n\nDetails:\n";
             
        //      foreach ($p->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
        //      mail($to, $subject, $body);
        // }
        // break;
    }


    // function paytostore_callback() {

    //     // Get PayPal API Credential.
    //     $paypal_credential = get_option('woocommerce_paypal_settings');

    //     $paypal_api_username = $paypal_credential['api_username'];
    //     $paypal_api_password = $paypal_credential['api_password'];
    //     $paypal_api_signature = $paypal_credential['api_signature'];
    //     $paypal_sender_email = $paypal_credential['email'];

    //     $header = array(
    //       'X-PAYPAL-SECURITY-USERID : '.$paypal_api_username,
    //       'X-PAYPAL-SECURITY-PASSWORD : '.$paypal_api_password,
    //       'X-PAYPAL-SECURITY-SIGNATURE : '.$paypal_api_signature,
    //       'X-PAYPAL-REQUEST-DATA-FORMAT : NV',
    //       'X-PAYPAL-RESPONSE-DATA-FORMAT : JSON',
    //       'X-PAYPAL-APPLICATION-ID : APP-80W284485P519543T',
    //     );

    //     $amt_sendermailID = $paypal_sender_email;
    //     $prm_cancelurl = 'https://example.com';
    //     $prm_curr_code = 'AUD';
    //     $amt_recev_emailID = 'sandbox4project@gmail.com';
    //     $amt_recev_amt = 0.10;
    //     $prm_returnurl = 'https://example.com';
        

    //     $args = 'actionType=PAY&senderEmail='.$amt_sendermailID.'&cancelUrl='.$prm_cancelurl.'&currencyCode='.$prm_curr_code.'&receiverList.receiver.email='.$amt_recev_emailID.'&receiverList.receiver.amount='.$amt_recev_amt.'&requestEnvelope.errorLanguage=en_US&returnUrl='.$prm_returnurl.'';         
         
    //      $ch = curl_init("https://svcs.sandbox.paypal.com/AdaptivePayments/Pay");
    //      //See the cURL documentation for more information: http://curl.haxx.se/docs/sslcerts.html
    //      //We recommend using this bundle: https://raw.githubusercontent.com/bagder/ca-bundle/master/ca-bundle.crt
    //      // curl_setopt( $ch, CURLOPT_CAINFO, "ca-bundle.crt");
    //      // curl_setopt( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    //      curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
    //      curl_setopt( $ch, CURLOPT_HEADER, 0);
    //      curl_setopt( $ch, CURLOPT_POST, 1);
    //      curl_setopt( $ch, CURLOPT_POSTFIELDS, $args);
    //      // curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
    //      // curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    //      $response = curl_exec( $ch );
    //      curl_close ($ch);

    //      //json_decode($response);
    //      // echo var_dump($json);
    //      //print_r($json);
    //      die();            
    // }
}