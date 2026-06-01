<h2 style="text-align:center;margin:30px;color:#4b2d7f;">TIN THÔNG BÁO</h2>

<div style="width:80%;margin:auto">

<?php foreach($news as $n): ?>

<div style="border:1px solid #ddd;padding:20px;margin-bottom:20px;border-radius:10px;background:#fafafa">

<h3 style="color:#4b2d7f"><?= htmlspecialchars($n['title'] ?? '') ?></h3>

<?php if(!empty($n['image'])): ?>
<img src="assets/uploads/<?= htmlspecialchars($n['image']) ?>" width="250" style="border-radius:6px">
<?php endif; ?>

<p style="margin-top:10px"><?= nl2br(htmlspecialchars($n['content'] ?? '')) ?></p>

<p style="font-size:13px;color:#777">
Gia sư đăng: <?= htmlspecialchars($n['tutor_name'] ?? 'Gia sư') ?> |
Ngày: <?= !empty($n['created_at']) ? date('d/m/Y', strtotime($n['created_at'])) : 'Chưa rõ' ?>
</p>

</div>

<?php endforeach; ?>

</div>
