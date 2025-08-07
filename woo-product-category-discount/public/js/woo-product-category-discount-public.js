jQuery(function ($) {
  if (typeof wp !== "undefined" && wp.hooks) {
    wp.hooks.addAction(
      "experimental__woocommerce_blocks-cart-set-item-quantity",
      "core/i18n",
      function (name, functionName, callback, priority) {
        setTimeout(function () {
          window.location.reload();
        }, 3000);
      },
      10
    );

    wp.hooks.addAction(
      "experimental__woocommerce_blocks-cart-remove-item",
      "core/i18n",
      function (name, functionName, callback, priority) {
        setTimeout(function () {
          window.location.reload();
        }, 3000);
      },
      10
    );
  }

  if (typeof window.wc !== "undefined" && window.wc.blocksCheckout) {
    const { registerCheckoutFilters } = window.wc.blocksCheckout;
    const wpcdCartItemClass = (defaultValue, extensions, args) => {
      const key = "_free_gift";
      const value = "yes";
      if (args && args.cartItem && args.cartItem.item_data) {
        if (WPCDHasKeyValuePair(args.cartItem.item_data, key, value)) {
          return defaultValue + "wpcd-free-gift-class";
        }
      }
      return defaultValue;
    };
    registerCheckoutFilters("woo-product-category-discount", {
      cartItemClass: wpcdCartItemClass,
    });
  }

  function WPCDHasKeyValuePair(array, key, value) {
    for (let i = 0; i < array.length; i++) {
      if (array[i].key === key && array[i].value === value) {
        return true;
      }
    }
    return false;
  }
});
