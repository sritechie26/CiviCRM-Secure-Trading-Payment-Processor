<?php

/*
  +--------------------------------------------------------------------+
  | CiviCRM version 4.3                                                |
  +--------------------------------------------------------------------+
  | Copyright CiviCRM LLC (c) 2004-2013                                |
  +--------------------------------------------------------------------+
  | This file is a part of CiviCRM.                                    |
  |                                                                    |
  | CiviCRM is free software; you can copy, modify, and distribute it  |
  | under the terms of the GNU Affero General Public License           |
  | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
  |                                                                    |
  | CiviCRM is distributed in the hope that it will be useful, but     |
  | WITHOUT ANY WARRANTY; without even the implied warranty of         |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
  | See the GNU Affero General Public License for more details.        |
  |                                                                    |
  | You should have received a copy of the GNU Affero General Public   |
  | License and the CiviCRM Licensing Exception along                  |
  | with this program; if not, contact CiviCRM LLC                     |
  | at info[AT]civicrm[DOT]org. If you have questions about the        |
  | GNU Affero General Public License or the licensing of CiviCRM,     |
  | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
  +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

define( 'CIVICRM_CONTRIBUTION_DD_COLLECTION_DAY', 'custom_30' );
define( 'CIVICRM_CONTRIBUTION_DD_COLLECTION_CODE', 'custom_116' );
define( 'CIVICRM_DIRECT_DEBIT_STANDARD_PAYMENT_ACTIVITY_ID', 53 );
define( 'CIVICRM_DIRECT_DEBIT_PAYMENT_INSTRUMENT_ID', 8 );
define( 'CIVICRM_CUSTOM_BANK_DETAILS_TABLE', 'civicrm_value_bank_details' );


function civicrm_SecureTrading_civicrm_config( &$config ) {
    $template =& CRM_Core_Smarty::singleton( );
    
    $st_Root = dirname( __FILE__ );
    
    $secureTradingDir = $st_Root . DIRECTORY_SEPARATOR . 'templates';
    
    if ( is_array( $template->template_dir ) ) {
        array_unshift( $template->template_dir, $secureTradingDir );
    } else {
        $template->template_dir = array( $secureTradingDir, $template->template_dir );
    }
    
    // also fix php include path
    $include_path = $st_Root . PATH_SEPARATOR . get_include_path( );
    set_include_path( $include_path );
	
}

function civicrm_SecureTrading_civicrm_install(){
    $query = "INSERT INTO `civicrm_payment_processor_type` (`name`, `title`, `description`, `is_active`, `is_default`, `user_name_label`, `password_label`, `signature_label`, `subject_label`, `class_name`, `url_site_default`, `url_api_default`, `url_recur_default`, `url_button_default`, `url_site_test_default`, `url_api_test_default`, `url_recur_test_default`, `url_button_test_default`, `billing_mode`, `is_recur`, `payment_type`) VALUES
             ('SecureTrading', 'SecureTrading', 'Secure Trading payment processor', 1, 1, 'User Name', 'Password', 'Signature', 'Subject', 'Payment_SecureTrading', '', NULL, '', NULL, '', NULL, '', NULL, 4, 0, 1)";

    CRM_Core_DAO::executeQuery($query);
}