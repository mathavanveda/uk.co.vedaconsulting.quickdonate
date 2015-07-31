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
    }

    $pageConfig = civicrm_api3('ContributionPage', 'getsingle', array(
      'id' => $this->_id,
    ));
    if (is_array($pageConfig['payment_processor'])) {
      CRM_Core_Error::fatal(ts('Multiple payment processors not supported with quick donate.'));
    }
    $processorDetails = CRM_Financial_BAO_PaymentProcessor::getPayment($pageConfig['payment_processor'], 'live');

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
        // Send receipt
        civicrm_api3('contribution', 'sendconfirmation', array('id' => $contributionID) + $pageConfig);
        CRM_Utils_System::setTitle(ts('Thank you'));
        $this->assign('status', 'thankyou');

        $profileID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'Supporter Profile', 'id', 'title');
        // Link (button) for users to create their own Personal Campaign page
        if ($profileID && !$session->get('userID')) {
          $linkTextUrl = CRM_Utils_System::url('civicrm/profile/create',
            "gid={$profileID}&reset=1",
            FALSE, NULL, TRUE
          );
          $this->assign('linkTextUrl', $linkTextUrl);
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
}
