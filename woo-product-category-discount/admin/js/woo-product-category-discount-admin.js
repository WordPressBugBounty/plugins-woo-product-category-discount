function statusCheck(el) {
  if (confirm(wpcd.message)) {
    const td = el.closest("td");
    const viewLink = td.querySelector(".view > a");

    if (viewLink) {
      viewLink.click();
    }
  }
}
