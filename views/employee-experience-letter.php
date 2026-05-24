<?php
use App\Helpers\EthiopianDateHelper; 
$is_exprience_registration_page = true; 

function calcDuration(string $start, ?string $end): array {
    $s = new DateTime($start);
    $e = $end ? new DateTime($end) : new DateTime();
    $diff = $s->diff($e);
    return [
        'years'      => $diff->y,
        'months'     => $diff->m,
        'days'       => $diff->d,
        'total_days' => (int)$s->diff($e)->days,
    ];
}

function formatDurationBadges(array $d): string {
    $html = '';
    $seen = 0;
    if ($d['years']  > 0) { $html .= '<span class="dur-badge">' . $d['years']  . ' ዓመት</span>'; $seen++; }
    if ($d['months'] > 0) { $p = $seen > 0 ? 'ከ' : ''; $html .= '<span class="dur-badge">' . $p . $d['months'] . ' ወር</span>'; $seen++; }
    if ($d['days']   > 0) { $p = $seen > 0 ? 'ከ' : ''; $html .= '<span class="dur-badge">' . $p . $d['days']   . ' ቀን</span>'; }
    if (!$html) $html = '<span class="dur-badge muted">—</span>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>የስራ ልምድ ማስረጃ — <?= htmlspecialchars($employee['first_name'] ?? '') ?></title>
<style>
    *, *::before, *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'DejaVu Sans', 'Noto Sans Ethiopic', sans-serif;
        font-size: 10.5pt;
        line-height: 1.55;
        color: #1a1a1a;
        background: #f0ede8;
        padding: 24px 0;
    }

    .page {
        width: 210mm;
        margin: 0 auto;
        background: #fff;
        padding: 12mm 18mm 14mm;
        box-shadow: 0 4px 32px rgba(0,0,0,0.13);
    }

    /* ════════════════════════════════════════
       LETTERHEAD
       ════════════════════════════════════════ */

    .letterhead {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 8px;
        border-bottom: 3px solid #1a3c6e;
        margin-bottom: 4px;
    }

    .letterhead-logo {
        flex-shrink: 0;
    }

    .letterhead-logo img {
        width: 56px;
        height: 56px;
        object-fit: contain;
        display: block;
    }

    .letterhead-logo-placeholder {
        width: 56px;
        height: 56px;
        border: 1.5px solid #1a3c6e;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1a3c6e;
        font-size: 8pt;
        text-align: center;
        line-height: 1.3;
    }

    .letterhead-org {
        flex: 1;
    }

    .letterhead-org .org-name {
        font-size: 14.5pt;
        font-weight: 700;
        color: #1a3c6e;
        letter-spacing: 0.02em;
        line-height: 1.1;
    }

    .letterhead-org .org-sub {
        font-size: 9pt;
        color: #555;
        margin-top: 1px;
        line-height: 1.2;
    }

    .letterhead-contact {
        text-align: right;
        font-size: 8.5pt;
        color: #555;
        line-height: 1.4;
        flex-shrink: 0;
    }

    .letterhead-contact span {
        display: block;
    }

    .letterhead-accent {
        height: 3px;
        background: linear-gradient(90deg, #1a3c6e 60%, #c49a2a 100%);
        margin-bottom: 8px;
    }

    /* ════════════════════════════════════════
       META
       ════════════════════════════════════════ */

    .meta-row {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        font-size: 9pt;
        color: #444;
        margin-bottom: 12px;
        text-align: right;
    }

    .meta-row .ref {
        font-style: italic;
    }

    /* ════════════════════════════════════════
       TITLE
       ════════════════════════════════════════ */

    .letter-title {
        text-align: center;
        margin-bottom: 14px;
    }

    .letter-title h1 {
        font-size: 12.5pt;
        font-weight: 700;
        color: #1a3c6e;
        text-decoration: underline;
        text-underline-offset: 4px;
        letter-spacing: 0.04em;
    }

    .letter-title .en-subtitle {
        font-size: 9pt;
        color: #777;
        margin-top: 1px;
    }

    /* ════════════════════════════════════════
       BODY
       ════════════════════════════════════════ */

    .letter-body p {
        text-align: justify;
        margin-bottom: 10px;
        hyphens: auto;
    }

    .letter-body b {
        color: #111;
        font-weight: 700;
    }

    /* ════════════════════════════════════════
       EXPERIENCE SECTION
       ════════════════════════════════════════ */

    .exp-section {
        margin: 10px 0 10px;
    }

    .exp-section-title {
        font-size: 9.5pt;
        font-weight: 700;
        color: #1a3c6e;
        border-bottom: 1.5px solid #1a3c6e;
        padding-bottom: 3px;
        margin-bottom: 6px;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .exp-section-title::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 14px;
        background: #c49a2a;
        border-radius: 2px;
        flex-shrink: 0;
    }

    .exp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5pt;
        color: #1a1a1a;
    }

    .exp-table thead tr {
        background: #1a3c6e;
        color: #fff;
    }

    .exp-table thead th {
        padding: 5px 8px;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-align: left;
        white-space: nowrap;
    }

    .exp-table thead th:last-child {
        text-align: center;
    }

    .exp-table tbody tr:nth-child(odd) {
        background: #f7f8fc;
    }

    .exp-table tbody tr:nth-child(even) {
        background: #ffffff;
    }

    .exp-table tbody tr {
        border-bottom: 1px solid #e2e6f0;
    }

    .exp-table tbody td {
        padding: 5px 8px;
        vertical-align: middle;
    }

    .exp-table tbody td:first-child {
        color: #1a3c6e;
        font-weight: 700;
        text-align: center;
        width: 28px;
    }

    /* ════════════════════════════════════════
       BADGES
       ════════════════════════════════════════ */

    .dur-badge {
        display: inline-block;
        background: #eef1f9;
        border: 1px solid #c8d0e8;
        color: #1a3c6e;
        border-radius: 3px;
        padding: 1px 6px;
        font-size: 8.5pt;
        margin-right: 2px;
        white-space: nowrap;
    }

    .dur-badge.muted {
        color: #aaa;
        border-color: #ddd;
        background: #fafafa;
    }

    .badge-now {
        display: inline-block;
        background: #1a7a3c;
        color: #fff;
        border-radius: 3px;
        padding: 1px 7px;
        font-size: 8pt;
        margin-right: 4px;
        font-weight: 600;
    }

    /* ════════════════════════════════════════
       TOTAL EXPERIENCE
       ════════════════════════════════════════ */

    .total-exp-box {
        margin-top: 10px;
        padding: 7px 12px;
        background: #f0f4ff;
        border-left: 3px solid #1a3c6e;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 9.5pt;
        flex-wrap: wrap;
    }

    .total-exp-box .label {
        color: #444;
        font-weight: 600;
    }

    /* ════════════════════════════════════════
       SIGNATURE
       ════════════════════════════════════════ */

    .letter-closing {
        margin-top: 14px;
        margin-bottom: 18px;
    }

    .letter-closing p {
        margin-bottom: 0;
    }

    .sig-footer-block {
        margin-top: 10px;
    }

    .sig-stamp-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 6px;
        gap: 20px;
    }

    .sig-block {
        line-height: 1.4;
    }

    .sig-line {
        display: block;
        width: 180px;
        border-bottom: 1.5px solid #333;
        margin-bottom: 4px;
    }

    .sig-label {
        font-size: 8.5pt;
        color: #444;
    }

    .sig-name {
        font-weight: 700;
        font-size: 9.5pt;
    }

    .stamp-box {
        width: 80px;
        height: 80px;
        border: 1.5px dashed #aaa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 7.5pt;
        color: #bbb;
        text-align: center;
        line-height: 1.4;
        flex-shrink: 0;
    }

    /* ════════════════════════════════════════
       FOOTER
       ════════════════════════════════════════ */

    .page-footer {
        margin-top: 10px;
        border-top: 1px solid #ddd;
        padding-top: 4px;
        font-size: 7.5pt;
        color: #888;
        text-align: center;
    }

    /* ════════════════════════════════════════
       BUTTON
       ════════════════════════════════════════ */

    .no-print {
        margin-bottom: 18px;
        text-align: center;
    }

    .print-btn {
        padding: 9px 22px;
        background: #1a3c6e;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 11pt;
    }

    .print-btn:hover {
        background: #16345d;
    }

    /* hidden on screen */

    .print-header,
    .print-footer {
        display: none;
    }

    /* ════════════════════════════════════════
       PRINT MODE
       ════════════════════════════════════════ */

    @page {
        size: A4;

        /* TOP RIGHT BOTTOM LEFT */
        margin: 14mm 16mm 14mm 16mm;
    }

    @media print {

        html,
        body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;

            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-size: 10pt;
        }

        .no-print {
            display: none !important;
        }

        .page {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        /* ════════════════════════════════════════
           FIXED HEADER
           ════════════════════════════════════════ */

        .print-header {
            display: block !important;

            position: fixed;
            top: 0;
            left: 0;
            right: 0;

            background: #fff;
            z-index: 9999;

            padding: 2mm 16mm 1mm 16mm;
        }

        .print-header .letterhead {
            padding-bottom: 2px !important;
            margin-bottom: 1px !important;
            border-bottom-width: 2px !important;
        }

        .print-header .letterhead-accent {
            height: 2px !important;
            margin-bottom: 2px !important;
        }

        .print-header .org-name {
            line-height: 1.05 !important;
        }

        .print-header .org-sub {
            margin-top: 0 !important;
        }

        .print-header .letterhead-contact {
            line-height: 1.25 !important;
        }

        /* ════════════════════════════════════════
           FIXED FOOTER
           ════════════════════════════════════════ */

        .print-footer {
            display: block !important;

            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;

            background: #fff;
            z-index: 9999;

            padding: 1mm 16mm 3mm 16mm;
        }

        /* hide original screen header/footer */

        .page > .letterhead,
        .page > .letterhead-accent,
        .screen-footer {
            display: none !important;
        }

        /* REMOVE HUGE TOP GAP */

        .meta-row {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* TABLE PRINTING */

        .exp-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        .exp-table thead {
            display: table-header-group;
        }

        .exp-table tfoot {
            display: table-footer-group;
        }

        .exp-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .exp-table td,
        .exp-table th {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* KEEP IMPORTANT BLOCKS TOGETHER */

        .meta-row,
        .letter-title,
        .exp-section-title,
        .total-exp-box,
        .sig-footer-block,
        .sig-stamp-row,
        .letter-closing,
        .page-footer {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* typography */

        p {
            orphans: 3;
            widows: 3;
        }

        * {
            overflow: visible !important;
        }

        .sig-footer-block {
            margin-top: 14px;
        }

        .page-footer {
            margin-top: 8px;
        }
    }
</style>
</head>
<body>

<div class="no-print">
    <button id="print-btn" style="padding:9px 22px;background:#1a3c6e;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:11pt;">
        📄 Print
    </button>
</div>

<div class="page">

    <!-- ══ LETTERHEAD ══ -->
    <div class="letterhead">
        <div class="letterhead-logo">
            <?php if (!empty($_SESSION['user']['logo_url'])): ?>
                <img src="<?= rtrim($_ENV['BASE_URL'] ?? '', '/') ?>/serve-file?file=<?= htmlspecialchars($_SESSION['user']['logo_url']) ?>&type=image" alt="Logo">
            <?php else: ?>
                <div class="letterhead-logo-placeholder">LOGO</div>
            <?php endif; ?>
        </div>
        <div class="letterhead-org">
            <div class="org-name"><?= htmlspecialchars($_SESSION['user']['branch_name'] ?? 'ድርጅቱ ስም') ?></div>
            <?php if (!empty($_SESSION['user']['org_subtitle'])): ?>
                <div class="org-sub"><?= htmlspecialchars($_SESSION['user']['org_subtitle']) ?></div>
            <?php endif; ?>
        </div>
        <div class="letterhead-contact">
            <?php if (!empty($_SESSION['user']['address'])): ?>
                <span>📍 <?= htmlspecialchars($_SESSION['user']['address']) ?></span>
            <?php endif; ?>
            <?php if (!empty($_SESSION['user']['phone'])): ?>
                <span>📞 <?= htmlspecialchars($_SESSION['user']['phone']) ?></span>
            <?php endif; ?>
            <?php if (!empty($_SESSION['user']['email'])): ?>
                <span>✉ <?= htmlspecialchars($_SESSION['user']['email']) ?></span>
            <?php endif; ?>
            <?php if (!empty($_SESSION['user']['po_box'])): ?>
                <span>ፖ.ሳ. <?= htmlspecialchars($_SESSION['user']['po_box']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="letterhead-accent"></div>

    <!-- ══ META ROW ══ -->
    <div class="meta-row">
        <span class="ref">ቁጥር: <?= htmlspecialchars($employee['ref_number'] ?? '_____/_____/___') ?></span>
        <?php
            $todayParts = explode('-', date('Y-m-d'));
            $todayEth   = EthiopianDateHelper::toEthCalendar($todayParts[2], $todayParts[1], $todayParts[0]);
        ?>
        <span>ቀን: <?= EthiopianDateHelper::getMonthName($todayEth['month']) ?> <?= $todayEth['day'] ?> <?= $todayEth['year'] ?> ዓ.ም</span>
    </div>

    <!-- ══ TITLE ══ -->
    <div class="letter-title">
        <h1>የስራ ልምድ ማስረጃ</h1>
        <div class="en-subtitle">Certificate of Work Experience</div>
    </div>

    <!-- ══ BODY ══ -->
    <div class="letter-body">
        <p>
            ይህ ሰነድ የተሰጠው
            <b><?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?></b>
            የሚባሉ ግለሰብ በ<b><?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?></b>
            ከ <b><?= htmlspecialchars($employee['start_date_eth'] ?? '---') ?></b> ዓ.ም እስከ
            <b><?= htmlspecialchars($employee['end_date_eth'] ?? 'አሁን') ?></b> ዓ.ም ድረስ
            በ<b><?= htmlspecialchars($employee['job_name'] ?? '') ?></b> የስራ መደብ ላይ ሲሰሩ
            እንደነበር ለማረጋገጥ ነው።
        </p>
        <p>
            በነበራቸው የቆይታ ጊዜ ሁሉ የተሰጣቸውን የሥራ ኃላፊነት በታማኝነት፣ በሙያዊ ብቃትና
            በጠንካራ የስራ ሥነ-ምግባር ሲወጡ እንደነበር በሙሉ ኃላፊነት እናረጋግጣለን።
        </p>

        <!-- ══ EXPERIENCE TABLE ══ -->
        <?php
        $hasRows = !empty($employee['date_of_employed']) || !empty($experiences);
        if ($hasRows):
            $grandTotalDays = 0;
            $counter = 1;
        ?>
        <div class="exp-section">
            <div class="exp-section-title">የሥራ ልምድ ዝርዝር</div>
            <table class="exp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>የሰሩበት መስሪያ ቤት</th>
                        <th>የስራ መደብ</th>
                        <th>የስራ ዘመን</th>
                        <th>ልምድ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employee['date_of_employed'])):
                        $startParts = explode('-', $employee['date_of_employed']);
                        $startEth   = EthiopianDateHelper::toEthCalendar($startParts[2], $startParts[1], $startParts[0]);
                        $today      = date('Y-m-d');
                        $endParts   = explode('-', $today);
                        $endEth     = EthiopianDateHelper::toEthCalendar($endParts[2], $endParts[1], $endParts[0]);
                        $duration   = calcDuration($employee['date_of_employed'], $today);
                    ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($employee['job_name'] ?? '') ?></td>
                        <td class="text-nowrap">
                            ከ<?= EthiopianDateHelper::getMonthName($startEth['month']) ?> <?= $startEth['day'] ?> <?= $startEth['year'] ?>
                            እስከ <span class="badge-now">ዛሬ</span>
                            <?= EthiopianDateHelper::getMonthName($endEth['month']) ?> <?= $endEth['day'] ?> <?= $endEth['year'] ?>
                        </td>
                        <td class="text-nowrap"><?= formatDurationBadges($duration) ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if (!empty($experiences)):
                        foreach ($experiences as $exp):
                            $startParts = explode('-', $exp['start_date']);
                            $startEth   = EthiopianDateHelper::toEthCalendar($startParts[2], $startParts[1], $startParts[0]);
                            $endEth     = null;
                            if (!empty($exp['end_date'])) {
                                $endParts = explode('-', $exp['end_date']);
                                $endEth   = EthiopianDateHelper::toEthCalendar($endParts[2], $endParts[1], $endParts[0]);
                            }
                            $duration        = calcDuration($exp['start_date'], $exp['end_date'] ?? null);
                            $grandTotalDays += $duration['total_days'];
                    ?>
                    <tr id="row-<?= htmlspecialchars($exp['id']) ?>">
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($exp['company_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($exp['job_title'] ?? '') ?></td>
                        <td class="text-nowrap">
                            ከ<?= EthiopianDateHelper::getMonthName($startEth['month']) ?> <?= $startEth['day'] ?> <?= $startEth['year'] ?>
                            እስከ
                            <?php if ($endEth): ?>
                                <?= EthiopianDateHelper::getMonthName($endEth['month']) ?> <?= $endEth['day'] ?> <?= $endEth['year'] ?>
                            <?php else: ?>
                                <span class="badge-now">አሁን</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap"><?= formatDurationBadges($duration) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php
            $allTotalDays = $grandTotalDays;
            if (!empty($employee['date_of_employed'])) {
                $primaryDuration = calcDuration($employee['date_of_employed'], date('Y-m-d'));
                $allTotalDays   += $primaryDuration['total_days'];
            }
            if ($allTotalDays > 0):
                $tY = intdiv($allTotalDays, 365);
                $tR = $allTotalDays % 365;
                $tM = intdiv($tR, 30);
                $tD = $tR % 30;
            ?>
            <div class="total-exp-box">
                <span class="label">ጠቅላላ የሥራ ልምድ ጊዜ፡</span>
                <?php $tSeen = 0; ?>
                <?php if ($tY > 0): ?><span class="dur-badge"><?= $tY ?> ዓመት</span><?php $tSeen++; endif; ?>
                <?php if ($tM > 0): ?><span class="dur-badge"><?= ($tSeen > 0 ? 'ከ' : '') . $tM ?> ወር</span><?php $tSeen++; endif; ?>
                <?php if ($tD > 0): ?><span class="dur-badge"><?= ($tSeen > 0 ? 'ከ' : '') . $tD ?> ቀን</span><?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>

        <p>ይህ ሰነድ ለሚፈለጉበት ዓላማ እንዲያገለግላቸው ተሰጥቷቸዋል።</p>
    </div>

    <!-- ══ CLOSING + SIGNATURE + FOOTER — wrapped so they never orphan ══ -->
    <div class="sig-footer-block">

        <div class="letter-closing">
            <p>ከሰላምታ ጋር፣</p>
        </div>

        <div class="sig-stamp-row">
            <div class="sig-block">
                <span class="sig-line"></span>
                <div class="sig-name">
                    <?= htmlspecialchars(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['father_name'] ?? ''))) ?>
                </div>
                <div class="sig-label"><?= htmlspecialchars($_SESSION['user']['job_title'] ?? 'የሰው ኃይል አስተዳደር ኃላፊ') ?></div>
                <div class="sig-label"><?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?></div>
            </div>
            <div class="stamp-box">የድርጅቱ<br>ማህተም</div>
        </div>

        <!-- screen-only footer (hidden on print, replaced by fixed print-footer) -->
        <div class="page-footer screen-footer">
            <?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?>
            <?php if (!empty($_SESSION['user']['address'])): ?>
                &nbsp;|&nbsp; <?= htmlspecialchars($_SESSION['user']['address']) ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['user']['phone'])): ?>
                &nbsp;|&nbsp; <?= htmlspecialchars($_SESSION['user']['phone']) ?>
            <?php endif; ?>
        </div>

    </div><!-- /.sig-footer-block -->

</div><!-- /.page -->

<!-- ════════════════════════════════════════
     PRINT HEADER
     ════════════════════════════════════════ -->

<div class="print-header">

    <div class="letterhead">

        <div class="letterhead-logo">
            <?php if (!empty($_SESSION['user']['logo_url'])): ?>
                <img src="<?= rtrim($_ENV['BASE_URL'] ?? '', '/') ?>/serve-file?file=<?= htmlspecialchars($_SESSION['user']['logo_url']) ?>&type=image" alt="Logo">
            <?php else: ?>
                <div class="letterhead-logo-placeholder">LOGO</div>
            <?php endif; ?>
        </div>

        <div class="letterhead-org">
            <div class="org-name">
                <?= htmlspecialchars($_SESSION['user']['branch_name'] ?? 'ድርጅቱ ስም') ?>
            </div>

            <?php if (!empty($_SESSION['user']['org_subtitle'])): ?>
                <div class="org-sub">
                    <?= htmlspecialchars($_SESSION['user']['org_subtitle']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="letterhead-contact">

            <?php if (!empty($_SESSION['user']['address'])): ?>
                <span>📍 <?= htmlspecialchars($_SESSION['user']['address']) ?></span>
            <?php endif; ?>

            <?php if (!empty($_SESSION['user']['phone'])): ?>
                <span>📞 <?= htmlspecialchars($_SESSION['user']['phone']) ?></span>
            <?php endif; ?>

            <?php if (!empty($_SESSION['user']['email'])): ?>
                <span>✉ <?= htmlspecialchars($_SESSION['user']['email']) ?></span>
            <?php endif; ?>

            <?php if (!empty($_SESSION['user']['po_box'])): ?>
                <span>ፖ.ሳ. <?= htmlspecialchars($_SESSION['user']['po_box']) ?></span>
            <?php endif; ?>

        </div>

    </div>

    <div class="letterhead-accent"></div>

</div>

<!-- ════════════════════════════════════════
     PRINT FOOTER
     ════════════════════════════════════════ -->

<div class="print-footer">

    <div class="page-footer">

        <?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?>

        <?php if (!empty($_SESSION['user']['address'])): ?>
            &nbsp;|&nbsp;
            <?= htmlspecialchars($_SESSION['user']['address']) ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['user']['phone'])): ?>
            &nbsp;|&nbsp;
            <?= htmlspecialchars($_SESSION['user']['phone']) ?>
        <?php endif; ?>

    </div>

</div>
</body>
</html>
<script nonce="<?php echo htmlspecialchars($GLOBALS['nonce'] ?? ''); ?>">
    document.addEventListener('DOMContentLoaded', () => {
        const printBtn = document.getElementById('print-btn');
        if (printBtn) {
            printBtn.addEventListener('click', () => window.print());
        }
    });
</script>