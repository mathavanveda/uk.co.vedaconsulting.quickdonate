{*
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
*}

{if $status eq 'thankyou'}
  <div id="help">
    <div>{ts 1=$email}Your transaction has been processed successfully. An email receipt has also been sent to %1{/ts}</div>
    {if $linkTextUrl}
      <br />
      <div class="crm-section create_pcp_link-section">
        <a href="{$linkTextUrl}" title="{ts}Setup An Account{/ts}" class="button"><span>&raquo; {ts}Setup An Account{/ts}</span></a>
      </div><br /><br />
    {/if}
  </div>
{elseif $status eq 'quickdonate'}
  {literal}
  <div class="quick-donate-box">
    <h3>Save an Acre</h3>
    <h4>100% funds rainforest projects</h4>
    <div class="donate-amount">
      <span class="preinput">£</span>
      <input id="direct_donation_form_amount" name="total_amount" required="required" type="number" value="40">
      <span class="postinput">GBP</span>
    </div>
    <div class="monthly-subscription">
      <input name="donation_form[payment_monthly_subscription]" type="hidden" value="0">
      <input id="direct_donation_form_payment_monthly_subscription" name="donation_form[payment_monthly_subscription]" type="checkbox" value="1">
      <label for="direct_donation_form_payment_monthly_subscription">Make this a recurring monthly gift</label>
    </div>
    <div class="donate-buttons">
      <button id="customButton" class="button payment large stripe-donate-button" name="button" type="submit">Give by <i class="icn credit-card">Credit Card</i></button>
      <button class="button payment large paypal-donate-button right" name="button" type="submit">Give by <i class="icn paypal">Direct Debit</i></button>
    </div>
    <div class="donate-by-check">
      <!-- a href="#donate-by-check">Give by check or stock</a-->
      <input type="hidden" id="stripe_token" name="stripe_token"/>
      <input type="hidden" id="email" name="email"/>
    </div>
  </div>
  {/literal}
  
  {literal}
  <script src="https://checkout.stripe.com/checkout.js"></script>
  
  <script>
  CRM.$(function($) {
    var handler = StripeCheckout.configure({
      key:   {/literal}'{$key}'{literal},
      image: 'http://d46.demo.civicrm.org/sites/all/modules/civicrm/i/smallLogo.png',
      token: function(token) {
        // Use the token to create the charge with a server-side script.
        // You can access the token ID with `token.id`
        $("#stripe_token").val(token.id);
        $("#email").val(token.email);
        $('#QuickDonate').submit();
      }
    });
  
    $('#customButton').on('click', function(e) {
      // Open Checkout with further options
      handler.open({
        name:        'Cool Earth',
        description: 'Saving Rainforest',
        currency:    {/literal}'{$currency}'{literal},
        amount:      $("#direct_donation_form_amount").val() * 100,
        email:       {/literal}'{$email}'{literal}
      });
      e.preventDefault();
    });
  
    // Close Checkout on page navigation
    $(window).on('popstate', function() {
      handler.close();
    });
  });
  </script>
  {/literal}
{else}
  {$error}
{/if}
