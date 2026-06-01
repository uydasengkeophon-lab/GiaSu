document.addEventListener("DOMContentLoaded", function () {
  var forms = document.querySelectorAll(".auth-box form");
  forms.forEach(function (form) {
    form.setAttribute("autocomplete", "on");
  });
});
