<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
|--------------------------------------------------------------------------
| Common constants
|--------------------------------------------------------------------------
|
| These constants is used for reuse var
|
*/
define('TO_EMAIL', 'damini.jaiswal@techinfini.com');
define('FROM_EMAIL', 'damini.jaiswal@techinfini.com');
define('BASE_URL', 'http://website.com/projects/doctoradvisor/');
define('Domain', 'http://website.com/');

/*
|--------------------------------------------------------------------------
| Common status constants
|--------------------------------------------------------------------------
|
| These constants is used for api request status
|
*/
define('A', 'error');
define('B', 'not exist');
define('C', 'warning');
define('D', 'fail');
define('E', 'header type error');
define('F', 'redirect');
define('G', 'added');
define('H', 'updated');
define('I', 'success');
define('J', 'exist');
define('K', 'token error');
define('L', 'authorized');
define('M', 'unauthorised');
define('N', 'email sent');
define('O', 'done');
define('P', 'active');
/*
|--------------------------------------------------------------------------
| Common status constants
|--------------------------------------------------------------------------
|
| These constants is used for api request msg
|
*/
define('HEADER_TYPE_ERROR_MSG', 'Sorry! request type error occured, please try later.');
define('COMMON_ERROR_OCCURED_MSG', 'Sorry! Some error occured, please try later.');
define('WRONG_CREDENTIAL_MSG', 'Sorry! You have used wrong details for login.');
define('USER_UNAUTHORIZED_MSG', 'Sorry! Your login token has expired.');
define('TOKEN_NOT_AVAILABLE_MSG', 'Sorry! Your login token is not available, please try to login again.');
define('NOT_EXIST_MSG', 'Sorry! This is not exist.');
define('EMAIL_NOT_EXIST_MSG', 'Sorry! This email address does not exist.');
define('LOGOUT_MSG', 'Logout successfully!');
define('LOGIN_MSG', 'You are logged in successfully!');
define('UPDATE_MSG', 'Updated successfully!');
define('ADD_MSG', 'Added successfully!');
define('PASSWORD_UPDATE_MSG', 'Password updated successfully!');
define('WRONG_FORGOT_PASS_LINK_MSG', 'Sorry! This URL is not authorized to reset your password.');
define('WRONG_OLD_PASSWORD_MSG', 'Sorry! Your old password does not exist.');
define('FORGOT_EMAIL_SENT_MSG', 'We have sent a link on your registered email address to reset your password.');

/*
|--------------------------------------------------------------------------
| Common email template constants
|--------------------------------------------------------------------------
|
| These constants is used for sent email
|
*/
define('EMAIL_HEADER_TEMPLATE',  '<table border="0" width="700" cellpadding="0" cellspacing="0" id="" style="background-color:#ffffff;border:1px solid #dedede;border-radius:3px!important; max-width: 100%; width:100%; margin: 0 auto;">
	   <tbody>
	      <tr>
	         <td align="center" valign="top">
	            <table border="0" cellpadding="0" cellspacing="0" width="700" id="" style="background-color:#fbd74a; border-radius:3px 3px 0 0!important;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif">
	               <tbody>
	                  <tr>
	                     <td id="" style="padding:36px 48px;display:block">
	                        <h1 style="color:#ffffff;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:center;text-transform: uppercase;">DR. Consultation</h1>
	                     </td>
	                  </tr>
	               </tbody>
	            </table>
	         </td>
	      </tr>
	      <tr>
	         <td align="center" valign="top">
	            <span class="HOEnZb"><font color="#888888">
	            </font></span>
	            <table border="0" cellpadding="0" cellspacing="0" width="600" id="">
	               <tbody>
	                  <tr>
	                     <td valign="top" style="background-color:#ffffff">
	                        <span class="HOEnZb"><font color="#888888">
	                        </font></span>
	                        <table border="0" cellpadding="20" cellspacing="0" width="100%">
	                           <tbody>
	                              <tr>
	                                 <td valign="top" style="padding:48px">
	                                    <div style="color:#636363;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left">
	                                       <p style="margin:0 0 16px">');
define('EMAIL_FOOTER_TEMPLATE',  '</p>
	                                       <span class="HOEnZb"><font color="#888888">
	                                       </font></span>
	                                    </div>
	                                    <span class="HOEnZb"><font color="#888888">
	                                    </font></span>
	                                 </td>
	                              </tr>
	                           </tbody>
	                        </table>
	                        <span class="HOEnZb"><font color="#888888">
	                        </font></span>
	                     </td>
	                  </tr>
	               </tbody>
	            </table>
	            <span class="HOEnZb"><font color="#888888">
	            </font></span>
	         </td>
	      </tr>
	      <tr>
	         <td align="center" valign="top">
	            <table border="0" cellpadding="10" cellspacing="0" width="600" id="">
	               <tbody>
	                  <tr>
	                     <td valign="top" style="padding:0">
	                        <table border="0" cellpadding="10" cellspacing="0" width="100%">
	                           <tbody>
	                              <tr>
	                                 <td colspan="2" valign="middle" id="" style="padding:0 48px 48px 48px;border:0;color:#c09bb9;font-family:Arial;font-size:12px;line-height:125%;text-align:center">
	                                    <p>&copy; DR. Consultation</p>
	                                 </td>
	                              </tr>
	                           </tbody>
	                        </table>
	                     </td>
	                  </tr>
	               </tbody>
	            </table>
	         </td>
	      </tr>
	   </tbody>
	</table>');

//PayUMoney Configurations
// define('MERCHANT_KEY', '4ZGiHuf8');
// define('SALT', 'm0UO7bCfqk');
// define('PAYU_BASE_URL', 'https://test.payu.in'); 
define('MERCHANT_KEY', 'FwVH0G');
define('SALT', 'dBckWYym');
define('PAYU_BASE_URL', 'https://test.payu.in');    //Testing url
//define('PAYU_BASE_URL', 'https://secure.payu.in');  //actual URL
define('SUCCESS_URL', 'http://website.com/projects/doctoradvisor/payumoney/order_success');  //have complete url
define('FAIL_URL', 'http://website.com/projects/doctoradvisor/payumoney/order_fail');    //add complete url 
define('PROJECT_URL', 'http://website.com/projects/doctors/#/'); 
define('TEST_PROJECT_URL', 'http://localhost:4200/#/');  