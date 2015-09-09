<?php
/*
  +--------------------------------------------------------------------+
  | CiviCRM version 4.6                                                |
  +--------------------------------------------------------------------+
  | Copyright CiviCRM LLC (c) 2004-2015                                |
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
 * @copyright CiviCRM LLC (c) 2004-2015
 * $Id$
 *
 */

/**
 * This class generates form components for processing a Contribution
 *
 */
class CRM_QuickDonate_Form_QuickDonate extends CRM_Core_Form {
  
  CONST C_CUSTOM_GROUP_GIFT_AID = 'Gift_Aid';
  CONST C_CUSTOM_FIELD_GIFT_AID = 'Eligible_for_Gift_Aid';
  /**
   * Set variables up before form is built.
   *
   * @return void
   */
  public function preProcess() {
    $session   = CRM_Core_Session::singleton();
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this, TRUE);
    $contributionParams = $requestParams = $_REQUEST;

    $contactID = $session->get('userID');
    if ($contactID && empty($contributionParams['email'])) {
      $emailDetails = CRM_Contact_BAO_Contact_Location::getEmailDetails($contactID);
      if (!empty($emailDetails)) {
        $contributionParams['email'] = $emailDetails[1];
        // for email to be prefilled
        $this->assign('email', $emailDetails[1]);
      }
      
      $nameDetails = self::getNameAddressPostCodeByContactId($contactID);
      $this->assign('displayName', $nameDetails['displayName']);
      $this->assign('address'    , $nameDetails['address']);
      $this->assign('postCode'  , $nameDetails['post_code']);
    }

    $pageConfig = civicrm_api3('ContributionPage', 'getsingle', array(
      'id' => $this->_id,
    ));
    if (is_array($pageConfig['payment_processor'])) {
      CRM_Core_Error::fatal(ts('Multiple payment processors not supported with quick donate.'));
    }
    $processorDetails = CRM_Financial_BAO_PaymentProcessor::getPayment($pageConfig['payment_processor'], 'live');
    
    //MV: get amount details if other amount enabled for contribution page.
    if ($pageConfig['amount_block_is_active']) {
      $sql = "SELECT cpfv.amount, cpfv.is_default, cpfv.weight 
        FROM civicrm_price_field_value cpfv 
        INNER JOIN civicrm_price_field cpf ON (cpf.id = cpfv.price_field_id)
        INNER JOIN civicrm_price_set_entity cpse ON (cpse.price_set_id = cpf.price_set_id)
        WHERE cpse.entity_id = %1 AND cpf.name = 'contribution_amount'
      ";
      $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($pageConfig['id'], 'Integer')));
      $amount = 0;
      while ($dao->fetch()) {
        if ($dao->weight == 1 || $dao->is_default == 1) {
          $amount = $dao->amount;
        }
      }
    }
    $pageConfig['default_amount'] = $amount ? $amount : $pageConfig['min_amount'];
    

    $pageConfig['currency_symbol'] = CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_Currency', $pageConfig['currency'], 'symbol', 'name');
    $this->assign('pageConfig', $pageConfig);
    $this->assign('key', $processorDetails['password']);
    $this->assign('currency', strtolower($pageConfig['currency']));

    if (!empty($requestParams['stripe_token'])) { //FIXME: could go in post process
      if (!$contributionParams['email']) {
        CRM_Core_Error::fatal(ts('Email address is required'));
      }

      $contributionParams['financial_type_id'] = $pageConfig['financial_type_id'];
      if (!$contactID) {
        $contactParams = array(
          'email'        => $contributionParams['email'],
          'contact_type' => 'Individual'
        );
        $dedupeParams = CRM_Dedupe_Finder::formatParams($contactParams, 'Individual');
        $dedupeParams['check_permission'] = FALSE;
        $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual');
        // if we find more than one contact, use the first one
        $contactID = CRM_Utils_Array::value(0, $ids);
        if (!$contactID) {
          $cont = civicrm_api3('Contact', 'create', $contactParams);
          $contactID = $cont['id'];
        }
      }
      $contributionParams['contact_id'] = $contactID;
      $contributionParams['payment_processor_id'] = $pageConfig['payment_processor'];
      $contributionParams['currencyID'] = $pageConfig['currency'];
      
      //gift aid 
      //get gift aid custom field id 
      $cfId = self::getcustomFieldIdbyGroupAndFieldName( self::C_CUSTOM_GROUP_GIFT_AID, self::C_CUSTOM_FIELD_GIFT_AID );
      
      if ($cfId && $contributionParams['donation_form']['gift_aid']) {
        $contributionParams["custom_{$cfId}"] = 1;
      }
      //gift aid end
      
      //campaign
      if ($pageConfig['campaign_id']) {
        $contributionParams['campaign_id'] = $pageConfig['campaign_id'];
      }
      
      if ($contributionParams['donation_form']['payment_monthly_subscription']) {
        $contributionParams["recur_frequency_unit"] = 'month';
        if ( !empty($pageConfig['recur_frequency_unit']) ) { 
          $contributionParams["recur_frequency_unit"] = $pageConfig['recur_frequency_unit'];
        }
        
        //recur Params, 
        $recurParams = array('contact_id' => $contactID);
        $recurParams['start_date']          = $recurParams['create_date'] = $recurParams['modified_date'] = date('YmdHis');
        $recurParams['amount']              = CRM_Utils_Array::value('total_amount', $contributionParams);
        $recurParams['auto_renew']          = CRM_Utils_Array::value('auto_renew', $pageConfig);
        $recurParams['frequency_unit']      = CRM_Utils_Array::value('recur_frequency_unit', $contributionParams);
        $recurParams['frequency_interval']  = 1;
        $recurParams['installments']        = CRM_Utils_Array::value('installments', $pageConfig);
        $recurParams['financial_type_id']   = CRM_Utils_Array::value('financial_type_id', $pageConfig);
        $recurParams['currency']            = CRM_Utils_Array::value('currency', $pageConfig);
        $recurParams['is_test']             = 0;
        $recurParams['payment_processor_id'] = CRM_Utils_Array::value('payment_processor', $pageConfig);
        $recurParams['is_email_receipt']    = CRM_Utils_Array::value('is_email_receipt', $pageConfig);
        $recurParams['contribution_status_id'] = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Pending');
        $recurParams['campaign_id']         = CRM_Utils_Array::value('campaign_id', $pageConfig);;
        
        if ($pageConfig['is_monetary']) {
          $recurParams['payment_instrument_id'] = 1;
        }
        $trxnId = $contactID."/".$recurParams['amount']."/".$recurParams['start_date'];
        $recurParams['invoice_id']          = $recurParams['trxn_id'] = $trxnId;
        $contributionParams['invoice_id']   = $contributionParams['trxn_id'] = $trxnId;
        
        $recurring = CRM_Contribute_BAO_ContributionRecur::add($recurParams);
        $contributionParams['contribution_recur_id'] = $recurring->id;
      }
      
      try {
        $result = civicrm_api3('Contribution', 'transact', $contributionParams);
      }
      catch (CiviCRM_API3_Exception $e) {
        $error = $e->getMessage();
        $this->assign('error', $error);
        CRM_Utils_System::setTitle(ts('Oops! There was a problem'));
      }

      
      if (!empty($result['error'])) {
        $this->assign('error', $result['error']);
        CRM_Utils_System::setTitle(ts('Oops! There was a problem'));
      }
      else if ($result){
        $contributionID = $result['id'];
        $contactID = $result['values'][$contributionID]['contact_id'];
        // Send receipt
        civicrm_api3('contribution', 'sendconfirmation', array('id' => $contributionID) + $pageConfig);
        CRM_Utils_System::setTitle(ts('Thank you'));
        $this->assign('status', 'thankyou');

        $profileID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'Supporter Profile', 'id', 'title');
        // Link (button) for users to create their own Personal Campaign page
        if ($profileID && !$session->get('userID')) {
          $ufId  = CRM_Core_BAO_UFMatch::getUFId($contactID);
          if ($ufId) {
            $config = CRM_Core_Config::singleton();
            $loginURL = $config->userSystem->getLoginURL();
            $this->assign('loginURL', $loginURL);
          }
          else {
            $linkTextUrl = CRM_Utils_System::url('civicrm/profile/create',
              "gid={$profileID}&reset=1",
              FALSE, NULL, TRUE
            );
            $this->assign('linkTextUrl', $linkTextUrl);
          }
        }
        
        
        //redirect if the logged in user
        if ($session->get('userID')) {
          $urlParams = array(
            'pageId' => $this->_id,
            'component' => 'contribute',
            'reset' => 1,
          );
          $sql = "SELECT pcp.id FROM civicrm_pcp pcp 
          INNER JOIN  civicrm_pcp_block cpb ON (cpb.id = pcp.pcp_block_id)
          WHERE cpb.entity_id = %1 AND pcp.contact_id = %2
          ";
          $sqlparams = array(
            1 => array($pageConfig['id'], 'Integer'),
            2 => array($contactID, 'Integer'),
          );
          
          $pcpId = CRM_Core_DAO::singleValueQuery($sql, $sqlparams); 
          if ($pcpId) {
            $urlParams['id'] = $pcpId;
          }
          $url = CRM_Utils_System::url('civicrm/pcp/setup', $urlParams);
          CRM_Utils_System::redirect($url);
        }
      }
    }
    else if (!empty($requestParams['_qf_Main_display'])) {
      //$this->assign('error', $error);
      CRM_Utils_System::setTitle(ts('Oops! There was a problem'));
    }
    else {
      $this->assign('status', 'quickdonate');
      CRM_Core_Resources::singleton()
        ->addStyleFile('uk.co.vedaconsulting.quickdonate', 'css/quickdonatebox.css');
    }
  }

  /**
   * Build the form object.
   *
   * @return void
   */
  public function buildQuickForm() {
  }
  
  static function getNameAddressPostCodeByContactId($contactID) {
    if (empty($contactID)) {
      return array();
    }
    $returnArray['address']     = $returnArray['post_code'] = null;
    $returnArray['displayName'] = CRM_Contact_BAO_Contact::displayName($contactID);
    
    //address
    $result = civicrm_api3('Address', 'get', array(
      'sequential' => 1,
      'is_primary' => 1,
      'contact_id' => $contactID,
    ));
    if ($result['count'] == 1) {
      $address = array( $result['values'][0]['street_address'], $result['values'][0]['city']);
      $returnArray['address'] = implode(', ', $address);
      $returnArray['post_code'] = $result['values'][0]['postal_code'];
    }
    
    return $returnArray;
  }
  
  static function getcustomFieldIdbyGroupAndFieldName( $cgName, $cfName ) {
    if (empty($cgName) || empty($cfName)) {
      return NULL;
    }
    
    $sqlCF = "SELECT cf.id 
    FROM civicrm_custom_field cf 
    INNER JOIN civicrm_custom_group cg ON (cg.id = cf.custom_group_id) 
    WHERE cg.name = %1 AND cf.name = %2";
    $sqlCFParams = array(
      1 => array($cgName, 'String'),
      2 => array($cfName, 'String'),
    );
    return CRM_Core_DAO::singleValueQuery($sqlCF, $sqlCFParams);
  }
}
