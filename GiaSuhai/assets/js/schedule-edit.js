document.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("schedule-edit-form");
  if (!form) {
    return;
  }

  form.addEventListener("submit", function (event) {
    var startInput = form.querySelector('input[name="gio_bat_dau"]');
    var endInput = form.querySelector('input[name="gio_ket_thuc"]');

    if (!startInput || !endInput) {
      return;
    }

    if (startInput.value >= endInput.value) {
      event.preventDefault();
      alert("Giờ kết thúc phải lớn hơn giờ bắt đầu.");
    }
  });
});
