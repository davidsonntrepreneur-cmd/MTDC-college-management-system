<?php
/**
 * Printable fee receipt (PDF via browser print dialog).
 * Access: admin (any receipt) or the student who owns it.
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT fp.*, s.admission_no, s.full_name, s.year, s.section, c.name course_name
    FROM fee_payments fp
    JOIN students s ON s.id = fp.student_id
    JOIN courses c ON c.id = s.course_id
    WHERE fp.id = ?');
$st->execute([$id]);
$r = $st->fetch();

if (!$r) { http_response_code(404); die('Receipt not found.'); }
if (current_role() === 'student' && (int)$r['student_id'] !== (int)($_SESSION['student_id'] ?? 0)) {
    http_response_code(403); die('Access denied.');
}
if (current_role() === 'faculty') { http_response_code(403); die('Access denied.'); }

/* amount in words (Indian system) */
function words_below_1000(int $n): string {
    $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    $s = '';
    if ($n >= 100) { $s .= $ones[intdiv($n, 100)] . ' Hundred'; $n %= 100; if ($n) $s .= ' '; }
    if ($n >= 20) { $s .= $tens[intdiv($n, 10)]; $n %= 10; if ($n) $s .= ' ' . $ones[$n]; }
    elseif ($n > 0) { $s .= $ones[$n]; }
    return $s;
}
function amount_in_words(float $amt): string {
    $rupees = (int)floor($amt);
    $paise  = (int)round(($amt - $rupees) * 100);
    if ($rupees == 0) { $w = 'Zero'; }
    else {
        $parts = [];
        foreach ([['Crore', 10000000], ['Lakh', 100000], ['Thousand', 1000]] as [$name, $div]) {
            if ($rupees >= $div) { $parts[] = words_below_1000(intdiv($rupees, $div)) . ' ' . $name; $rupees %= $div; }
        }
        if ($rupees > 0) $parts[] = words_below_1000($rupees);
        $w = implode(' ', $parts);
    }
    $out = 'Rupees ' . $w;
    if ($paise > 0) $out .= ' and ' . words_below_1000($paise) . ' Paise';
    return $out . ' Only';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt <?= e($r['receipt_no']) ?> | MTDC</title>
<style>
  :root { --primary:#1a3a6b; --accent:#c8a253; }
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:'Segoe UI',system-ui,Arial,sans-serif; background:#f2f4f8; padding:24px; color:#22293a; }
  .sheet { max-width:640px; margin:0 auto; background:#fff; border:1px solid #ccd3e0; }
  .rc-head { display:flex; gap:14px; align-items:center; padding:18px 24px; border-bottom:3px double var(--primary); }
  .rc-logo { width:56px; height:56px; border-radius:50%; background:var(--accent); color:var(--primary); font-weight:800; font-size:20px; display:flex; align-items:center; justify-content:center; flex:0 0 56px; }
  .rc-college { font-size:19px; font-weight:800; color:var(--primary); }
  .rc-place { font-size:12px; color:#555; }
  .rc-title { text-align:center; background:var(--primary); color:#fff; font-weight:700; letter-spacing:2px; padding:6px; font-size:13px; }
  .rc-body { padding:20px 24px; }
  table.meta { width:100%; border-collapse:collapse; font-size:13.5px; }
  table.meta td { padding:7px 4px; border-bottom:1px dashed #d7dce6; vertical-align:top; }
  table.meta td:first-child { color:#666; width:38%; }
  table.meta td:last-child { font-weight:600; }
  .rc-amount { margin:18px 0 6px; border:2px solid var(--primary); border-radius:8px; display:flex; }
  .rc-amount .lbl { background:var(--primary); color:#fff; padding:12px 16px; font-size:13px; font-weight:700; display:flex; align-items:center; }
  .rc-amount .val { padding:10px 16px; font-size:24px; font-weight:800; color:var(--primary); }
  .rc-words { font-size:12.5px; color:#444; font-style:italic; margin-bottom:16px; }
  .rc-foot { display:flex; justify-content:space-between; align-items:flex-end; padding:26px 24px 18px; }
  .rc-sign { text-align:center; font-size:12px; color:#555; }
  .rc-sign .line { width:170px; border-top:1px solid #888; margin-bottom:4px; }
  .rc-note { font-size:11px; color:#888; text-align:center; padding:0 24px 16px; }
  .toolbar { max-width:640px; margin:0 auto 14px; display:flex; gap:10px; justify-content:flex-end; }
  .btn { padding:9px 18px; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; text-decoration:none; }
  .btn-p { background:var(--primary); color:#fff; }
  .btn-g { background:var(--accent); color:var(--primary); }
  @media print {
    body { background:#fff; padding:0; }
    .toolbar { display:none; }
    .sheet { border:none; max-width:100%; }
    @page { size:A5 landscape; margin:8mm; }
  }
</style>
</head>
<body>
<div class="toolbar">
  <a class="btn btn-g" href="<?= base_url(role_home()) ?>">← Back</a>
  <button class="btn btn-p" onclick="window.print()">🖨 Print / Save as PDF</button>
</div>

<div class="sheet">
  <div class="rc-head">
    <div class="rc-logo">MT</div>
    <div>
      <div class="rc-college"><?= e(COLLEGE_NAME) ?></div>
      <div class="rc-place"><?= e(COLLEGE_PLACE) ?></div>
    </div>
  </div>
  <div class="rc-title">FEE RECEIPT</div>
  <div class="rc-body">
    <table class="meta">
      <tr><td>Receipt No.</td><td><?= e($r['receipt_no']) ?></td></tr>
      <tr><td>Date</td><td><?= e(date('d-m-Y', strtotime($r['payment_date']))) ?></td></tr>
      <tr><td>Received From</td><td><?= e($r['full_name']) ?> (<?= e($r['admission_no']) ?>)</td></tr>
      <tr><td>Course / Class</td><td><?= e($r['course_name']) ?> — Year <?= (int)$r['year'] ?>, Sec <?= e($r['section']) ?></td></tr>
      <tr><td>Academic Year</td><td><?= e($r['academic_year']) ?></td></tr>
      <tr><td>Payment Mode</td><td><?= e($r['mode']) ?></td></tr>
      <?php if ($r['remarks']): ?><tr><td>Towards</td><td><?= e($r['remarks']) ?></td></tr><?php endif; ?>
    </table>
    <div class="rc-amount">
      <div class="lbl">AMOUNT<br>RECEIVED</div>
      <div class="val">₹ <?= number_format((float)$r['amount'], 2) ?></div>
    </div>
    <div class="rc-words"><?= e(amount_in_words((float)$r['amount'])) ?></div>
  </div>
  <div class="rc-foot">
    <div class="rc-sign"><div class="line"></div>Student / Parent Signature</div>
    <div class="rc-sign"><div class="line"></div>Cashier / Office Seal</div>
  </div>
  <div class="rc-note">This is a computer-generated receipt issued by <?= e(COLLEGE_NAME) ?>, Palamaner. Generated on <?= e(date('d-m-Y h:i A')) ?>.</div>
</div>
</body>
</html>
