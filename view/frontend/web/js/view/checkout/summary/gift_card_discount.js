define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, totals) {
    'use strict';

    return Component.extend({
        totals: quote.getTotals(),
        defaults: {
            template: 'Gifty_GiftCard/checkout/summary/gift_card_discount'
        },
        isGiftCardDiscountDisplayed: function () {
            return true;
        },
        getGiftCardTitle: function () {
            return '(Gift Card Title)'
        },
        getValue: function () {
            var price = 0;

            if (this.totals()) {
                price = totals.getSegment('gifty_gift_card_discount').value;
            }

            return this.getFormattedPrice(price);
        }
    });
});