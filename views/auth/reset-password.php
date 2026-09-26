<?php
// src/Views/auth/reset-password.php

$nonce = $GLOBALS['nonce'] ?? '';
?>
<!DOCTYPE html>
<html lang="am">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>የይለፍ ቃል መቀየሪያ</title>

    <style nonce="<?= htmlspecialchars($nonce) ?>">

        * { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            font-family: "Noto Sans Ethiopic", "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #f4f7fb 0%, #eef3f9 50%, #f8fafc 100%);
            color: #1f2937;
        }

        .reset-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .reset-container { width: 100%; max-width: 460px; }

        .brand-section { text-align: center; margin-bottom: 28px; }

        .page-title {
            margin: 0 0 8px;
            font-size: 25px;
            font-weight: 700;
            color: #172033;
        }

        .page-subtitle {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
        }

        .reset-card {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .reset-card-body { padding: 38px; }

        .custom-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 0;
            border-radius: 10px;
            padding: 13px 14px;
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group { margin-bottom: 24px; }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .password-group { display: flex; align-items: stretch; width: 100%; }

        .password-icon {
            width: 48px;
            min-width: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d1d5db;
            border-right: 0;
            border-radius: 10px 0 0 10px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 18px;
        }

        .password-input {
            min-width: 0;
            flex: 1;
            height: 48px;
            border: 1px solid #d1d5db;
            border-left: 0;
            border-right: 0;
            border-radius: 0;
            padding: 0 12px;
            font-size: 14px;
            color: #111827;
            outline: none;
            box-shadow: none;
        }

        .password-input:focus { border-color: #86b7fe; box-shadow: none; }

        .password-toggle {
            width: 48px;
            min-width: 48px;
            border: 1px solid #d1d5db;
            border-left: 0;
            border-radius: 0 10px 10px 0;
            background: #ffffff;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 13px;
            font-weight: 600;
        }

        .password-toggle:hover { background: #f3f4f6; color: #374151; }

        .password-toggle:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
        }

        .password-input.is-invalid { border-color: #dc3545; }
        .password-input.is-valid { border-color: #198754; }

        .password-group.invalid .password-icon,
        .password-group.invalid .password-toggle { border-color: #dc3545; }

        .password-group.valid .password-icon,
        .password-group.valid .password-toggle { border-color: #198754; }

        /* =====================================================
           Requirements checklist
           ===================================================== */

        .requirements {
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            display: none;
        }

        .requirements.show { display: block; }

        .req-item {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 12.5px;
            color: #6b7280;
            padding: 4px 0;
            transition: color 0.15s ease;
        }

        .req-dot {
            width: 16px;
            height: 16px;
            min-width: 16px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: transparent;
            transition: all 0.15s ease;
        }

        .req-item.met { color: #198754; }

        .req-item.met .req-dot {
            border-color: #198754;
            background: #198754;
            color: #ffffff;
        }

        .req-item.met .req-dot::after { content: "✓"; }

        /* =====================================================
           Match feedback (error + success)
           ===================================================== */

        .match-feedback {
            display: none;
            align-items: center;
            gap: 8px;
            margin-top: -8px;
            margin-bottom: 22px;
            padding: 11px 13px;
            border-radius: 9px;
            font-size: 13px;
        }

        .match-feedback.show { display: flex; }

        .match-feedback.error { background: #fff1f2; color: #b42318; }
        .match-feedback.success { background: #f0fdf4; color: #15803d; }

        .strength-container { margin-top: 10px; display: none; }
        .strength-container.show { display: block; }

        .strength-bar {
            height: 4px;
            width: 100%;
            overflow: hidden;
            border-radius: 10px;
            background: #e5e7eb;
        }

        .strength-progress {
            height: 100%;
            width: 0;
            border-radius: 10px;
            transition: width 0.25s ease, background 0.25s ease;
        }

        .strength-text { margin-top: 6px; font-size: 11px; color: #6b7280; }

        .submit-button {
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 10px;
            background: #0d6efd;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .submit-button:hover:not(:disabled) {
            background: #0b5ed7;
            box-shadow: 0 8px 18px rgba(13, 110, 253, 0.20);
        }

        .submit-button:active:not(:disabled) { transform: translateY(1px); }

        .submit-button:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            box-shadow: none;
        }

        .security-footer {
            margin-top: 22px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }

        @media (max-width: 576px) {
            .reset-wrapper { padding: 25px 14px; }
            .reset-card-body { padding: 28px 22px; }
            .page-title { font-size: 22px; }
        }
        /* Add this inside the existing <style> block, anywhere after .custom-alert */

.alert-danger {
    background: #fff1f2;
    border: 1px solid rgba(180, 35, 24, 0.2);
    border-left: 3px solid #b42318;
    color: #b42318;
}

.alert-success {
    background: #f0fdf4;
    border: 1px solid rgba(21, 128, 61, 0.2);
    border-left: 3px solid #15803d;
    color: #15803d;
}

.mb-4 { margin-bottom: 1.5rem; }

    </style>

</head>


<body>

<div class="reset-wrapper">

    <main class="reset-container">

        <div class="brand-section">
            <h1 class="page-title">የይለፍ ቃል መቀየሪያ</h1>
            <p class="page-subtitle">
                አዲስ የይለፍ ቃል በመፍጠር<br>
                የመለያዎን ደህንነት ያረጋግጡ።
            </p>
        </div>

        <section class="reset-card">
            <div class="reset-card-body">

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger custom-alert mb-4" role="alert">
                        <div><?= htmlspecialchars($_SESSION['error']) ?></div>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success custom-alert mb-4" role="alert">
                        <div><?= htmlspecialchars($_SESSION['success']) ?></div>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form method="POST" action="reset-password-process" id="resetForm" novalidate>

                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">

                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">አዲስ የይለፍ ቃል</label>

                        <div class="password-group" id="passwordGroup">
                            <div class="password-icon">🔒</div>
                            <input type="password" id="password" name="password" class="password-input"
                                   minlength="8" maxlength="128" autocomplete="new-password"
                                   placeholder="አዲስ የይለፍ ቃል ያስገቡ" required autofocus>
                            <button type="button" class="password-toggle" id="togglePassword"
                                    aria-label="የይለፍ ቃል አሳይ" title="የይለፍ ቃል አሳይ">አሳይ</button>
                        </div>

                        <!-- Strength meter -->
                        <div class="strength-container" id="strengthContainer">
                            <div class="strength-bar">
                                <div class="strength-progress" id="strengthProgress"></div>
                            </div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>

                        <!-- Live requirements checklist -->
                        <div class="requirements" id="requirements">
                            <div class="req-item" id="req-length">
                                <span class="req-dot"></span>
                                <span>ቢያንስ 8 ፊደላት</span>
                            </div>
                            <div class="req-item" id="req-lower">
                                <span class="req-dot"></span>
                                <span>ትንሽ ፊደል (a-z)</span>
                            </div>
                            <div class="req-item" id="req-upper">
                                <span class="req-dot"></span>
                                <span>ትልቅ ፊደል (A-Z)</span>
                            </div>
                            <div class="req-item" id="req-number">
                                <span class="req-dot"></span>
                                <span>ቁጥር (0-9)</span>
                            </div>
                            <div class="req-item" id="req-special">
                                <span class="req-dot"></span>
                                <span>የተለየ ምልክት (!@#$%...)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">የይለፍ ቃል ያረጋግጡ</label>

                        <div class="password-group" id="confirmGroup">
                            <div class="password-icon">🛡️</div>
                            <input type="password" id="password_confirm" name="password_confirm" class="password-input"
                                   minlength="8" maxlength="128" autocomplete="new-password"
                                   placeholder="የይለፍ ቃሉን እንደገና ያስገቡ" required>
                            <button type="button" class="password-toggle" id="toggleConfirmPassword"
                                    aria-label="የይለፍ ቃል አሳይ" title="የይለፍ ቃል አሳይ">አሳይ</button>
                        </div>
                    </div>

                    <!-- Match feedback: error OR success -->
                    <div id="matchError" class="match-feedback error" role="alert">
                        <span>⚠️ የይለፍ ቃላት አይመሳሰሉም።</span>
                    </div>

                    <div id="matchSuccess" class="match-feedback success" role="status">
                        <span>✅ የይለፍ ቃላት ተመሳሳይ ናቸው።</span>
                    </div>

                    <button type="submit" class="submit-button" id="submitBtn" disabled>
                        <span id="submitText">ቀይር</span>
                    </button>

                </form>
            </div>
        </section>

        <div class="security-footer">
            🔐 የመለያዎ ደህንነት ለእኛ አስፈላጊ ነው።
        </div>

    </main>
</div>

<script nonce="<?= htmlspecialchars($nonce) ?>">
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('resetForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirm');
    const passwordGroup = document.getElementById('passwordGroup');
    const confirmGroup = document.getElementById('confirmGroup');
    const matchError = document.getElementById('matchError');
    const matchSuccess = document.getElementById('matchSuccess');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const strengthContainer = document.getElementById('strengthContainer');
    const strengthProgress = document.getElementById('strengthProgress');
    const strengthText = document.getElementById('strengthText');
    const requirements = document.getElementById('requirements');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');

    const rules = {
        length:  { el: document.getElementById('req-length'),  test: v => v.length >= 8 },
        lower:   { el: document.getElementById('req-lower'),   test: v => /[a-z]/.test(v) },
        upper:   { el: document.getElementById('req-upper'),   test: v => /[A-Z]/.test(v) },
        number:  { el: document.getElementById('req-number'),  test: v => /[0-9]/.test(v) },
        special: { el: document.getElementById('req-special'), test: v => /[^A-Za-z0-9]/.test(v) },
    };

    function toggleVisibility(input, btn) {
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = 'ደብቅ';
        } else {
            input.type = 'password';
            btn.textContent = 'አሳይ';
        }
    }

    togglePassword.addEventListener('click', () => toggleVisibility(password, togglePassword));
    toggleConfirmPassword.addEventListener('click', () => toggleVisibility(confirmPassword, toggleConfirmPassword));

    function allRulesMet(value) {
        return Object.values(rules).every(r => r.test(value));
    }

    function updateRequirements(value) {
        let metCount = 0;
        Object.values(rules).forEach(rule => {
            const passed = rule.test(value);
            rule.el.classList.toggle('met', passed);
            if (passed) metCount++;
        });
        return metCount;
    }

    function updateStrength(value) {
        if (value.length === 0) {
            strengthContainer.classList.remove('show');
            requirements.classList.remove('show');
            return;
        }

        strengthContainer.classList.add('show');
        requirements.classList.add('show');

        const metCount = updateRequirements(value);

        const levels = [
            { width: 20,  color: '#dc3545', text: 'በጣም ደካማ' },
            { width: 40,  color: '#dc3545', text: 'ደካማ' },
            { width: 60,  color: '#fd7e14', text: 'መካከለኛ' },
            { width: 80,  color: '#0d6efd', text: 'ጥሩ' },
            { width: 100, color: '#198754', text: 'ጠንካራ' },
        ];

        const level = levels[Math.min(metCount, levels.length - 1)];
        strengthProgress.style.width = level.width + '%';
        strengthProgress.style.background = level.color;
        strengthText.textContent = level.text;
        strengthText.style.color = level.color;
    }

    function validateMatch() {
        const pwVal = password.value;
        const confirmVal = confirmPassword.value;

        matchError.classList.remove('show');
        matchSuccess.classList.remove('show');
        confirmPassword.classList.remove('is-invalid', 'is-valid');
        confirmGroup.classList.remove('invalid', 'valid');

        if (confirmVal.length === 0) return null; // untouched, no verdict yet

        if (pwVal !== confirmVal) {
            matchError.classList.add('show');
            confirmPassword.classList.add('is-invalid');
            confirmGroup.classList.add('invalid');
            return false;
        }

        matchSuccess.classList.add('show');
        confirmPassword.classList.add('is-valid');
        confirmGroup.classList.add('valid');
        return true;
    }

    function updateSubmitState() {
        const rulesOk = allRulesMet(password.value);
        const matchOk = validateMatch();
        submitBtn.disabled = !(rulesOk && matchOk === true);

        password.classList.toggle('is-valid', rulesOk && password.value.length > 0);
        password.classList.toggle('is-invalid', !rulesOk && password.value.length > 0);
        passwordGroup.classList.toggle('valid', rulesOk && password.value.length > 0);
        passwordGroup.classList.toggle('invalid', !rulesOk && password.value.length > 0);
    }

    password.addEventListener('input', function () {
        updateStrength(password.value);
        updateSubmitState();
    });

    confirmPassword.addEventListener('input', updateSubmitState);

    form.addEventListener('submit', function (event) {
        const rulesOk = allRulesMet(password.value);
        const matchOk = validateMatch();

        if (!rulesOk || matchOk !== true) {
            event.preventDefault();
            if (!rulesOk) {
                password.focus();
            } else {
                confirmPassword.focus();
            }
            return;
        }

        submitBtn.disabled = true;
        submitText.textContent = 'በማስቀመጥ ላይ...';
    });

});
</script>

</body>
</html>