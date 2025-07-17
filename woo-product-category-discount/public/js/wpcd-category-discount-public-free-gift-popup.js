jQuery(function ($) {
  function renderGiftCards(products) {
    return products
      .map((product) => {
        return `
          <div class="gift-card" data-product-id="${product.id}">
            <img src="${product.image}" style="width:100px;height:100px;object-fit:contain;" />
            <h4 style="font-size:14px;">${product.name}</h4>
            <div style="font-size:13px;">${product.price}</div>
          </div>
        `;
      })
      .join("");
  }

  const modalHTML = `
    <div id="gift-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9998;"></div>
    <div id="free-gift-modal" style="
      position:fixed;
      top:10%;
      left:50%;
      transform:translateX(-50%);
      background:#fff;
      padding:20px;
      border:1px solid #ccc;
      z-index:9999;
      max-width:700px;
      width:90%;
      max-height:80%;
      overflow-y:auto;
      box-shadow:0 0 15px rgba(0,0,0,0.3);
      border-radius:8px;
    ">
      <h3 style="margin-top:0;margin-bottom:10px;">${
        wpcd_free_gift_popup.i18n.free_gift_label
      }</h3>
      <div id="gift-options" style="
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        gap:15px;
        margin-bottom:20px;
      ">
        ${renderGiftCards(wpcd_free_gift_popup.gifts)}
      </div>
      <button id="gift-submit" style="padding:10px 20px;background:#0071a1;color:#fff;border:none;border-radius:4px;cursor:pointer;">${
        wpcd_free_gift_popup.i18n.add_to_cart
      }</button>
      <button id="gift-close" style="margin-left:10px;padding:10px 20px;border:1px solid #ccc;background:#f5f5f5;border-radius:4px;">${
        wpcd_free_gift_popup.i18n.close
      }</button>
    </div>
  `;

  $("body").append(modalHTML);

  $("#gift-close, #gift-overlay").on("click", function () {
    $("#free-gift-modal, #gift-overlay").remove();
  });

  $("#gift-submit").on("click", function () {
    $.ajax({
      url: wpcd_free_gift_popup.ajaxurl,
      method: "POST",
      data: {
        action: "wpcd_add_optional_gift_to_cart",
        nonce: wpcd_free_gift_popup.nonce,
      },
      success: function (response) {
        location.reload();
      },
    });
  });
});
