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
class CRM_Core_Payment_SecureTradingIPN{

 static function processSecureTrading_postData($params) {
 
 //update the Contribution
 //take the contribution id and update it.. make sure whether it updating participant id in civicrm_participatn table. if not updating paricipant  then use API to update pariticpant as well.
 //after this redirect to thank you page
 //$returnURL = CRM_Utils_System::url( $url, "_qf_ThankYou_display=1&qfKey={$params['qfKey']}", true, null, false );
		 
	$contributionGetParams = array(
									'version' => 3,
									'id' => $params['contribution_id'],
									);
	$contributionGetResult = civicrm_api('Contribution', 'get', $contributionGetParams);
	$contact_id = $contributionGetResult['values'][$params['contribution_id']]['contact_id'];

	//updating contribution status to completed.
	 $contribParams = array(
	  'version' => 3,
	  'sequential' => 1,
	  'financial_type_id' => 4,
	  'contribution_status_id' => 1,
	  'contact_id' => $contact_id,
	  'total_amount' => $params['mainamount'],
	  'id' => $params['contribution_id'],
	  'trxn_id' => $params['transactionreference'],
	);
	$contribResult = civicrm_api('Contribution', 'create', $contribParams);
	print_r($contact_id);
	print_r($contribResult);
	//getting participant id
	$participantPaymentParams = array(
	  'version' => 3,
	  'sequential' => 1,
	  'contribution_id' =>$params['contribution_id'],
	);
	$participantPaymentResult = civicrm_api('ParticipantPayment', 'get', $participantPaymentParams);
	$participant_id = $participantPaymentResult['values'][0]['participant_id'];
	
	if($participant_id)
	{
		//get the event id
		$participantGetParams = array(
					  'version' => 3,
					  'id' => $participant_id,
					   );
		$participantGetParamsResult = civicrm_api('Participant', 'get', $participantGetParams);
		$event_id = $participantGetParamsResult['values'][$participant_id]['event_id'];
	
		//updating participant status to registered.
		$participantParams = array(
		  'version' => 3,
		  'sequential' => 1,
		  'event_id' => $event_id,
		  'contact_id' => $contact_id,
		  'id' => $participant_id,
		  'participant_status_id' => 1,
		);
		$participantResult = civicrm_api('Participant', 'create', $participantParams);
	
	}

	$redirectURL = CRM_Utils_System::url( 'civicrm/event/register', "_qf_ThankYou_display=1&qfKey={$params['qfkey']}", true, null, false );
	CRM_Utils_System::redirect( $redirectURL);
 }
}

