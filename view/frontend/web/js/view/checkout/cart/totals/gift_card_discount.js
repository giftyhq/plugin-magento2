define([
    'Gifty_GiftCard/js/view/checkout/summary/gift_card_discount',
], function (Component) {
    'use strict';

    return Component.extend({

        /**
         * @override
         */
        isGiftCardDiscountDisplayed: function () {
            return true;
        }
    });
});