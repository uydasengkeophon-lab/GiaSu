<form method="POST" action="?route=booking_store">

    <input type="hidden" name="tutor_id" value="<?= $tutor['id'] ?>">

    Số tiền:
    <input type="text" name="amount" required><br>

    <!-- 👇 THÊM ĐOẠN NÀY -->
    Ngày:
    <input type="date" name="study_date" required><br>

    Bắt đầu:
    <input type="time" name="start_time" required><br>

    Kết thúc:
    <input type="time" name="end_time" required><br>

    <button type="submit">Đặt lịch</button>
</form>