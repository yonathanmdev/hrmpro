<?php
// Ensure these are passed from your controller:
// $employee['first_name'], $employee['father_name'], $employee['g_father_name']
// $employee['job_name'], $employee['start_date_eth'], $employee['end_date_eth']
// $employee['ref_number'] (optional)
// $_SESSION['user']['branch_name'], ['logo_url'], ['first_name'], ['father_name'], ['job_title']
// $_SESSION['user']['address'], ['phone'], ['email'], ['po_box'] (optional contact fields)
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>የስራ ልምድ ማስረጃ — <?= htmlspecialchars($employee['first_name'] ?? '') ?></title>
    <style>
        /* ─── Base ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', 'Noto Sans Ethiopic', sans-serif;
            font-size: 11pt;
            line-height: 1.75;
            color: #1a1a1a;
            background: #f0ede8;
            padding: 32px 0;
        }

        /* ─── Page Shell ─── */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 18mm 22mm 20mm;
            box-shadow: 0 4px 32px rgba(0,0,0,0.13);
            position: relative;
        }

        /* ─── Letterhead ─── */
        .letterhead {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 14px;
            border-bottom: 3px solid #1a3c6e;
            margin-bottom: 6px;
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
            line-height: 1.2;
        }

        .letterhead-org .org-sub {
            font-size: 9pt;
            color: #555;
            margin-top: 2px;
        }

        .letterhead-contact {
            text-align: right;
            font-size: 8.5pt;
            color: #555;
            line-height: 1.7;
            flex-shrink: 0;
        }

        .letterhead-contact span {
            display: block;
        }

        /* Thin accent line below border */
        .letterhead-accent {
            height: 3px;
            background: linear-gradient(90deg, #1a3c6e 60%, #c49a2a 100%);
            margin-bottom: 18px;
        }

        /* ─── Meta Row (Ref + Date) ─── */
        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 9.5pt;
            color: #444;
            margin-bottom: 26px;
        }

        .meta-row .ref { font-style: italic; }

        /* ─── Letter Title ─── */
        .letter-title {
            text-align: center;
            margin-bottom: 24px;
        }

        .letter-title h1 {
            font-size: 13pt;
            font-weight: 700;
            color: #1a3c6e;
            text-decoration: underline;
            text-underline-offset: 4px;
            letter-spacing: 0.04em;
        }

        .letter-title .en-subtitle {
            font-size: 9.5pt;
            color: #777;
            margin-top: 2px;
        }

        /* ─── Body ─── */
        .letter-body p {
            text-align: justify;
            margin-bottom: 16px;
            hyphens: auto;
        }

        .letter-body b {
            color: #111;
            font-weight: 700;
        }

        /* ─── Closing ─── */
        .letter-closing {
            margin-top: 10px;
            margin-bottom: 52px;
        }

        .letter-closing p { margin-bottom: 0; }

        /* ─── Signature & Stamp Block ─── */
        .sig-stamp-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 8px;
        }

        .sig-block {
            line-height: 1.5;
        }

        .sig-line {
            display: block;
            width: 200px;
            border-bottom: 1.5px solid #333;
            margin-bottom: 6px;
        }

        .sig-label {
            font-size: 9pt;
            color: #444;
        }

        .sig-name {
            font-weight: 700;
            font-size: 10pt;
        }

        .stamp-box {
            width: 100px;
            height: 100px;
            border: 1.5px dashed #aaa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            color: #bbb;
            text-align: center;
            line-height: 1.4;
        }

        /* ─── Footer Rule ─── */
        .page-footer {
            position: absolute;
            bottom: 14mm;
            left: 22mm;
            right: 22mm;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            font-size: 7.5pt;
            color: #aaa;
            text-align: center;
        }

        /* ─── Print ─── */
        .no-print { margin-bottom: 18px; text-align: center; }

        @media print {
            body { background: #fff; padding: 0; }
            .page { box-shadow: none; margin: 0; padding: 14mm 18mm 18mm; }
            .no-print { display: none !important; }
            .page-footer { position: fixed; bottom: 10mm; }
        }
    </style>
</head>
<body>

<!-- Print Button -->
<div class="no-print">
    <button onclick="window.print()"
        style="padding:9px 22px;background:#1a3c6e;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:11pt;">
        📄 ይህን ደብዳቤ አትም
    </button>
</div>

<div class="page">

    <!-- ══ LETTERHEAD ══ -->
    <div class="letterhead">

        <!-- Logo (small, professional) -->
        <div class="letterhead-logo">
            <?php if (!empty($_SESSION['user']['logo_url'])): ?>
                <img src="<?= rtrim($_ENV['BASE_URL'] ?? '', '/') ?>/serve-file?file=<?= htmlspecialchars($_SESSION['user']['logo_url']) ?>&type=image"
                     alt="Logo">
            <?php else: ?>
                <div class="letterhead-logo-placeholder">LOGO</div>
            <?php endif; ?>
        </div>

        <!-- Org Name -->
        <div class="letterhead-org">
            <div class="org-name"><?= htmlspecialchars($_SESSION['user']['branch_name'] ?? 'ድርጅቱ ስም') ?></div>
            <?php if (!empty($_SESSION['user']['org_subtitle'])): ?>
                <div class="org-sub"><?= htmlspecialchars($_SESSION['user']['org_subtitle']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Contact Info (right side) -->
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
        <span class="ref">
            ቁጥር: <?= htmlspecialchars($employee['ref_number'] ?? '___/___/___') ?>
        </span>
        <span>ቀን: <?= date('d/m/Y') ?></span>
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
            <b><?= htmlspecialchars(
                trim(
                    ($employee['first_name'] ?? '') . ' ' .
                    ($employee['father_name'] ?? '') . ' ' .
                    ($employee['g_father_name'] ?? '')
                )
            ) ?></b>
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

        <p>
            ይህ ሰነድ ለሚፈለጉበት ዓላማ እንዲያገለግላቸው ተሰጥቷቸዋል።
        </p>
    </div>

    <!-- ══ CLOSING ══ -->
    <div class="letter-closing">
        <p>ከሰላምታ ጋር፣</p>
    </div>

    <!-- ══ SIGNATURE + STAMP ══ -->
    <div class="sig-stamp-row">

        <div class="sig-block">
            <span class="sig-line"></span>
            <div class="sig-name">
                <?= htmlspecialchars(
                    trim(
                        ($_SESSION['user']['first_name'] ?? '') . ' ' .
                        ($_SESSION['user']['father_name'] ?? '')
                    )
                ) ?>
            </div>
            <div class="sig-label">
                <?= htmlspecialchars($_SESSION['user']['job_title'] ?? 'የሰው ኃይል አስተዳደር ኃላፊ') ?>
            </div>
            <div class="sig-label"><?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?></div>
        </div>

        <div class="stamp-box">የድርጅቱ<br>ማህተም</div>

    </div>

    <!-- ══ PAGE FOOTER ══ -->
    <div class="page-footer">
        <?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?>
        <?php if (!empty($_SESSION['user']['address'])): ?>
            &nbsp;|&nbsp; <?= htmlspecialchars($_SESSION['user']['address']) ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['user']['phone'])): ?>
            &nbsp;|&nbsp; <?= htmlspecialchars($_SESSION['user']['phone']) ?>
        <?php endif; ?>
    </div>

</div><!-- /.page -->
</body>
</html>