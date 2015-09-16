/**
 * Ash_ValidateCreditCard
 *
 * Detects and validates credit card numbers with the help
 * of the jQuery Credit Card Validator plugin.
 *
 * The detected card type is populated to Magento's <paymentcode>_cc_type form
 * field, which we customized from a select field to a hidden input element.
 */
function detectAndValidateCreditCard(gatewayCode, acceptedCardsStr) {
  var card_number_field_id  = "#" + gatewayCode + "_cc_number"
    , card_type_field_id    = "#" + gatewayCode + "_cc_type"
    , card_number_field     = jQuery(card_number_field_id)
    , card_type_field       = jQuery(card_type_field_id)
    , accepted_cards        = acceptedCardsStr.split(',')
    , cards                 = jQuery("." + gatewayCode + ".cards li")
    , chosen_card;

  card_number_field.validateCreditCard(function(e) {
    if (e.card_type == null) {
      // turn all cards back on
      cards.removeClass("off");
      // remove valid class from cc_number field
      card_number_field.removeClass("valid");
      // remove previous value from cc_type field
      card_type_field.val('');
      return;
    }

    // find card li w/ matching class name (e.g., '.mastercard' or '.visa')
    chosen_card = jQuery("." + gatewayCode + ".cards ." + e.card_type.name);

    // turn other cards off
    cards.addClass("off");
    // but keep the chosen card on
    chosen_card.removeClass("off");

    // update the hidden field for the card_type
    card_type_field.val(chosen_card.data('card-type-code'));

    if (e.length_valid && e.luhn_valid) {
      return card_number_field.addClass("valid");
    } else {
      return card_number_field.removeClass("valid");
    }
  }, {
    accept: accepted_cards
  });

}


/**
 * Ash_ValidateCreditCard
 *
 * Detects credit card types from saved credit cards
 * based off of sibling hidden input fields
 *
 * DOES NOT populate the cc_type field (should already be populated)
 * DOES switch css classes for the credit card icons
 */
function detectSavedCreditCard(gatewayCode) {
    var card_type_field_id    = "#" + gatewayCode + "_saved_cc_type"
      , card_type_field       = jQuery(card_type_field_id)
      , cards                 = jQuery("." + gatewayCode + "_saved.cards li")
      , chosen_card
      , card_type_name;

    // turn all cards back on
    cards.removeClass("off");

    jQuery("[name='payment[ccsave_id]']").on('click', function(){
        card_type_name = jQuery(this).siblings("[name^='cctype']").val();
        // find card li w/ matching class name (e.g., '.mastercard' or '.visa')
        chosen_card = jQuery("." + gatewayCode + "_saved.cards ." + card_type_name);
        // turn other cards off
        cards.addClass("off");
        // but keep the chosen card on
        chosen_card.removeClass("off");
    });

    // turn the cards off if user is entering a new credit card (separate nested form)
    jQuery("#" + gatewayCode + "_cc_new").on('click', function(){
        cards.addClass('off');
    });

}
