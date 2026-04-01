document.addEventListener('DOMContentLoaded', function() {
    
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
            fetch('update-organization-process', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // እዚህ ጋር ነው '<' ስህተት የሚፈጠረው፤ ስለዚህ ወደ ጽሁፍ ቀይረን እንፈትሽ
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
                    Swal.fire({
                        icon: 'success',
                        title: 'ተሳክቷል!',
                        text: data.message,
                        timer: 2000
                    }).then(() => {
                        location.reload(); // ገጹን አድሶ አዲሱን ስም እንዲያሳይ
                    });
                } else {
                    Swal.fire('ስህተት', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error Details:', error);
                Swal.fire('ስህተት', 'መረጃውን ማደስ አልተቻለም። እባክዎ ኮንሶሉን (F12) ይፈትሹ።', 'error');
            })
            .finally(() => {
                // ቁልፉን መልሶ ማግበር
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'አድስ (Update)';
            });
        });
    }
});