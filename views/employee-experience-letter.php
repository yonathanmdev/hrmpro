<?php
use App\Helpers\EthiopianDateHelper;
$is_exprience_registration_page = true;

function calcDuration(string $start, ?string $end): array {
    $s    = new DateTime($start);
    $e    = $end ? new DateTime($end) : new DateTime();
    $diff = $s->diff($e);
    return [
        'years'      => $diff->y,
        'months'     => $diff->m,
        'days'       => $diff->d,
        'total_days' => (int) $s->diff($e)->days,
    ];
}

function formatDurationBadges(array $d): string {
    $html = '';
    $seen = 0;
    if ($d['years']  > 0) { $html .= '<span class="dur-badge">' . $d['years']  . ' ዓመት</span>'; $seen++; }
    if ($d['months'] > 0) { $p = $seen > 0 ? ' ከ' : ''; $html .= '<span class="dur-badge">' . $p . $d['months'] . ' ወር</span>'; $seen++; }
    if ($d['days']   > 0) { $p = $seen > 0 ? ' ከ' : ''; $html .= '<span class="dur-badge">' . $p . $d['days']   . ' ቀን</span>'; }
    if (!$html) $html = '<span class="dur-badge muted">—</span>';
    return $html;
}

function calcLeaveOverlapDays(
    string $expStart,
    ?string $expEnd,
    string $leaveStart,
    string $leaveEnd
): int {
    $expEndDate     = $expEnd ? new DateTime($expEnd) : new DateTime();
    $expStartDate   = new DateTime($expStart);
    $leaveStartDate = new DateTime($leaveStart);
    $leaveEndDate   = new DateTime($leaveEnd);

    $overlapStart = $expStartDate > $leaveStartDate ? $expStartDate : $leaveStartDate;
    $overlapEnd   = $expEndDate   < $leaveEndDate   ? $expEndDate   : $leaveEndDate;

    if ($overlapStart >= $overlapEnd) return 0;
    return (int) $overlapStart->diff($overlapEnd)->days;
}

function calcNetExperience(int $totalDays, string $expStart, ?string $expEnd, array $studyLeaves): array {
    $deducted = 0;
    $matched  = [];
    foreach ($studyLeaves as $leave) {
        if (empty($leave['start_date']) || empty($leave['end_date'])) continue;
        $overlap = calcLeaveOverlapDays($expStart, $expEnd, $leave['start_date'], $leave['end_date']);
        if ($overlap > 0) {
            $deducted += $overlap;
            $matched[] = array_merge($leave, ['overlap_days' => $overlap]);
        }
    }
    return [
        'net_days'      => max(0, $totalDays - $deducted),
        'deducted_days' => $deducted,
        'leaves'        => $matched,
    ];
}

function daysToYMD(int $days): array {
    $start = new DateTime('@0');
    $end   = new DateTime('@' . ($days * 86400));
    $diff  = $start->diff($end);
    return [
        'years'      => $diff->y,
        'months'     => $diff->m,
        'days'       => $diff->d,
        'total_days' => $days,
    ];
}

// ── Study leave & active leave resolution ────────────────────────────────────
$studyLeaves    = $studyLeaves ?? [];
$isOnStudyLeave = ($employee['status'] ?? '') === 'Study Leave';

$activeLeave = null;
if ($isOnStudyLeave && !empty($studyLeaves)) {
    usort($studyLeaves, fn($a, $b) => strcmp($b['start_date'], $a['start_date']));
    $activeLeave = $studyLeaves[0];
}

// ── Pre-build study leave display rows ───────────────────────────────────────
$studyLeaveRows = [];
$totalStudyDays = 0;

foreach ($studyLeaves as $sl) {
    if (empty($sl['start_date'])) continue;

    $isActive   = $isOnStudyLeave && $activeLeave
                  && $sl['start_date'] === $activeLeave['start_date'];
    $endForCalc = !empty($sl['end_date']) ? $sl['end_date'] : date('Y-m-d');
    $dur        = calcDuration($sl['start_date'], $endForCalc);

    $sp       = explode('-', $sl['start_date']);
    $startEth = EthiopianDateHelper::toEthCalendar($sp[2], $sp[1], $sp[0]);

    $endEth = null;
    if (!empty($sl['end_date'])) {
        $ep     = explode('-', $sl['end_date']);
        $endEth = EthiopianDateHelper::toEthCalendar($ep[2], $ep[1], $ep[0]);
    }

    $totalStudyDays += $dur['total_days'];

    $studyLeaveRows[] = [
        'start_eth'    => $startEth,
        'end_eth'      => $endEth,
        'reason'       => $sl['reason'] ?? '',
        'duration_ymd' => daysToYMD($dur['total_days']),
        'is_active'    => $isActive,
    ];
}

// ── Today in Ethiopian calendar ───────────────────────────────────────────────
$todayStr   = date('Y-m-d');
$todayParts = explode('-', $todayStr);
$todayEth   = EthiopianDateHelper::toEthCalendar($todayParts[2], $todayParts[1], $todayParts[0]);

// ── Hire date in Ethiopian calendar ──────────────────────────────────────────
$hireStart = $employee['date_of_employed'] ?? null;
$hireEth   = null;
if ($hireStart) {
    $hp      = explode('-', $hireStart);
    $hireEth = EthiopianDateHelper::toEthCalendar($hp[2], $hp[1], $hp[0]);
}
?>
<!DOCTYPE html>
<html lang="am" dir="ltr"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ደብረ ታቦር ከተማ አስተዳደር ት/ት መምሪያ — የሥራ ልምድ</title>

    <style>
        /* ═══════════════════════════════════════
           FONTS
        ═══════════════════════════════════════ */
        @import url('https://fonts.googleapis.com/css2?family=Noto+Serif+Ethiopic:wght@400;500;600;700&family=Noto+Sans+Ethiopic:wght@300;400;500;600&display=swap');

        /* ═══════════════════════════════════════
           CSS VARIABLES
        ═══════════════════════════════════════ */
        :root {
            --brand:        #1a3a5c;
            --brand-mid:    #2563a8;
            --brand-light:  #dbeafe;
            --accent:       #c8973a;
            --accent-light: #fef3c7;
            --ink:          #111827;
            --ink-soft:     #374151;
            --ink-muted:    #6b7280;
            --line:         #d1d5db;
            --line-light:   #f3f4f6;
            --page-bg:      #ffffff;
            --header-h:     160px;
            --footer-h:     48px;
        }

        /* ═══════════════════════════════════════
           RESET & BASE
        ═══════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            font-family: 'Noto Sans Ethiopic', 'Noto Serif Ethiopic', serif;
            font-size: 10pt;
            color: var(--ink);
            background: #e5e7eb;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ═══════════════════════════════════════
           SCREEN PREVIEW WRAPPER
        ═══════════════════════════════════════ */
        .preview-shell {
            max-width: 900px;
            margin: 24px auto;
            padding: 0;
        }

        /* ═══════════════════════════════════════
           PAGE SIMULATION (screen only)
        ═══════════════════════════════════════ */
        .page-wrap {
            background: var(--page-bg);
            box-shadow: 0 4px 24px rgba(0,0,0,.18);
            border-radius: 3px;
            margin-bottom: 32px;
            padding: 0;
            overflow: hidden;
        }

        /* ═══════════════════════════════════════
           PRINT HEADER  ← USER DEFINED PRESERVED
        ═══════════════════════════════════════ */
        .print-header {
            display: block;
            width: 100%;
        }

        .letterhead {
            display: flex;
            align-items: stretch;
            min-height: var(--header-h);
            padding: 20px 32px;
            gap: 20px;
            background: var(--page-bg);
            border-bottom: 3px solid var(--brand);
        }

        /* Logo column */
        .letterhead-logo {
            flex: 0 0 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .letterhead-logo img {
            max-width: 90px;
            max-height: 90px;
            object-fit: contain;
        }
        .letterhead-logo-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 6px;
            background: var(--brand-light);
            border: 2px dashed var(--brand-mid);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10pt;
            font-weight: 600;
            color: var(--brand-mid);
            letter-spacing: .05em;
        }

        /* Org name center column */
        .letterhead-org {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 16px;
            border-left: 1px solid var(--line);
            border-right: 1px solid var(--line);
        }
        .org-name {
            font-family: 'Noto Serif Ethiopic', serif;
            font-size: 17pt;
            font-weight: 700;
            color: var(--brand);
            line-height: 1.3;
            letter-spacing: .01em;
        }
        .org-sub {
            font-size: 9pt;
            color: var(--ink-soft);
            margin-top: 4px;
            line-height: 1.4;
        }

        /* Bottom accent stripe */
        .letterhead-accent {
            height: 6px;
            background: linear-gradient(90deg, var(--brand) 0%, var(--brand-mid) 60%, var(--accent) 100%);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ═══════════════════════════════════════
           REPORT TITLE BAND
        ═══════════════════════════════════════ */
        .report-title-band {
            padding: 18px 32px 14px;
            border-bottom: 1px solid var(--line);
        }
        .report-title-band h1 {
            font-family: 'Noto Serif Ethiopic', serif;
            font-size: 14pt;
            font-weight: 700;
            color: var(--brand);
        }
        .report-meta {
            display: flex;
            gap: 24px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .report-meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .meta-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--ink-muted);
            font-weight: 600;
        }
        .meta-value {
            font-size: 9.5pt;
            color: var(--ink);
            font-weight: 500;
        }

        /* ═══════════════════════════════════════
           EMPLOYEE SUMMARY CARD
        ═══════════════════════════════════════ */
        .employee-card {
            margin: 18px 32px;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0;
            border: 1px solid var(--line);
            border-radius: 6px;
            overflow: hidden;
        }
        .employee-card-accent {
            width: 8px;
            background: linear-gradient(180deg, var(--brand) 0%, var(--brand-mid) 100%);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .employee-card-body {
            padding: 14px 18px;
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }
        .employee-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--brand-light);
            flex-shrink: 0;
        }
        .employee-avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--brand-light);
            border: 2px solid var(--brand-mid);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16pt;
            font-weight: 700;
            color: var(--brand);
            flex-shrink: 0;
        }
        .employee-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 3px;
        }
        .employee-name {
            font-family: 'Noto Serif Ethiopic', serif;
            font-size: 13pt;
            font-weight: 700;
            color: var(--brand);
        }
        .employee-position {
            font-size: 9.5pt;
            color: var(--ink-soft);
        }
        .employee-id {
            font-size: 8.5pt;
            color: var(--ink-muted);
        }
        .employee-fields {
            margin-left: auto;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .emp-field {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .emp-field .label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--ink-muted);
        }
        .emp-field .value {
            font-size: 9pt;
            font-weight: 500;
            color: var(--ink);
        }

        /* ═══════════════════════════════════════
           EMPLOYEE INFO LIST
        ═══════════════════════════════════════ */
        .emp-info-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0;
            width: 100%;
        }

        .emp-info-list li {
            display: flex;
            align-items: baseline;
            gap: 6px;
            padding: 7px 12px;
            border-bottom: 1px solid var(--line);
            font-size: 9pt;
            color: var(--ink);
            line-height: 1.5;
        }

        .emp-info-list li:nth-child(odd) {
            border-right: 1px solid var(--line);
        }

        /* last row: don't double-border the bottom */
        .emp-info-list li:nth-last-child(-n+2) {
            border-bottom: 0;
        }
        /* if odd total, last item spans full width */
        .emp-info-list li:last-child:nth-child(odd) {
            grid-column: 1 / -1;
            border-right: 0;
        }

        .emp-info-list li span {
            font-size: 8pt;
            font-weight: 700;
            color: var(--brand);
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* ═══════════════════════════════════════
           SECTION HEADING
        ═══════════════════════════════════════ */
        .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 32px 0;
        }
        .section-heading .bar {
            width: 4px;
            height: 18px;
            background: var(--accent);
            border-radius: 2px;
            flex-shrink: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .section-heading h2 {
            font-family: 'Noto Serif Ethiopic', serif;
            font-size: 11pt;
            font-weight: 700;
            color: var(--brand);
        }
        .section-count {
            margin-left: auto;
            font-size: 8pt;
            color: var(--ink-muted);
            background: var(--line-light);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 2px 10px;
        }

        /* ═══════════════════════════════════════
           EXPERIENCE TIMELINE
        ═══════════════════════════════════════ */
        .experience-list {
            margin: 12px 32px 0;
        }

        .exp-item {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 0;
            position: relative;
            page-break-inside: avoid;
        }

        /* Timeline spine */
        .exp-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 107px;
            top: 28px;
            bottom: 0;
            width: 1px;
            background: var(--line);
        }

        /* Date column */
        .exp-date-col {
            padding: 14px 16px 14px 0;
            text-align: right;
            position: relative;
        }
        .exp-date-range {
            font-size: 8pt;
            color: var(--ink-muted);
            line-height: 1.5;
        }
        .exp-date-range .year {
            font-size: 9.5pt;
            font-weight: 600;
            color: var(--brand);
        }
        .exp-duration {
            font-size: 7.5pt;
            color: var(--accent);
            font-weight: 600;
            margin-top: 2px;
            display: block;
        }

        /* Dot on timeline */
        .exp-dot {
            position: absolute;
            right: -7px;
            top: 18px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: var(--brand-mid);
            border: 2px solid var(--page-bg);
            box-shadow: 0 0 0 1px var(--brand-mid);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .exp-dot.current {
            background: var(--accent);
            box-shadow: 0 0 0 1px var(--accent);
        }

        /* Content column */
        .exp-content-col {
            padding: 10px 0 16px 20px;
            border-left: 0;
        }

        .exp-card {
            background: var(--line-light);
            border: 1px solid var(--line);
            border-left: 3px solid var(--brand-mid);
            border-radius: 0 5px 5px 0;
            padding: 12px 16px;
        }
        .exp-card.current-job {
            border-left-color: var(--accent);
            background: var(--accent-light);
        }

        .exp-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 4px;
        }
        .exp-title {
            font-family: 'Noto Serif Ethiopic', serif;
            font-size: 10.5pt;
            font-weight: 700;
            color: var(--brand);
            line-height: 1.35;
        }
        .exp-type-badge {
            flex-shrink: 0;
            font-size: 7.5pt;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
            background: var(--brand-light);
            color: var(--brand);
            border: 1px solid var(--brand-mid);
            white-space: nowrap;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .exp-type-badge.current {
            background: var(--accent-light);
            color: #92400e;
            border-color: var(--accent);
        }

        .exp-company {
            font-size: 9.5pt;
            color: var(--ink-soft);
            font-weight: 500;
            margin-bottom: 6px;
        }

        .exp-responsibilities {
            font-size: 8.5pt;
            color: var(--ink-soft);
            line-height: 1.7;
            border-top: 1px solid var(--line);
            padding-top: 8px;
            margin-top: 6px;
        }
        .exp-responsibilities-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--ink-muted);
            font-weight: 600;
            margin-bottom: 4px;
            display: block;
        }

        /* ═══════════════════════════════════════
           SUMMARY STATS ROW
        ═══════════════════════════════════════ */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            margin: 18px 32px 0;
            border: 1px solid var(--line);
            border-radius: 6px;
            overflow: hidden;
        }
        .stat-cell {
            padding: 12px 16px;
            text-align: center;
            border-right: 1px solid var(--line);
        }
        .stat-cell:last-child { border-right: 0; }
        .stat-value {
            font-family: 'Noto Serif Ethiopic', serif;
            font-size: 15pt;
            font-weight: 700;
            color: var(--brand);
            line-height: 1;
        }
        .stat-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--ink-muted);
            margin-top: 4px;
        }

        /* ═══════════════════════════════════════
           PRINT FOOTER  ← USER DEFINED PRESERVED
        ═══════════════════════════════════════ */
        .print-footer {
            display: block;
            margin-top: 24px;
        }
        .page-footer {
            border-top: 2px solid var(--brand);
            padding: 8px 32px;
            font-size: 8pt;
            color: var(--ink-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: var(--footer-h);
            background: var(--page-bg);
        }
        .page-footer-right {
            font-size: 7.5pt;
            color: var(--ink-muted);
        }
        .page-footer-center {
            font-size: 7.5pt;
            color: var(--ink-muted);
            display: flex;
            gap: 4px;
            align-items: center;
        }

        /* Page number placeholder (populated by JS for screen; @page for print) */
        .page-num {
            font-size: 7.5pt;
            color: var(--ink-muted);
        }

        /* ═══════════════════════════════════════
           PRINT BUTTON (screen only)
        ═══════════════════════════════════════ */
        .print-controls {
            max-width: 900px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 0 4px;
        }
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--brand);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 9px 20px;
            font-size: 9.5pt;
            font-family: inherit;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: .01em;
            transition: background .2s;
        }
        .btn-print:hover { background: var(--brand-mid); }
        .btn-print svg { width: 16px; height: 16px; }

        /* ═══════════════════════════════════════
           @MEDIA PRINT
        ═══════════════════════════════════════ */
        @media print {
            html, body {
                background: white;
                font-size: 9.5pt;
            }

            .preview-shell { margin: 0; max-width: 100%; }
            .page-wrap {
                box-shadow: none;
                border-radius: 0;
                margin-bottom: 0;
            }
            .print-controls { display: none; }

            /* Header and footer repeat on every page */
            .print-header {
                position: running(header);
            }
            .print-footer {
                position: running(footer);
            }

            @page {
                size: A4 portrait;
                margin: 0;

                @top-center { content: element(header); }
                @bottom-center { content: element(footer); }
            }

            /* Fallback for browsers that don't support running elements */
            .print-header, .print-footer { position: static; }

            .exp-item {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .employee-card {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Reapply gradient/color for print */
            .letterhead-accent,
            .employee-card-accent,
            .exp-type-badge,
            .exp-dot {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }


        /* ══ META ══ */
        .meta-row {
            display: flex; flex-direction: column; align-items: flex-end;
            font-size: 9pt; color: #444; margin-bottom: 10px; text-align: right;
        }
        .meta-row .ref { font-style: italic; }
        /* ═══════════════════════════════════════
           EXPERIENCE TABLE
        ═══════════════════════════════════════ */
        .experience-table-wrap {
            margin: 14px 32px 0;
            border: 1px solid var(--line);
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }

        .experience-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.7pt;
        }

        .experience-table thead {
            background: var(--brand);
            color: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .experience-table th {
            padding: 11px 10px;
            text-align: center;
            font-weight: 700;
            border-right: 1px solid rgba(255,255,255,.15);
            font-size: 8pt;
        }

        .experience-table th:last-child {
            border-right: 0;
        }

        .experience-table td {
            padding: 10px 8px;
            border-top: 1px solid var(--line);
            vertical-align: middle;
        }

        .experience-table tbody tr:nth-child(even) {
            background: #fafafa;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .experience-table tbody tr:hover {
            background: #f3f6fb;
        }

        .experience-table .center {
            text-align: center;
        }

        .table-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            background: var(--brand-light);
            color: var(--brand);
            border: 1px solid var(--brand-mid);
            font-size: 7.5pt;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 7.5pt;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-badge.active {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #22c55e;
        }

        .status-badge.inactive {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .empty-row {
            text-align: center;
            padding: 18px !important;
            color: var(--ink-muted);
            font-style: italic;
        }

        /* print */
        @media print {

            .experience-table-wrap {
                break-inside: auto;
                page-break-inside: auto;
            }

            .experience-table tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .experience-table thead {
                display: table-header-group;
            }

            .experience-table tfoot {
                display: table-footer-group;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .page-wrap {
                page-break-after: always;
                overflow: visible !important;
            }

            .print-header,
            .print-footer {
                width: 100%;
            }

            .print-footer {
                margin-top: 20px;
            }

            table,
            tr,
            td,
            th {
                page-break-inside: avoid !important;
            }

            .signature-block,
            .employee-card,
            .total-exp-box,
            .experience-table-wrap {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        .badge-now {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 7.5pt;
            font-weight: 700;
        }

        .active-study-row {
            background: #fff7ed !important;
        }

        .status-badge.warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }

        .total-exp-box {
            margin-top: 14px;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-left: 4px solid var(--brand);
            border-radius: 6px;
            background: #fafafa;
            font-size: 9pt;
        }

        .total-exp-box .label {
            display: block;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 6px;
        }

        .main-letter-title {
            text-align: center;
            font-family: 'Noto Serif Ethiopic', serif;
            font-size: 14pt;
            font-weight: 700;
            color: var(--brand);
            margin: 16px 0 10px;
            line-height: 1.7;
        }

        /* ═══════════════════════════════════════
           CLOSING GREETING
        ═══════════════════════════════════════ */
        .closing-greeting {
            text-align: right;
            margin: 20px 32px 6px;
            font-size: 10pt;
            font-weight: 600;
            color: var(--ink);
        }

        /* ═══════════════════════════════════════
           SIGNATURE BLOCK
        ═══════════════════════════════════════ */
        .signature-block {
            margin: 8px 32px 0;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }
        .sig-col {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 220px;
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid var(--ink);
            height: 32px;
            margin-bottom: 4px;
        }
        .sig-label {
            font-size: 9pt;
            font-weight: 600;
            color: var(--ink);
        }
        .sig-name {
            font-size: 8pt;
            color: var(--ink-muted);
        }
    </style>
</head>
<body>

<!-- ══ PRINT CONTROLS (screen only) ══ -->
<div class="print-controls">
    <button class="btn-print" id="print-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        አትም
    </button>
</div>

<div class="preview-shell">
<div class="page-wrap">

    <!-- ══ PRINT HEADER ══ -->
    <div class="print-header">
        <div class="letterhead">
            <div class="letterhead-logo">
                <img src="<?= rtrim($_ENV['BASE_URL'] ?? '', '/') ?>/serve-file?file=<?= htmlspecialchars($_SESSION['user']['logo_url']) ?>&type=image" 
                   alt="<?= htmlspecialchars($_SESSION['user']['alt_name'] ?? '') ?>" 
                   class="img-fluid">
            </div>
            <div class="letterhead-org">
                <div class="org-name"><?= $_SESSION['user']['branch_name'] ?></div>
            </div>
        </div>
        <div class="letterhead-accent"></div>
    </div><!-- /.print-header -->


    <!-- ═══════════════════════════════════════
         REPORT TITLE BAND
    ═══════════════════════════════════════ -->
    <div class="report-title-band">

        <!-- ══ META ROW ══ -->
        <div class="meta-row">
            <span class="ref">
                ቁጥር:
                <?= htmlspecialchars($employee['ref_number'] ?? '_____/_____/___') ?>
            </span>

            <span>
                ቀን:
                <?= EthiopianDateHelper::getMonthName($todayEth['month']) ?>
                <?= $todayEth['day'] ?>
                <?= $todayEth['year'] ?> ዓ.ም
            </span>
        </div>

        <div class="employee-name">
            ለአቶ/ወ/ሮ/ወ/ሪ/ት፦  <?= htmlspecialchars(
                trim(
                    ($employee['first_name'] ?? '') . ' ' .
                    ($employee['father_name'] ?? '') . ' ' .
                    ($employee['g_father_name'] ?? '')
                ) ?: '—'
            ) ?>
            <p><u>ባሉበት</u></p>
        </div>

        <!-- ══ TITLE ══ -->
        <h1 class="main-letter-title">
            ጉዳዩ፡-<u>የስራ ልምድ ማስረጃ መስጠትን ይመለከታል</u>
        </h1>

        <!-- ══ REPORT META ══ -->
        <div class="report-meta">
            <div class="report-meta-item">
                <span class="meta-value">
                   የ<?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?>
                   የስራ ባልደረባ የሆኑት አቶ/ወ/ሮ/ወ/ሪት
                   <?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? '') . ' ' . ($employee['g_father_name'] ?? ''))) ?>
                   የተባሉት ሠራተኛ የስራ ልምድ ማስረጃ ይሠጠኝ በማለት በቀን
                   <?= EthiopianDateHelper::getMonthName($todayEth['month']) ?> <?= $todayEth['day'] ?> <?= $todayEth['year'] ?> ዓ.ም
                   በተፃፈ ማመልከቻ ጠይቀዋል፡፡
                </span>
            </div>
        </div>

    </div><!-- /.report-title-band -->


    <!-- ═══════════════════════════════════════
         EMPLOYEE SUMMARY CARD
    ═══════════════════════════════════════ -->
    <div class="employee-card">

        <div class="employee-card-accent"></div>

        <div class="employee-card-body">

            <ul class="emp-info-list">
                <?php if ($hireEth): ?>
                <li><span>የቅጥር ዘመን፡</span>
                    <?= EthiopianDateHelper::getMonthName($hireEth['month']) ?> <?= $hireEth['day'] ?> <?= $hireEth['year'] ?>
                </li>
                <?php endif; ?>
                <?php if (!empty($employee['education_level'])): ?>
                <li><span>የት/ት ደረጃ፡</span> <?= htmlspecialchars($employee['education_level']) ?></li>
                <?php endif; ?>
                <?php if (!empty($employee['department'])): ?>
                <li><span>የሰለጠኑበት ሙያ፡</span> <?= htmlspecialchars($employee['department']) ?></li>
                <?php endif; ?>
                <?php if (!empty($employee['dereja'])): ?>
                <li><span>ደረጃ፡</span> <?= htmlspecialchars($employee['dereja']) ?></li>
                <?php endif; ?>
                <?php if (!empty($employee['job_identifier_no'])): ?>
                <li><span>የመ/መ/ ቁጥር፡</span> <?= htmlspecialchars($employee['job_identifier_no']) ?></li>
                <?php endif; ?>
                <?php if (!empty($employee['job_name'])): ?>
                <li><span>የስራ መደቡ መጠሪያ፡</span> <?= htmlspecialchars($employee['job_name']) ?></li>
                <?php endif; ?>
                <?php if (!empty($employee['pension_number'])): ?>
                <li><span>የጡረታ መ/ቁጥር፡</span> <?= htmlspecialchars($employee['pension_number']) ?></li>
                <?php endif; ?>
                <?php if (isset($employee['salary'])): ?>
                <li><span>የደመወዝ መጠን፡</span> <?= number_format((float)($employee['salary']), 2) ?></li>
                <?php endif; ?>
            </ul>

        </div>

    </div><!-- /.employee-card -->


    <!-- ═══════════════════════════════════════
         EXPERIENCE TABLE
    ═══════════════════════════════════════ -->
    <div class="section-heading">
        <div class="bar"></div>
        <h2>የሥራ ልምድ ዝርዝር</h2>
    </div>

    <div class="experience-table-wrap">

        <table class="experience-table">

            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>የሥራ ቦታ</th>
                    <th>የሥራ መደብ</th>
                    <th>የሥራ ዘመን</th>
                    <th style="width:22%">የተጣራ ልምድ</th>
                </tr>
            </thead>

            <tbody>

            <?php
            $counter = 1;
            $grandTotalDays = 0;
            $grandDeducted  = 0;
            ?>

            <!-- ═══════════════════════════════
                 MAIN EMPLOYMENT
            ═══════════════════════════════ -->
            <?php if (!empty($employee['date_of_employed'])): ?>

                <?php
                $expStart = $employee['date_of_employed'];

                if ($isOnStudyLeave && $activeLeave) {
                    $expEnd = $activeLeave['start_date'];

                    $priorLeaves = array_values(array_filter(
                        $studyLeaves,
                        fn($l) => $l['start_date'] !== $activeLeave['start_date']
                    ));
                } else {
                    $expEnd = $todayStr;
                    $priorLeaves = $studyLeaves;
                }

                $rawDuration = calcDuration($expStart, $expEnd);

                $net = calcNetExperience(
                    $rawDuration['total_days'],
                    $expStart,
                    $expEnd,
                    $priorLeaves
                );

                $grandTotalDays += $net['net_days'];
                $grandDeducted  += $net['deducted_days'];

                $sp = explode('-', $expStart);
                $startEth = EthiopianDateHelper::toEthCalendar($sp[2], $sp[1], $sp[0]);

                $ep = explode('-', $expEnd);
                $endEth = EthiopianDateHelper::toEthCalendar($ep[2], $ep[1], $ep[0]);
                ?>

                <tr>
                    <td class="center"><?= $counter++ ?></td>

                    <td>
                        <?= htmlspecialchars($_SESSION['user']['branch_name'] ?? '') ?>
                    </td>

                    <td>
                        <strong><?= htmlspecialchars($employee['job_name'] ?? '') ?></strong>
                    </td>

                    <td>
                        ከ<?= EthiopianDateHelper::getMonthName($startEth['month']) ?>
                        <?= $startEth['day'] ?>
                        <?= $startEth['year'] ?>

                        እስከ

                        <?php if ($isOnStudyLeave && $activeLeave): ?>

                            <?= EthiopianDateHelper::getMonthName($endEth['month']) ?>
                            <?= $endEth['day'] ?>
                            <?= $endEth['year'] ?>

                        <?php else: ?>

                            <span class="badge-now"> <?= EthiopianDateHelper::getMonthName($todayEth['month']) ?> <?= $todayEth['day'] ?> <?= $todayEth['year'] ?></span>

                        <?php endif; ?>
                    </td>

                    <td class="center">
                        <?= formatDurationBadges(daysToYMD($net['net_days'])) ?>
                    </td>
                </tr>

            <?php endif; ?>

            <!-- ═══════════════════════════════
                 EXTERNAL EXPERIENCES
            ═══════════════════════════════ -->
            <?php if (!empty($experiences)): ?>

                <?php foreach ($experiences as $exp): ?>

                    <?php
                    $rawDuration = calcDuration(
                        $exp['start_date'],
                        $exp['end_date'] ?? null
                    );

                    $net = calcNetExperience(
                        $rawDuration['total_days'],
                        $exp['start_date'],
                        $exp['end_date'] ?? null,
                        $studyLeaves
                    );

                    $grandTotalDays += $net['net_days'];
                    $grandDeducted  += $net['deducted_days'];

                    $sp = explode('-', $exp['start_date']);
                    $startEth = EthiopianDateHelper::toEthCalendar($sp[2], $sp[1], $sp[0]);

                    $endEth = null;

                    if (!empty($exp['end_date'])) {
                        $ep = explode('-', $exp['end_date']);
                        $endEth = EthiopianDateHelper::toEthCalendar($ep[2], $ep[1], $ep[0]);
                    }
                    ?>

                    <tr>

                        <td class="center"><?= $counter++ ?></td>

                        <td>
                            <?= htmlspecialchars($exp['company_name'] ?? '') ?>
                        </td>

                        <td>
                            <strong><?= htmlspecialchars($exp['job_title'] ?? '') ?></strong>
                        </td>

                        <td>
                            ከ<?= EthiopianDateHelper::getMonthName($startEth['month']) ?>
                            <?= $startEth['day'] ?>
                            <?= $startEth['year'] ?>

                            እስከ

                            <?php if ($endEth): ?>

                                <?= EthiopianDateHelper::getMonthName($endEth['month']) ?>
                                <?= $endEth['day'] ?>
                                <?= $endEth['year'] ?>

                            <?php else: ?>

                                <span class="badge-now"> <?= EthiopianDateHelper::getMonthName($todayEth['month']) ?> <?= $todayEth['day'] ?> <?= $todayEth['year'] ?></span>

                            <?php endif; ?>
                        </td>

                        <td class="center">
                            <?= formatDurationBadges(daysToYMD($net['net_days'])) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

    <?php if (!empty($studyLeaveRows)): ?>

    <div class="section-heading" style="margin-top:18px;">
        <div class="bar"></div>
        <h2>በት/ት ላይ የቆዩበት ጊዜ</h2>
    </div>

    <div class="experience-table-wrap">

        <table class="experience-table study-table">
            <tbody>

            <?php foreach ($studyLeaveRows as $i => $row): ?>

                <tr <?= $row['is_active'] ? 'class="active-study-row"' : '' ?>>

                    <td class="center"><?= $i + 1 ?></td>

                    <td>
                        ከ
                        <?= EthiopianDateHelper::getMonthName($row['start_eth']['month']) ?>
                        <?= $row['start_eth']['day'] ?>
                        <?= $row['start_eth']['year'] ?>

                        እስከ

                        <?php if ($row['end_eth']): ?>

                            <?= EthiopianDateHelper::getMonthName($row['end_eth']['month']) ?>
                            <?= $row['end_eth']['day'] ?>
                            <?= $row['end_eth']['year'] ?>

                        <?php else: ?>

                            <span class="badge-now"> <?= EthiopianDateHelper::getMonthName($todayEth['month']) ?> <?= $todayEth['day'] ?> <?= $todayEth['year'] ?></span>

                        <?php endif; ?>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>
        </table>

    </div>

    <?php endif; ?>

    <?php if ($grandTotalDays > 0): ?>

    <?php $netYMD = daysToYMD($grandTotalDays); ?>

    <div class="total-exp-box">
        <span class="label">በአጠቃላይ

        <?= formatDurationBadges($netYMD) ?> ገለገሉ መሆናቸውን እንገልጻለን። ያገለገሉ እና አሁንም በማገልገል ላይ ያሉ መሆናቸውን እንገልጻለን።
        </span>
    </div>

    <?php endif; ?>

    <!-- ══ GREETING ══ -->
    <p class="closing-greeting">ከሰላምታ ጋር፣</p>

    <!-- ══ SIGNATURE BLOCK ══ -->
    <div class="signature-block">
        <div class="sig-col">
            <div class="sig-line"></div>
            <div class="sig-label"><?= $_SESSION['user']['first_name'].' '.$_SESSION['user']['father_name'] ?></div>
        </div>
    </div><!-- /.signature-block -->

    <!-- ══ PRINT FOOTER ══ -->
    <div class="print-footer">
        <div class="page-footer">

            <span class="page-footer-center">
                <?php if (!empty($_SESSION['user']['phone_number'])): ?>
                    <span>&#x260E; <?= htmlspecialchars($_SESSION['user']['phone_number']) ?></span>
                <?php endif; ?>
                <?php if (!empty($_SESSION['user']['postal_code'])): ?>
                    &nbsp;|&nbsp; ፖ.ሳ.ቁ <?= htmlspecialchars($_SESSION['user']['postal_code']) ?>
                <?php endif; ?>
            </span>
            <span class="page-footer-right page-num">ገጽ 1</span>
        </div>
    </div><!-- /.print-footer -->

</div><!-- /.page-wrap -->
</div><!-- /.preview-shell -->

<script nonce="<?php echo $GLOBALS['nonce']; ?>">
    document.addEventListener('DOMContentLoaded', () => {
        // Print button
        const btn = document.getElementById('print-btn');
        if (btn) btn.addEventListener('click', () => window.print());

        // Page number in footer (screen preview only — CSS counters handle print)
        const pageNums = document.querySelectorAll('.page-num');
        pageNums.forEach(el => { el.textContent = 'ገጽ 1'; });
    });
</script>
</body>
</html>