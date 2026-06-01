document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("[data-confirm]").forEach(function (element) {
    element.addEventListener("submit", function (event) {
      var message =
        element.getAttribute("data-confirm") || "Bạn có chắc không?";
      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });

    element.addEventListener("click", function (event) {
      if (element.tagName !== "A") {
        return;
      }
      var linkMessage =
        element.getAttribute("data-confirm") || "Bạn có chắc không?";
      if (!window.confirm(linkMessage)) {
        event.preventDefault();
      }
    });
  });

  document.querySelectorAll("[data-alert]").forEach(function (button) {
    button.addEventListener("click", function () {
      window.alert(button.getAttribute("data-alert"));
    });
  });
});
