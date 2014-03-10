<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$                                                                                                                 
 *
 */
require_once 'CRM/Core/Payment.php';

class CRM_Core_Payment_SecureTrading extends CRM_Core_Payment {
    const
        CHARSET  = 'iso-8859-1';
    
    protected $_mode = null;

	static protected $_params = array();

    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = null;
    
    /** 
     * Constructor 
     * 
     * @param string $mode the mode of operation: live or test
     *
     * @return void 
     */ 
    function __construct( $mode, &$paymentProcessor ) {
	$mode = 'test';
        $this->_mode             = $mode;
        $this->_paymentProcessor = $paymentProcessor;
        $this->_processorName    = ts('Secure Trading');

        if ( $this->_paymentProcessor['payment_processor_type'] == 'SecureTrading' ) {
            return;
        }

        //if ( ! $this->_paymentProcessor['user_name'] ) {
        //    CRM_Core_Error::fatal( ts( 'Could not find User ID for payment processor' ) );
        //}
    }

    /** 
     * singleton function used to manage this object 
     * 
     * @param string $mode the mode of operation: live or test
     *
     * @return object 
     * @static 
     * 
     */ 
    static function &singleton( $mode, &$paymentProcessor ) {
        $processorName = $paymentProcessor['name'];
		


        if (self::$_singleton[$processorName] === null ) {
            self::$_singleton[$processorName] = new CRM_Core_Payment_SecureTrading( $mode, $paymentProcessor );
        }
        return self::$_singleton[$processorName];
    }

    /** 
     * This function checks to see if we have the right config values 
     * 
     * @return string the error message if any 
     * @public 
     */ 
    function checkConfig( ) {
		$config = CRM_Core_Config::singleton();

		$error = array();
	 
		if (empty($this->_paymentProcessor['user_name'])) {
		  $error[] = ts('The "User ID" is not set in the Administer CiviCRM Payment Processor.');
		}
	 
		if (!empty($error)) {
		  return implode('<p>', $error);
		}
		else {
		  return NULL;
		}
	}
	 /**
     * This function collects all the information from a web/api form and invokes
     * the relevant payment processor specific functions to perform the transaction
     *
     * @param  array $params assoc array of input parameters for this transaction
     *
     * @return array the result in an nice formatted array (or an error object)
     * @public
     */
    function doDirectPayment( &$params ) {
        $args = array( );print_r($params);print_r($this->_paymentProcessor['user_name'] ); echo 'ding';die;

        //print_r("asdS");exit;
        $this->initialize( $args, 'DoDirectPayment' );

        //$args['paymentAction']  = $params['payment_action'];
		//$args['Freq']			  = $params['frequency']; // **** Confirm if we want to pass frequency
        //$args['Amount']         = $params['amount'];
        //$args['currencyCode']   = $params['currencyID'];
        //$args['invnum']         = $params['invoiceID'];
        //$args['ipaddress']      = $params['ip_address'];
        //$args['creditCardType'] = $params['credit_card_type'];
        //$args['acct']           = $params['credit_card_number'];
        //$args['expDate']        = sprintf( '%02d', $params['month'] ) . $params['year'];
        //$args['cvv2']           = $params['cvv2'];
		$args['title']		   	= $params['title']; //**** Confirm how field populated
        $args['firstname']      = $params['first_name'];
        $args['surname']        = $params['last_name'];
        $args['email']          = $params['email'];
		$args['accountholder']	= $params['title'].' '.$params['first_name'].' '.$params['last_name'];
        $args['address1']       = $params['street_address'];
        $args['town']           = $params['city'];
        $args['county']         = $params['state_province'];
        $args['country']        = $params['country'];
        $args['postcode']       = $params['postal_code'];

        // Allow further manipulation of the arguments via custom hooks ..
        CRM_Utils_Hook::alterPaymentProcessorParams( $this, $params, $args );

        $result = $this->invokeAPI( $args );

        if ( is_a( $result, 'CRM_Core_Error' ) ) {  
            return $result;  
        }

        /* Success */
		/* AG - No Result returned from Rapidata therefore assume amount entered is transaction amt */
		/***** How do we pass back frequency *****/
        $params['trxn_id']        = $params['contactID']; 	//$result['transactionid'];
        $params['gross_amount']   = $params['amount']; 		//$result['amt'];
        return $params;
    }
    function doTransferCheckout( &$params, $component = 'contribute' ) {
        $config = CRM_Core_Config::singleton( );  
		
        if ( $component != 'contribute' && $component != 'event') {
            CRM_Core_Error::fatal( ts( 'Componentr is invalid' ) );
        }
	
        $notifyURL = 
            $config->userFrameworkResourceURL . 
            "extern/ipn.php?reset=1&contactID={$params['contactID']}" .
            "&contributionID={$params['contributionID']}" .
            "&module={$component}";

		$membershipID = CRM_Utils_Array::value( 'membershipID', $params );
		if ( $membershipID ) {
			$notifyURL .= "&membershipID=$membershipID";
		}
		$relatedContactID = CRM_Utils_Array::value( 'related_contact', $params );
		if ( $relatedContactID ) {
			$notifyURL .= "&relatedContactID=$relatedContactID";

			$onBehalfDupeAlert = CRM_Utils_Array::value( 'onbehalf_dupe_alert', $params );
			if ( $onBehalfDupeAlert ) {
				$notifyURL .= "&onBehalfDupeAlert=$onBehalfDupeAlert";
			}
		}
        $url    = 'civicrm/contribute/transact';
        $cancel = '_qf_Main_display';
        $returnURL = CRM_Utils_System::url( $url,
                                            "_qf_ThankYou_display=1&qfKey={$params['qfKey']}",
                                            true, null, false );
        $cancelURL = CRM_Utils_System::url( $url,
                                            "$cancel=1&cancel=1&qfKey={$params['qfKey']}",
                                            true, null, false );

        // ensure that the returnURL is absolute.
        if ( substr( $returnURL, 0, 4 ) != 'http' ) {
            require_once 'CRM/Utils/System.php';
            $fixUrl = CRM_Utils_System::url("civicrm/admin/setting/url", '&reset=1');
            CRM_Core_Error::fatal( ts( 'Sending a relative URL to RSM is erroneous. Please make your resource URL (in <a href="%1">Administer CiviCRM &raquo; Global Settings &raquo; Resource URLs</a> ) complete.', array( 1 => $fixUrl ) ) );
        }
          
        if ($params['contributionTypeID'] == 2) {
            require_once 'api/api.php';
            //get membership duration
           if($params['membershipID']){
               $memParams = array ('version' => '3','page' =>'CiviCRM', 'q' =>'civicrm/ajax/rest', 'sequential' =>'1', 'id' =>$params['membershipID']);
               $memDetails = civicrm_api("Membership","get", $memParams );
               $memTypeParams = array ('version' => '3','page' =>'CiviCRM', 'q' =>'civicrm/ajax/rest', 'sequential' =>'1', 'id' => $memDetails['values'][0]['membership_type_id']);
            }else{  
               $memTypeParams = array ('version' => '3','page' =>'CiviCRM', 'q' =>'civicrm/ajax/rest', 'sequential' =>'1', 'id' =>$params['selectMembership']);
            }
            $memTypeDetails = civicrm_api("MembershipType","get", $memTypeParams );
            $mem_duration_unit = $memTypeDetails['values'][0]['duration_unit'];
            $mem_duration_interval = $memTypeDetails['values'][0]['duration_interval'];   
              
            if ($mem_duration_unit == 'month') {
                if ($mem_duration_interval == '3') {
                    $repeatValue = 3;  
                } elseif ($mem_duration_interval == '6'){
                    $repeatValue = 6;  
                } else {
                    $repeatValue = 1;   
                }            
            } elseif ($mem_duration_unit == 'year') {
                $repeatValue = 12;           
            }
        } else {
            $repeatValue = 1;
        }
        
        $contactDetails = civicrm_api("Contact","get", array ('version' => '3','sequential' =>'1', 'contact_id' =>$params['contactID']));
        $contact = $contactDetails['values'][0]; 
  
        $ST_campaignID = $this->_paymentProcessor['user_name']; 
        $individual_prefix = substr($contact['prefix'],0,-1);           
		$STParams = array(      
								   
								 'sitereference'     	=> $this->_paymentProcessor['user_name'],
								 'currencyiso3a'     	=> $params['currencyID'],
								 'mainamount'        	=> $params['amount'] . '.00',
								 'version'  	 	 	=> 1,
								 'billingpremise'  	 	=> $contact['street_address'],
								 'billingstreet'   	 	=> $contact['street_address'],
								 'billingtown'		 	=> $contact['city'],
								 'billingcounty'	 	=> $contact['state_province'], 								 
								 'billingpostcode'	 	=> $contact['postal_code'],  
								 'orderreference'		=> $contact['external_identifier'],  
								 'billingprefixname'   	=> $individual_prefix,
								 'billingfirstname'   	=> $contact['first_name'],
								 'billinglastname'  	=> $contact['last_name'],
								 'billingcountryiso2a'  => $contact['country'],
								 'billingemail'   		=> $contact['email'],
								 //'accountholder'      => $individual_prefix.' '.$contact['first_name'].' '.$contact['last_name'],
								 'contribution_id'  	=> $params['contributionID'],
								 'qfkey'     			=> $params['qfKey'],	
								 'campaign'      		=> $ST_campaignID,	
                                 'purchasedesc' 		=> $params['description'],
                                 'repeat'        		=> $repeatValue,
                                 'donationfix'  		=> '0',
                                 'donorref'      		=> $params['contactID'],
                                );
                                // print_r($STParams);die;
        require_once 'CRM/Core/Session.php';
        $session = CRM_Core_Session::singleton();
        $session->set('qfKey1' , $params['qfKey']);                        
                                
        // Allow further manipulation of the arguments via custom hooks ..
        CRM_Utils_Hook::alterPaymentProcessorParams( $this, $params, $STParams );          
        $uri = '';
        foreach ( $STParams as $key => $value ) {
            if ( $value === null ) {
                continue;
            }
            $value = urlencode( $value );
            if ( $key == 'return' ||
                 $key == 'cancel_return' ||
                 $key == 'notify_url' ) {
                $value = str_replace( '%2F', '/', $value );
            }
            $uri .= "&{$key}={$value}";
        }

        $uri = substr( $uri, 1 );
        $url = $this->_paymentProcessor['url_site'];
        $sub = empty( $params['is_recur'] ) ? 'xclick' : 'subscriptions';
    
		$STURL = "{$url}?$uri";
  
// $st_url = 'https://payments.securetrading.net/process/payments/choice?siterefernce=username&currencyiso3a=USD&mainamount=100.00&version=1';
$action_url = $this->_paymentProcessor['url_site']; 
?>
      <html>
      <body>
      <form method="POST" name="securetrading" action="<?php echo $action_url ; ?>" id="parameters">            
      <?php
          foreach($STParams as $key => $val){
            echo '<input  name="'.$key.'" type="hidden" value="'.$val.'" />';
          }  
		echo '<input type="hidden" name="_charset_" />';
      ?>       
     </form>
    
    <script type="text/javascript">
    document.forms['securetrading'].submit();
    </script>
<?php
    
        echo "Redirecting... please wait";
        require_once 'CRM/Core/Session.php';
        CRM_Core_Session::storeSessionObjects( );
        exit;
 }
}