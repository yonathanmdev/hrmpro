document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = window.location.origin + '/HRM';

    // 1. ሞዳሉን መረጃ ሞልቶ መክፈት (ከዚህ በፊት የሰራነው)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.edit-org');
        if (btn) {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');

            document.getElementById('edit_org_id').value = id;
            document.getElementById('edit_org_name').value = name;

            $('#editOrgModal').modal('show');
        }
    });

    // 2. የተስተካከለውን መረጃ መላክ (የቀረው ክፍል)
    const editForm = document.getElementById('editOrgForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault(); // ገጹ እንዳይደሰስ (Refresh እንዳይሆን) ይከለክላል

            // በፎርሙ ውስጥ ያሉትን መረጃዎች መሰብሰብ
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');

            // ቁልፉን ለጊዜው ማሰናከል (Double click ለመከላከል)
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> በማደስ ላይ...';

            // መረጃውን ወደ PHP መላክ
            fetch(BASE_URL + '/?action=update-organization-process', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin', // <-- ADD THIS to send session cookies
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    if (response.status === 403) {
                        // Authentication/Authorization error
                        return response.text().then(text => {
                            try {
                                const data = JSON.parse(text);
                                if (data.message === 'access_denied') {
                                    throw new Error('ACCESS_DENIED');
                                }
                            } catch (e) {
                                // Not JSON
                            }
                            throw new Error('FORBIDDEN');
                        });
                    }
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (err) {
                        // PHP ስህተት ካለው እዚህ ጋር በዝርዝር ያሳየናል
                        throw new Error("ሰርቨሩ የላከው መረጃ ትክክል አይደለም (HTML ሊሆን ይችላል)፦ " + text);
                    }
                });
            })
            .then(data => {
                if (data.status === 'success') {
                    $('#editOrgModal').modal('hide');

                    // SweetAlert2 ካለዎት ይጠቀሙ፡ ከሌለ በ ተራ alert መቀየር ይችላሉ
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'ተሳክቷል!',
                            text: data.message,
                            timer: 2000
                        }).then(() => {
                            location.reload(); // ገጹን አድሶ አዲሱን ስም እንዲያሳይ
                        });
                    } else {
                        alert(data.message || 'ተሳክቷል!');
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('ስህተት', data.message, 'error');
                    } else {
                        alert(data.message || 'ስህተት ተፈጥሯል።');
                    }
                }
            })
            .catch(error => {
                console.error('Error Details:', error);

                if (error.message === 'ACCESS_DENIED' || error.message === 'FORBIDDEN') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'መግባት አልተሳካም',
                            text: 'መግባት አልተሳካም ወይም ፈቃድ የለዎትም። እባክዎ እንደገና ይግቡ።',
                            confirmButtonText: 'ወደ መግቢያ ገጽ ሂድ'
                        }).then(() => {
                            window.location.href = BASE_URL + '/login';
                        });
                    } else {
                        alert('መግባት አልተሳካም። እባክዎ እንደገና ይግቡ።');
                        window.location.href = BASE_URL + '/login';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('ስህተት', 'መረጃውን ማደስ አልተቻለም። እባክዎ ኮንሶሉን (F12) ይፈትሹ።', 'error');
                    } else {
                        alert('መረጃውን ማደስ አልተቻለም። እባክዎ ኮንሶሉን (F12) ይፈትሹ።');
                    }
                }
            })
            .finally(() => {
                // ቁልፉን መልሶ ማግበር
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'አድስ (Update)';
            });
        });
    }
});