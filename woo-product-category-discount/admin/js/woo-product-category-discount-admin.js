let pauseStatusCheckUntil = {};
function statusCheck(el) {
  if (confirm(wpcd.message)) {
    const td = el.closest("td");
    const viewLink = td.querySelector(".view > a");

    if (viewLink) {
      viewLink.click();
    }
  }
}

jQuery(document).on("change", ".toggle-status", function () {
  let checkbox = jQuery(this);
  let postId = checkbox.data("id");
  let status = checkbox.is(":checked") ? 1 : 0;
  let _this = jQuery(this);

  jQuery.ajax({
    url: wpcd.ajax_url,
    type: "POST",
    data: {
      action: "update_discount_status",
      discount_id: postId,
      nonce: wpcd.update_discount_nonce,
    },
    beforeSend: function () {
      _this.parent().hide();
      _this.parent().parent().append(wpcd.loader_img);
      jQuery(".toggle-status.wpcd-status").attr("disabled", "disabled");
      _this
        .parent()
        .parent()
        .find(".discount-status")
        .after(`<span class="overlay">${wpcd.processing_message}</span>`);
    },
    success: function (response) {
      if (!response.success) {
        alert(response.data.message ?? wpcd.error);
      } else {
        if (response.data.html) {
          _this.parent().parent().html(response.data.html);
          _this.parent().parent().find(".loader").remove();
          jQuery(".toggle-status.wpcd-status").removeAttr("disabled");
          jQuery(".overlay").remove();
        } else {
          if (response.data.next == "remove") {
            removeDiscount(postId, response.data.discount_data).then(
              (discountRemoved) => {
                if (discountRemoved) {
                  _this.parent().parent().find(".loader").remove();
                  _this.parent().show();
                  _this
                    .parent()
                    .parent()
                    .find(".discount-status")
                    .text(wpcd.inactive);
                  jQuery(".toggle-status.wpcd-status").removeAttr("disabled");
                  jQuery(".overlay").remove();
                }
              }
            );
          } else {
            applyDiscount(postId).then((discountApplied) => {
              if (discountApplied) {
                _this.parent().parent().find(".loader").remove();
                _this.parent().show();
                _this
                  .parent()
                  .parent()
                  .find(".discount-status")
                  .text(wpcd.active);
                jQuery(".toggle-status.wpcd-status").removeAttr("disabled");
                jQuery(".overlay").remove();
              }
            });
          }
        }
      }
    },
    error: function (xhr, status, error) {
      alert(xhr.responseJSON.data.message ?? wpcd.error);
    },
  });
});

function removeDiscount(discountId, discountData, processedChunks = 0) {
  const payloadData = {
    ...discountData,
    processed_chunks: processedChunks,
  };

  return jQuery
    .ajax({
      url: wpcd.api_url + "remove-discount/",
      method: "POST",
      data: JSON.stringify({
        discount_id: discountId,
        discount_data: payloadData,
      }),
      contentType: "application/json",
      headers: {
        "X-WP-Nonce": wpcd.wp_rest_nonce,
      },
    })
    .then(function (res) {
      const processed = res?.data?.processed ?? res?.processed ?? 0;
      if (processed >= 100) {
        return true;
      } else {
        return removeDiscount(discountId, discountData, processedChunks + 1);
      }
    })
    .catch(function (err) {
      console.error("Error removing discount:", err);
      return false;
    });
}

function applyDiscount(discountId) {
  return jQuery
    .ajax({
      url: wpcd.api_url + "process-discount/",
      method: "POST",
      data: JSON.stringify({ discount_id: discountId }),
      contentType: "application/json",
      headers: {
        "X-WP-Nonce": wpcd.wp_rest_nonce,
      },
    })
    .then(function (res) {
      const processed = res?.data?.processed ?? res?.processed ?? 0;
      if (processed >= 100) {
        return true;
      } else {
        return applyDiscount(discountId);
      }
    })
    .catch(function (err) {
      console.error("Error applying discount:", err);
      return false;
    });
}

jQuery(function ($) {
  $(".view-more-btn").on("click", function (e) {
    e.preventDefault();
    var $taxonomy = $(this).closest(".taxonomy-details");
    $taxonomy
      .find(".wpcd-hidden, .wpcd-shown")
      .toggleClass("wpcd-hidden wpcd-shown");
    $(this).text(
      $(this).text() === wpcd.view_more ? wpcd.view_less : wpcd.view_more
    );
  });
});
