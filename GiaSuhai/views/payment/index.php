<?php
$dayNames = [
    0 => 'Thứ Hai',
    1 => 'Thứ Ba',
    2 => 'Thứ Tư',
    3 => 'Thứ Năm',
    4 => 'Thứ Sáu',
    5 => 'Thứ Bảy',
    6 => 'Chủ Nhật'
];

$sessionNames = [
    1 => 'Buổi sáng',
    2 => 'Buổi chiều',
    3 => 'Buổi tối'
];

$formatSubject = function ($row) {
    return $row['recurring_subject'] ?: ($row['tutor_subjects'] ?: 'Lớp học');
};
?>

<div class="container" style="padding: 42px 0;">
    <?php if (!empty($_GET['error'])): ?>
        <div style="max-width: 960px; margin: 0 auto 16px; background:#fee2e2; color:#991b1b; padding:12px 14px; border-radius:8px; font-weight:700;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($booking)): ?>
        <section style="max-width: 1000px; margin: 0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; margin-bottom:20px;">
                <div>
                    <p style="margin:0 0 6px; color:#64748b; font-weight:800; text-transform:uppercase; font-size:12px;">Học phí</p>
                    <h2 style="margin:0; color:#4b2d7f;">Danh sách đơn chờ thanh toán</h2>
                </div>

                <form method="get" action="" style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="hidden" name="url" value="payment/index">
                    <input type="search" name="q" value="<?= htmlspecialchars($keyword ?? '') ?>" placeholder="Tìm gia sư hoặc môn học"
                           style="min-width:260px; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
                    <button type="submit" style="background:#4b2d7f; color:#fff; border:0; border-radius:8px; padding:10px 16px; font-weight:700; cursor:pointer;">
                        Tìm kiếm
                    </button>
                </form>
            </div>

            <div style="display:grid; gap:14px;">
                <?php if (!empty($paymentList)): ?>
                    <?php foreach ($paymentList as $item): ?>
                        <article style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px; display:grid; grid-template-columns:1fr auto; gap:14px; align-items:center; box-shadow:0 8px 24px rgba(15,23,42,0.06);">
                            <div>
                                <strong style="font-size:18px; color:#111827;"><?= htmlspecialchars($formatSubject($item)) ?></strong>
                                <p style="margin:8px 0 0; color:#475569;">
                                    Gia sư: <b><?= htmlspecialchars($item['tutor_name'] ?? 'Không rõ') ?></b>
                                    <?php if (isset($item['thu_trong_tuan'], $item['phien_hoc'])): ?>
                                        · <?= htmlspecialchars($dayNames[(int)$item['thu_trong_tuan']] ?? '') ?>
                                        · <?= htmlspecialchars($sessionNames[(int)$item['phien_hoc']] ?? '') ?>
                                    <?php endif; ?>
                                </p>
                                <p style="margin:8px 0 0; color:#e91e63; font-weight:800;">
                                    <?= number_format($item['amount'] ?? 0) ?> VNĐ
                                </p>
                            </div>
                            <a href="index.php?url=payment/index&id=<?= (int)$item['id'] ?>"
                               style="background:#e91e63; color:#fff; padding:11px 16px; border-radius:8px; font-weight:800; text-decoration:none; text-align:center;">
                                Thanh toán QR
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="background:#fff; border:1px dashed #cbd5e1; border-radius:12px; padding:28px; text-align:center; color:#64748b; font-weight:700;">
                        Không có đơn chờ thanh toán phù hợp.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
        <div class="auth-box" style="width: min(620px, 94vw); margin: 0 auto; text-align: center;">
            <h2 style="color: #4b2d7f; margin-bottom: 20px;">Thanh Toán Học Phí</h2>
            
            <div style="background: #f9f9ff; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: left;">
                <p><strong>Mã đơn:</strong> #<?= htmlspecialchars($booking['id'] ?? '') ?></p>

                <p><strong>Gia sư:</strong> 
                    <?= htmlspecialchars($booking['tutor_name'] ?? 'Không rõ') ?>
                </p>

                <p><strong>Môn học:</strong> 
                    <?= htmlspecialchars($formatSubject($booking)) ?>
                </p>

                <?php if (isset($booking['thu_trong_tuan'], $booking['phien_hoc'])): ?>
                    <p><strong>Lịch học:</strong>
                        <?= htmlspecialchars($dayNames[(int)$booking['thu_trong_tuan']] ?? '') ?> -
                        <?= htmlspecialchars($sessionNames[(int)$booking['phien_hoc']] ?? '') ?>
                    </p>
                <?php endif; ?>

                <p><strong>Số tiền cần thanh toán:</strong></p>
                <h3 style="color: #f80759; font-size: 28px;">
                    <?= number_format($booking['amount'] ?? 0) ?> VNĐ
                </h3>
            </div>

            <div style="margin-bottom: 30px;">
                <p style="margin-bottom: 10px;">Quét mã QR bên dưới để chuyển khoản:</p>

                <?php 
                    $bankId = "ICB";
                    $accountNo = "108877541667";
                    $template = "compact2";

                    $amount = $booking['amount'] ?? 0;
                    $description = "Thanh toan don " . ($booking['id'] ?? '');

                    $qrSource = "https://img.vietqr.io/image/" 
                        . $bankId . "-" . $accountNo . "-" . $template 
                        . ".png?amount=" . $amount
                        . "&addInfo=" . urlencode($description)
                        . "&accountName=" . urlencode("APPCO CENTER")
                        . "&t=" . time();
                ?>

                <img src="<?= $qrSource ?>"
                     alt="QR Code"
                     style="width: 250px; border: 1px solid #ddd; border-radius: 10px;"
                     onerror="this.src='https://via.placeholder.com/250?text=QR+Error';">

                <p style="font-size: 12px; color: #666; margin-top: 5px;">
                    Nội dung CK: <strong><?= htmlspecialchars($description) ?></strong>
                </p>

                <p style="font-size: 14px; color: #4b2d7f; font-weight: bold; margin-top: 5px;">
                    Ngân hàng: VietinBank (ICB) - STK: 108877541667
                </p>
            </div>

            <a href="index.php?url=payment/pay&id=<?= (int)$booking['id'] ?>"
               onclick="return confirm('Xác nhận đã thanh toán?');"
               style="display:inline-block; padding:12px 25px; background:#28a745; color:#fff; border-radius:5px;">
               ✔ Tôi đã thanh toán
            </a>

            <a href="index.php?url=payment/index"
               style="display:inline-block; margin-left:10px; padding:12px 18px; background:#e5e7eb; color:#111827; border-radius:5px;">
               Danh sách đơn
            </a>
        </div>
    <?php endif; ?>
</div>
