document.addEventListener("DOMContentLoaded", function () {
  var storedTheme = localStorage.getItem("admin-theme");
  if (storedTheme === "dark") {
    document.body.classList.add("admin-dark");
  }

  var themeToggle = document.getElementById("adminThemeToggle");
  if (themeToggle) {
    themeToggle.addEventListener("click", function () {
      document.body.classList.toggle("admin-dark");
      localStorage.setItem(
        "admin-theme",
        document.body.classList.contains("admin-dark") ? "dark" : "light"
      );
    });
  }

  var sidebarToggle = document.getElementById("adminSidebarToggle");
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", function () {
      document.body.classList.toggle("sidebar-open");
    });
  }

  document.querySelectorAll("[data-confirm]").forEach(function (element) {
    element.addEventListener("click", function (event) {
      var message =
        element.getAttribute("data-confirm") || "Bạn có chắc chắn không?";
      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });

  var roleSwitch = document.querySelector('[data-role-switch="user-edit"]');
  if (roleSwitch) {
    var toggleRoleFields = function (role) {
      document
        .querySelectorAll("[data-role-fields]")
        .forEach(function (section) {
          var matches = section.getAttribute("data-role-fields") === role;
          section.style.display = matches ? "" : "none";

          section
            .querySelectorAll("input, textarea, select")
            .forEach(function (field) {
              field.disabled = !matches;
            });
        });
    };

    toggleRoleFields(roleSwitch.value);
    roleSwitch.addEventListener("change", function () {
      toggleRoleFields(roleSwitch.value);
    });
  }
});
