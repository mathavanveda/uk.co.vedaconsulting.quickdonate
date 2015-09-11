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
<section class="donate section section--green">

  <div class="row">
    <div class="column two-thirds push-sixth l-one-whole">

      {if $status eq 'thankyou'}
      <div id="help">
        <div>{ts 1=$email}Your transaction has been processed successfully. An email receipt has also been sent to %1{/ts}</div>
        {if $linkTextUrl}
        <br />
        <div class="crm-section create_pcp_link-section">
          <a href="{$linkTextUrl}" title="{ts}Setup An Account{/ts}" class="button"><span>&raquo; {ts}Setup An Account{/ts}</span></a>
        </div><br /><br />
        {/if}
        {if $loginURL}
        <br />
        <div class="crm-section create_pcp_link-section">
          <a href="{$loginURL}" title="{ts}Login to Account{/ts}" class="button"><span>&raquo; {ts}Login to Account{/ts}</span></a>
        </div><br /><br />
        {/if}
      </div>
      {elseif $status eq 'quickdonate'}


      <div class="form form--donate" action="#">
          <!--            <h3>{$pageConfig.title}</h3>
          <h4>{$pageConfig.intro_text}</h4> -->
          <div class="field-group">
            <label for="donation-amount">Enter your donation amount:</label><br>
            <!--         <input type="text" id="donation-amount" class="field"> -->
            <span class="preinput">{$pageConfig.currency_symbol}</span>
            <input id="direct_donation_form_amount" name="total_amount" required="required" type="number" {if $pageConfig.min_amount} min="{$pageConfig.min_amount}" {/if} {if $pageConfig.max_amount} max="{$pageConfig.max_amount}" {/if} pattern="\d*" step="0.01" value="{$pageConfig.default_amount}">
            <span class="postinput">{$pageConfig.currency}</span>
              <!--                 <button id="btn--donate" class="btnblock" type="submit">Donate</button>-->
            <button id="customButton" class="customButton btn--donate button payment large stripe-donate-button btnblock" name="button" type="submit">Donate</button>
          </div>

          <div class="field-group">
            <label for="donation_type-gift" class="title">
              <input name="donation_form[gifting_to]" type="hidden" value="0">
              <!--                     <input class="radio radio--donatetype" id="donation_type-gift" type="checkbox" name="donation_type" value="gift">-->
              <input id="direct_donation_form_gifting_to" class="radio radio--donatetype" name="donation_form[gifting_to]" type="checkbox" value="1">
              I'd like to <span class="highlight">gift my donation</span>
            </label>
          </div>

          <div id="giftee" class="is-hidden">


          <fieldset class="crm-group honor_block-group field-group">
            <legend>Enter the Giftee's Details</legend>
            <div id="honorType" class="honoree-name-email-section">
              {include file="CRM/UF/Form/Block.tpl" fields=$honoreeProfileFields mode=8 prefix='honor'}
            </div>

          </fieldset>

          <div class="field-group">
            <button id="customButton2" class="payment large stripe-donate-button btnblock" type="submit">Give</button>
          </div>
        </div>




        <div class="field-group">
          <label for="donation_type-regular" class="title">
            <input name="donation_form[payment_monthly_subscription]" type="hidden" value="0">
            <input id="direct_donation_form_payment_monthly_subscription" class="radio radio--donatetype" name="donation_form[payment_monthly_subscription]" type="checkbox" value="1">
            <label for="direct_donation_form_payment_monthly_subscription"> I'd like to <span class="highlight">donate regularly</span></label>
          </label>
        </div>

      <div class="gift-aid field-group">
      <label for="donation_type-giftaid"  class="title">
        <input name="donation_form[gift_aid]" type="hidden" value="0">
        <input id="direct_donation_form_gift_aid" name="donation_form[gift_aid]"  class="checkbox checkbox--donatetype" type="checkbox" value="1">
        I would like to add <span class="highlight">Gift Aid</span></label>
        <span><p class="meta">Please say yes! The government will add money to your donation - we just need your address.</p>
      </div>


      <div id="giftaid" class="gift_aid_declaration is-hidden">
        <div class="first_name">
          <input name="contact_details[first_name]" type="hidden" value="">
          <label for="contact_details_first_name">First Name</label> &nbsp;&nbsp;&nbsp;&nbsp;
          <input id="contact_details_first_name" name="contact_details[first_name]" type="text" value="{$firstName}">
        </div>
        <div class="last_name">
          <input name="contact_details[last_name]" type="hidden" value="">
          <label for="contact_details_last_name">Last Name</label> &nbsp;&nbsp;&nbsp;&nbsp;
          <input id="contact_details_last_name" name="contact_details[last_name]" type="text" value="{$lastName}">
        </div>
        <div class="contact_street_address">
          <input name="contact_details[street_address]" type="hidden" value="">
          <label for="contact_details_street_address">Street Address</label> &nbsp;&nbsp;
          <input id="contact_details_street_address" name="contact_details[street_address]" type="text" value="{$streetAddress}">
        </div>
        <div class="contact_supplemental_address_1">
          <input name="contact_details[supplemental_address_1]" type="hidden" value="">
          <label for="contact_details_supplemental_address_1">Supplemental Address</label> &nbsp;&nbsp;
          <input id="contact_details_supplemental_address_1" name="contact_details[supplemental_address_1]" type="text" value="{$supplementalAddress1}">
        </div>
        <div class="contact_city">
          <input name="contact_details[city]" type="hidden" value="">
          <label for="contact_details_city">City</label> &nbsp;&nbsp;
          <input id="contact_details_city" name="contact_details[city]" type="text" value="{$city}">
        </div>
        <div class="contact_post_code">
          <input name="contact_details[post_code]" type="hidden" value="">
          <label for="contact_details_post_code">Post Code</label>
          <input id="contact_details_post_code" name="contact_details[post_code]" type="text" value="{$postCode}">
        </div>
        <div class="giftee_declaration_text">
         <p class="meta">I confirm I have paid or will pay an amount of Income Tax and/or Capital 
              Gains Tax for each tax year (6 April to 5 April) that is at least equal to the 
              amount of tax that all t
              he charities or Community Amateur Sports Clubs 
              (CASCs) that I donate to
              will reclaim on my gifts for 
              that tax year. I understand 
              that other taxes such as 
              VAT and Council Tax do not 
              qualify. I understand the 
              charity will reclaim 28p of tax on every 
              £1 that I gave up to 5 April 2008 and 
              will reclaim 25p of tax on every £1 that
               I give on or after 6 April 2008
          </p>
        </div>
        </div>

        <div id="regular" class="is-hidden">
          <button id="customButton3" class="customButton button payment large stripe-donate-button btnblock" name="button" type="submit">Donate</button>
          <a class=" payment large stripe-donate-button btnblock" href="{crmURL p='civicrm/contribute/transact' q="reset=1&id=4" fe='true'}" target="_blank">Direct Debit</a>
        </div>
          <div class="donate-by-check">
          <!-- a href="#donate-by-check">Give by check or stock</a-->
          <input type="hidden" id="stripe_token" name="stripe_token"/>
          <input type="hidden" id="email" name="email"/>
        </div>
      </form>
    </div>
  </div>
</section>
  
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
  
    $('.customButton').on('click', function(e) {
      // Open Checkout with further options
      var amount = ($("#direct_donation_form_amount").val() * 100).toFixed(2);
      
      handler.open({
        name:        'Cool Earth',
        description: 'Saving Rainforest',
        currency:    {/literal}'{$currency}'{literal},
        amount:      amount,
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
