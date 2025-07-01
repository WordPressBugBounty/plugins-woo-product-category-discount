jQuery(function ($) {
  $("#discount_type").on("change", function () {
    if ($(this).val() === "taxonomy") {
      $("#taxonomy-options").slideDown();
    } else {
      $("#taxonomy-options").slideUp();
    }
  });

  $("#estimate-products").on("click", function () {
    let data = {
      action: "wpcd_estimate_products",
      taxonomy: $("#taxonomy").val(),
      match_type: $("#match_type").val(),
    };

    $("#product-count-result").text("Estimating...");
    $.post(ajaxurl, data, function (response) {
      if (response.success) {
        $("#product-count-result").text(
          "Roughly " + response.data.count + " products match."
        );
      } else {
        $("#product-count-result").text("Could not estimate products.");
      }
    });
  });
});
