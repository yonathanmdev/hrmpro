$(document).ready(function() {
      let searchTimeout = null;

    $(document).on('input', '#emp_search_input', function() {
        const q = $(this).val().trim();
        const $list = $('#emp_search_suggestions');
        const $error = $('#search_error_msg');

        clearTimeout(searchTimeout);

        if (q.length < 2) {
            $list.hide().empty();
            return;
        }

        searchTimeout = setTimeout(() => {
            // ተመሳሳዩን የ Fetch አማራጮች (Options) እንጠቀም
            fetch(`${BASE_URL}/employee-search-api?query=${encodeURIComponent(q)}`, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(employees => {
                $list.empty().show();

                if (employees.length > 0) {
                    employees.forEach(emp => {
                        // ስሙን በ loop ውስጥ ነው መገንባት ያለብን
                        const full_name = `${emp.first_name} ${emp.father_name} ${emp.g_father_name}`;
                    
                        const $btn = $(`
                            <button type="button" class="list-group-item list-group-item-action py-2 d-flex justify-content-between align-items-center">
                                <span>${full_name}</span>
                                <span class="badge badge-light border text-muted">${emp.employee_id || ''}</span>
                            </button>
                        `);

                        $btn.on('click', function() {
                            $(this).addClass('active');
                            window.location.href = `${BASE_URL}/employee-experience/${emp.uuid}`;
                        });

                        $list.append($btn);
                    });
                } else {
                    $list.append('<li class="list-group-item small text-danger text-center">ምንም ሰራተኛ አልተገኘም</li>');
                }
            })
            .catch(err => {
                console.error("Search Error:", err);
                $error.text('መረጃ በመፈለግ ላይ ስህተት ተፈጥሯል።').show();
            });
        }, 300);
    });

    $('#employeeSearchModal').on('hidden.bs.modal', function () {
        $('#emp_search_input').val('');
        $('#emp_search_suggestions').hide().empty();
    });
});