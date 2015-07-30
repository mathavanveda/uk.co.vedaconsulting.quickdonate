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
    CRM_Core_Error::debug_var('$_REQUEST', $_REQUEST);
    if (!empty($_REQUEST['stripeToken'])) {
      $donateConfig = $donatePage = civicrm_api3('ContributionPage', 'getsingle', array(
        'id' => 1,
      ));

      $contributionparams = $_REQUEST;
      try {

        $contributionparams['amount'] = 23;
        $contributionparams['total_amount'] = 23;
        $contributionparams['financial_type_id'] = 1;
        $contributionparams['contact_id'] = 202;
        $contributionparams['email'] = 'deepak@example.org';
        $contributionparams["payment_processor_id"] = 3;
        $contributionparams["stripe_token"] = $contributionparams['stripeToken'];
        $contributionparams["currencyID"] = $donateConfig['currency'];

        CRM_Core_Error::debug_var('$contributionparams', $contributionparams);
        $result = civicrm_api3('Contribution', 'transact', $contributionparams);
        CRM_Core_Error::debug_var('$result', $result);
      }
      catch (CiviCRM_API3_Exception $e) {
        $error = $e->getMessage();
        CRM_Core_Error::debug_var('$error', $error);
        $errorList['error'] = $error;
      }
    }
    else {
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
