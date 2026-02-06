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

jQuery(document).on("click", ".terminate-progress-link", function (e) {
  e.preventDefault();
  let discount_id = jQuery(this).data("discount-id");
  let _this = jQuery(this);

  if (!discount_id) {
    alert(wpcd.error);
    return;
  }

  if (!confirm(wpcd.confirm_terminate)) return;

  jQuery.ajax({
    url: wpcd.ajax_url,
    type: "POST",
    data: {
      action: "terminate_discount_progress",
      discount_id: jQuery(this).data("discount-id"),
      nonce: wpcd.terminate_discount_nonce,
    },
    beforeSend: function () {
      _this
        .parents("td")
        .append(`<span class="overlay">${wpcd.processing_message}</span>`);
    },
    success: function (response) {
      if (!response.success) {
        alert(response.data.message ?? wpcd.error);
        jQuery(".overlay").remove();
      } else {
        location.reload();
      }
    },
    error: function (xhr, status, error) {
      alert(xhr.responseJSON.data.message ?? wpcd.error);
    },
  });
});

let processingDiscounts = new Set();
jQuery(document).on("change", ".toggle-status", function () {
  let checkbox = jQuery(this);
  let postId = checkbox.data("id");

  if (processingDiscounts.has(postId)) {
    return;
  }

  processingDiscounts.add(postId);

  jQuery.ajax({
    url: wpcd.ajax_url,
    type: "POST",
    data: {
      action: "update_discount_status",
      discount_id: postId,
      nonce: wpcd.update_discount_nonce,
    },
    beforeSend: function () {
      checkbox.parent().hide();
      checkbox.parent().parent().append(wpcd.loader_img);
      jQuery(".toggle-status.wpcd-status").attr("disabled", "disabled");
      checkbox
        .parent()
        .parent()
        .find(".discount-status")
        .after(`<span class="overlay">${wpcd.processing_message}</span>`);
    },
    success: function (response) {
      if (!response.success) {
        alert(response.data.message ?? wpcd.error);
        cleanup();
        return;
      }
      if (response.data.html) {
        checkbox.parent().parent().html(response.data.html);
        cleanup();
      } else {
        if (response.data.next == "remove") {
          removeDiscount(postId, response.data.discount_data).then(
            (discountRemoved) => {
              if (discountRemoved) {
                checkbox.parent().parent().find(".loader").remove();
                checkbox.parent().show();
                checkbox
                  .parent()
                  .parent()
                  .find(".discount-status")
                  .text(wpcd.inactive);
              }
              cleanup();
            }
          );
        } else {
          applyDiscount(postId).then((discountApplied) => {
            if (discountApplied) {
              checkbox.parent().parent().find(".loader").remove();
              checkbox.parent().show();
              checkbox
                .parent()
                .parent()
                .find(".discount-status")
                .text(wpcd.active);
            }
            cleanup();
          });
        }
      }
    },
    error: function (xhr, status, error) {
      alert(xhr.responseJSON.data.message ?? wpcd.error);
      cleanup();
    },
  });

  function cleanup() {
    jQuery(".toggle-status.wpcd-status").removeAttr("disabled");
    jQuery(".overlay").remove();
    checkbox.parent().show();
    checkbox.parent().parent().find(".loader").remove();
    processingDiscounts.delete(postId);
  }
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

  let visibleDiscount = [];
  $(".wpcd-status").each(function () {
    visibleDiscount.push($(this).data("id"));
  });

  let latestDiscountUpdateId = 0;
  setInterval(function () {
    let statusForDiscount = [];
    for (let i = 0; i < visibleDiscount.length; i++) {
      if (!processingDiscounts.has(visibleDiscount[i])) {
        statusForDiscount.push(visibleDiscount[i]);
      }
    }

    if (statusForDiscount.length > 0) {
      const requestId = Date.now();
      latestDiscountUpdateId = requestId;

      jQuery.ajax({
        url: wpcd.ajax_url,
        type: "POST",
        data: {
          action: "get_latest_discount_status",
          discount_ids: statusForDiscount,
          nonce: wpcd.fetch_discount_status_nonce,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            if (requestId !== latestDiscountUpdateId) {
              return;
            }

            let statuses = response.data.discount_statuses;
            Object.keys(statuses).forEach((id) => {
              const $el = $(`#toggle-status-${id}`);
              const $parent = $el.parent();

              const $target = $parent.hasClass("wp-list-toggle")
                ? $parent.parent()
                : $parent;

              $target.html(statuses[id]);
            });
          }
        },
      });
    }
  }, 10000);
});
