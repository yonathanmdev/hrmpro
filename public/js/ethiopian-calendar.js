(function(){
    // 1. የኢትዮጵያ ዘመን አቆጣጠር ቅንጅቶች
    const EthiopianDateSettings = {
        defaultRule: "any", // "past", "future", "any"
    };

    // 2. የPast/Future/Any ቫሊዴሽን ቼክ
    function validateEthiopianDate(dateObj, rule) {
        const now = new Date();
        const today = gregorianToEthiopianParsed(
            now.getDate(),
            now.getMonth() + 1,
            now.getFullYear()
        );

        const { day: d, month: m, year: y } = dateObj;

        const isPast =
            y < today.year ||
            (y === today.year && m < today.month) ||
            (y === today.year && m === today.month && d <= today.day);

        const isFuture =
            y > today.year ||
            (y === today.year && m > today.month) ||
            (y === today.year && m === today.month && d >= today.day);

        if (rule === "past" && !isPast) return false;
        if (rule === "future" && !isFuture) return false;
        return true; 
    }

    /* -----------------------------
        CSS መርጫ (Styles)
    ----------------------------- */
    const style = document.createElement('style');
    style.textContent = `
        :root { --accent: #1A1208; --gold: #C8962A; --muted: #666; }
        .ethiopian-calendar-popup { 
            position: absolute; width: 340px; background: #fff; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.15); border-radius: 12px; 
            padding: 15px; z-index: 9999; border: 1px solid #ddd; display: none;
            font-family: 'Segoe UI', 'Noto Serif Ethiopic', sans-serif;
        }
        .cal-header-ui { display: flex; justify-content: space-between; align-items: center; background: var(--accent); padding: 10px; border-radius: 8px; color: var(--gold); }
        .cal-header-ui button { background: none; border: 1px solid var(--gold); color: var(--gold); border-radius: 50%; width: 28px; height: 28px; cursor: pointer; }
        .weekdayHeader { display: grid; grid-template-columns: repeat(7, 1fr); margin-top: 10px; border-bottom: 1px solid #eee; }
        .weekdayHeader div { text-align: center; font-size: 11px; color: var(--muted); padding: 8px 0; font-weight: bold; }
        .daysGrid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-top: 10px; }
        .cell { padding: 10px 0; text-align: center; border-radius: 6px; cursor: pointer; font-size: 13px; transition: 0.2s; }
        .cell:hover { background: #f0f0f0; }
        .cell.selected { background: var(--accent); color: var(--gold); font-weight: bold; }
        .cell.today { outline: 1.5px solid var(--gold); }
        .controls { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee; }
        .btn-ui { padding: 6px 12px; border-radius: 6px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-size: 12px; }
        .popup-list { position: absolute; background: #fff; border: 1px solid #ddd; height: 200px; width: 120px; overflow: auto; z-index: 10001; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .popup-list div { padding: 8px; cursor: pointer; font-size: 13px; }
        .popup-list div:hover { background: #f5f5f5; }
    `;
    document.head.appendChild(style);

    /* -----------------------------
        ኢትዮጵያዊ የሒሳብ ስሌቶች (Utilities)
    ----------------------------- */
    const ET_MONTHS = ["መስከረም","ጥቅምት","ኅዳር","ታኅሣስ","ጥር","የካቲት","መጋቢት","ሚያዝያ","ግንቦት","ሰኔ","ሐምሌ","ነሐሴ","ጳጉሜ"];
    function pad(n){ return String(n).padStart(2,'0'); }
    function formatEthiopian(o){ return `${pad(o.day)}/${pad(o.month)}/${o.year}`; }
    function parseDMY(s){ 
        if(!s) return null; 
        const p = s.trim().split('/'); 
        if(p.length !== 3) return null;
        return { day: parseInt(p[0]), month: parseInt(p[1]), year: parseInt(p[2]) };
    }

    // --- ያቀረብከው የኢትዮጵያ ወደ ግሪጎርያን መቀየሪያ ---
    function ethiopianToGregorian(ethiopianDay, ethiopianMonth, ethiopianYear) {
        let leapEffect=0, gregorianDay=0, gregorianMonth=0, gregorianYear=0, gcleapEffect=0;
        if ((((ethiopianYear-1)+5500) % 4) == 3) leapEffect=1; else leapEffect=0;

        if (ethiopianMonth == 1) {
            ethiopianMonth= 0;
            gregorianYear = ethiopianYear + 7;
            if (ethiopianDay <= (20 - leapEffect)) { gregorianMonth = 9; gregorianDay = ethiopianDay + 10 + leapEffect; }
            else { gregorianMonth = 10; gregorianDay = (leapEffect == 1) ? ethiopianDay - 19 : ethiopianDay - 20; }
        } else if (ethiopianMonth == 2) {
            gregorianYear = ethiopianYear + 7;
            if (ethiopianDay <= (21 - leapEffect)) { gregorianMonth = 10; gregorianDay = ethiopianDay + 10 + leapEffect; }
            else { gregorianMonth = 11; gregorianDay = (leapEffect == 1) ? ethiopianDay - 20 : ethiopianDay - 21; }
        } else if (ethiopianMonth == 3) {
            gregorianYear = ethiopianYear + 7;
            if (ethiopianDay <= (21 - leapEffect)) { gregorianMonth = 11; gregorianDay = ethiopianDay + 9 + leapEffect; }
            else { gregorianMonth = 12; gregorianDay = (leapEffect == 1) ? ethiopianDay - 20 : ethiopianDay - 21; }
        } else if (ethiopianMonth == 4) {
            if (ethiopianDay <= (22 - leapEffect)) { gregorianYear = ethiopianYear + 7; gregorianMonth = 12; gregorianDay = ethiopianDay + 9 + leapEffect; }
            else { gregorianYear = ethiopianYear + 8; gregorianMonth = 1; gregorianDay = (leapEffect == 1) ? ethiopianDay - 21 : ethiopianDay - 22; }
        } else if (ethiopianMonth == 5) {
            gregorianYear = ethiopianYear + 8;
            if (ethiopianDay <= (23 - leapEffect)) { gregorianMonth = 1; gregorianDay = ethiopianDay + 8 + leapEffect; }
            else { gregorianMonth = 2; gregorianDay = (leapEffect == 1) ? ethiopianDay - 22 : ethiopianDay - 23; }
        } else if (ethiopianMonth == 6) {
            gregorianYear = ethiopianYear + 8;
            gcleapEffect = (gregorianYear % 4 === 0 && (gregorianYear % 100 !== 0 || gregorianYear % 400 === 0)) ? 1 : 0;
            if (ethiopianDay <= (21 + gcleapEffect - leapEffect)) { gregorianMonth = 2; gregorianDay = ethiopianDay + 7 + leapEffect; }
            else { gregorianMonth = 3; gregorianDay = (leapEffect == 1) ? ethiopianDay - (20 + gcleapEffect) : ethiopianDay - (21 + gcleapEffect); }
        } else if (ethiopianMonth == 7) {
            gregorianYear = ethiopianYear + 8;
            if (ethiopianDay <= 22) { gregorianMonth = 3; gregorianDay = ethiopianDay + 9; }
            else { gregorianMonth = 4; gregorianDay = ethiopianDay - 22; }
        } else if (ethiopianMonth == 8) {
            gregorianYear = ethiopianYear + 8;
            if (ethiopianDay <= 22) { gregorianMonth = 4; gregorianDay = ethiopianDay + 8; }
            else { gregorianMonth = 5; gregorianDay = ethiopianDay - 22; }
        } else if (ethiopianMonth == 9) {
            gregorianYear = ethiopianYear + 8;
            if (ethiopianDay <= 23) { gregorianMonth = 5; gregorianDay = ethiopianDay + 8; }
            else { gregorianMonth = 6; gregorianDay = ethiopianDay - 23; }
        } else if (ethiopianMonth == 10) {
            gregorianYear = ethiopianYear + 8;
            if (ethiopianDay <= 23) { gregorianMonth = 6; gregorianDay = ethiopianDay + 7; }
            else { gregorianMonth = 7; gregorianDay = ethiopianDay - 23; }
        } else if (ethiopianMonth == 11) {
            gregorianYear = ethiopianYear + 8;
            if (ethiopianDay <= 24) { gregorianMonth = 7; gregorianDay = ethiopianDay + 7; }
            else { gregorianMonth = 8; gregorianDay = ethiopianDay - 24; }
        } else if (ethiopianMonth == 12) {
            gregorianYear = ethiopianYear + 8;
            if (ethiopianDay <= 25) { gregorianMonth = 8; gregorianDay = ethiopianDay + 6; }
            else { gregorianMonth = 9; gregorianDay = ethiopianDay - 25; }
        } else if (ethiopianMonth == 13) {
            gregorianYear = ethiopianYear + 8; gregorianMonth = 9; gregorianDay = ethiopianDay + 5;
        }
        return { day: gregorianDay, month: gregorianMonth, year: gregorianYear };
    }

    // --- ያቀረብከው ግሪጎርያን ወደ ኢትዮጵያ መቀየሪያ ---
    function gregorianToEthiopianParsed(day, month, year){
        let ethyear=0, ethmonth=0, ethday=0, ethleapEffect=0, ethleapEffect2=0;
        ethleapEffect = ((((year-9)+5500) % 4) == 3) ? 1 : 0;

        if (month == 1) {
            ethyear = year - 8;
            if (day <= (8 + ethleapEffect)) { ethmonth = 4; ethday = day + 22 - ethleapEffect; }
            else { ethmonth = 5; ethday = (ethleapEffect == 1) ? day - 9 : day - 8; }
        } else if (month == 2) {
            ethyear = year - 8;
            if (day <= (7 + ethleapEffect)) { ethmonth = 5; ethday = day + 23 - ethleapEffect; }
            else { ethmonth = 6; ethday = (ethleapEffect == 1) ? day - 8 : day - 7; }
        } else if (month == 3) {
            ethyear = year - 8;
            if (day <= 9) { ethmonth = 6; ethday = day + 21; }
            else { ethmonth = 7; ethday = day - 9; }
        } else if (month == 4) {
            ethyear = year - 8;
            if (day <= 8) { ethmonth = 7; ethday = day + 22; }
            else { ethmonth = 8; ethday = day - 8; }
        } else if (month == 5) {
            ethyear = year - 8;
            if (day <= 8) { ethmonth = 8; ethday = day + 22; }
            else { ethmonth = 9; ethday = day - 8; }
        } else if (month == 6) {
            ethyear = year - 8;
            if (day <= 7) { ethmonth = 9; ethday = day + 23; }
            else { ethmonth = 10; ethday = day - 7; }
        } else if (month == 7) {
            ethyear = year - 8;
            if (day <= 7) { ethmonth = 10; ethday = day + 23; }
            else { ethmonth = 11; ethday = day - 7; }
        } else if (month == 8) {
            ethyear = year - 8;
            if (day <= 6) { ethmonth = 11; ethday = day + 24; }
            else { ethmonth = 12; ethday = day - 6; }
        } else if (month == 9) {
            ethleapEffect2 = ((((year-8)+5500) % 4) == 3) ? 1 : 0;
            if (day <= 5) { ethyear = year - 8; ethmonth = 12; ethday = day + 25; }
            else if (day >= 6 && day <= (10 + ethleapEffect2)) { ethyear = year - 8; ethmonth = 13; ethday = day - 5; }
            else { ethyear = year - 7; ethmonth = 1; ethday = (ethleapEffect2 == 1) ? day - 11 : day - 10; }
        } else if (month == 10) {
            ethleapEffect2 = ((((year-8)+5500) % 4) == 3) ? 1 : 0;
            ethyear = year - 7;
            if (day <= (10 + ethleapEffect2)) { ethmonth = 1; ethday = (ethleapEffect2 == 1) ? day + 19 : day + 20; }
            else { ethmonth = 2; ethday = (ethleapEffect2 == 1) ? day - 11 : day - 10; }
        } else if (month == 11) {
            ethleapEffect2 = ((((year-8)+5500) % 4) == 3) ? 1 : 0;
            ethyear = year - 7;
            if (day <= (9 + ethleapEffect2)) { ethmonth = 2; ethday = (ethleapEffect2 == 1) ? day + 20 : day + 21; }
            else { ethmonth = 3; ethday = (ethleapEffect2 == 1) ? day - 10 : day - 9; }
        } else if (month == 12) {
            ethleapEffect2 = ((((year-8)+5500) % 4) == 3) ? 1 : 0;
            ethyear = year - 7;
            if (day <= (9 + ethleapEffect2)) { ethmonth = 3; ethday = (ethleapEffect2 == 1) ? day + 20 : day + 21; }
            else { ethmonth = 4; ethday = (ethleapEffect2 == 1) ? day - 10 : day - 9; }
        }
        return { day: ethday, month: ethmonth, year: ethyear };
    }

    function getMonthLength(y, m) { return m >= 1 && m <= 12 ? 30 : (((y-1+5500)%4 === 3) ? 6 : 5); }
    function weekdayOfStart(y, m) { const g = ethiopianToGregorian(1, m, y); return new Date(g.year, g.month-1, g.day).getDay(); }

    /* -----------------------------
        ካላንደር መገንቢያ (UI)
    ----------------------------- */
    const popup = document.createElement('div'); 
    popup.className = 'ethiopian-calendar-popup';
    popup.innerHTML = `
        <div class="cal-header-ui">
            <div>
                <button id="pY">&laquo;</button>
                <button id="pM">&lsaquo;</button>
            </div>
            <div style="position:relative;">
                <span id="lblM" style="cursor:pointer"></span> 
                <span id="lblY" style="cursor:pointer; font-weight:bold"></span>
                <div id="listY" class="popup-list" style="display:none"></div>
                <div id="listM" class="popup-list" style="display:none"></div>
            </div>
            <div>
                <button id="nM">&rsaquo;</button>
                <button id="nY">&raquo;</button>
            </div>
        </div>
        <div class="weekdayHeader">
            <div>እሁድ</div><div>ሰኞ</div><div>ማክ</div><div>ረቡዕ</div><div>ሐሙስ</div><div>ዓርብ</div><div>ቅዳሜ</div>
        </div>
        <div id="grid" class="daysGrid"></div>
        <div class="controls">
            <button id="btnT" class="btn-ui">ዛሬ (Today)</button>
            <button id="btnC" class="btn-ui">ዝጋ</button>
        </div>
    `;
    document.body.appendChild(popup);

    let activeInput = null, viewY = null, viewM = null, selected = null;

    function render() {
        popup.querySelector('#lblM').textContent = ET_MONTHS[viewM-1];
        popup.querySelector('#lblY').textContent = viewY;
        const grid = popup.querySelector('#grid');
        grid.innerHTML = '';
        
        const start = weekdayOfStart(viewY, viewM);
        const len = getMonthLength(viewY, viewM);
        const now = new Date();
        const today = gregorianToEthiopianParsed(now.getDate(), now.getMonth()+1, now.getFullYear());

        for(let i=0; i<start; i++) grid.appendChild(document.createElement('div'));

        for(let d=1; d<=len; d++) {
            const cell = document.createElement('div');
            cell.className = 'cell';
            cell.textContent = d;
            if(today.day===d && today.month===viewM && today.year===viewY) cell.classList.add('today');
            if(selected && selected.day===d && selected.month===viewM && selected.year===viewY) cell.classList.add('selected');

            cell.onclick = () => {
                const rule = activeInput.dataset.rule || EthiopianDateSettings.defaultRule;
                const picked = { day: d, month: viewM, year: viewY };
                
                if(!validateEthiopianDate(picked, rule)) {
                    alert(`የተሳሳተ ቀን! ይህ ቀን መሆን ያለበት: ${rule === 'past' ? 'ያለፈ' : 'የወደፊት'} ነው።`);
                    return;
                }
                selected = picked;
                activeInput.value = formatEthiopian(selected);
                
                // Greg output
                const gcTarget = activeInput.dataset.gregorian;
                if(gcTarget) {
                    const g = ethiopianToGregorian(d, viewM, viewY);
                    const targetEl = document.querySelector(gcTarget);
                    if(targetEl) targetEl.value = `${g.year}-${pad(g.month)}-${pad(g.day)}`;
                }

                popup.style.display = 'none';
            };
            grid.appendChild(cell);
        }
    }

    // Input Listeners
    document.querySelectorAll('.ethiopian-date').forEach(el => {
        el.addEventListener('focus', () => {
            activeInput = el;
            const val = parseDMY(el.value);
            const now = new Date();
            const today = gregorianToEthiopianParsed(now.getDate(), now.getMonth()+1, now.getFullYear());
            
            selected = val || today;
            viewY = selected.year;
            viewM = selected.month;

            const rect = el.getBoundingClientRect();
            popup.style.top = (rect.bottom + window.scrollY + 5) + 'px';
            popup.style.left = (rect.left + window.scrollX) + 'px';
            popup.style.display = 'block';
            render();
        });
    });

    // Nav Clickers
    popup.querySelector('#pM').onclick = () => { viewM--; if(viewM<1){ viewM=13; viewY--; } render(); };
    popup.querySelector('#nM').onclick = () => { viewM++; if(viewM>13){ viewM=1; viewY++; } render(); };
    popup.querySelector('#pY').onclick = () => { viewY--; render(); };
    popup.querySelector('#nY').onclick = () => { viewY++; render(); };
    popup.querySelector('#btnT').onclick = () => {
        const n = new Date();
        const t = gregorianToEthiopianParsed(n.getDate(), n.getMonth()+1, n.getFullYear());
        viewY = t.year; viewM = t.month; render();
    };
    popup.querySelector('#btnC').onclick = () => popup.style.display = 'none';

    // Year Dropdown
    popup.querySelector('#lblY').onclick = () => {
        const list = popup.querySelector('#listY');
        const now = new Date();
    const todayEth = gregorianToEthiopianParsed(now.getDate(), now.getMonth() + 1, now.getFullYear());
    const currentEthYear = todayEth.year;
        list.style.display = 'block';
        list.innerHTML = '';
        for(let i=viewY-80; i<=currentEthYear; i++) {
            const d = document.createElement('div'); d.textContent = i;
            d.onclick = () => { viewY=i; list.style.display='none'; render(); };
            list.appendChild(d);
        }
    };
})();