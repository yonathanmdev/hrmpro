document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Listen for clicks on the Edit buttons (Event Delegation)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-org')) {
            const btn = e.target.closest('.edit-org');
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');

            // Fill the hidden ID and name input
            document.getElementById('edit_org_id').value = id;
            document.getElementById('edit_org_name').value = name;

            // Show the Bootstrap modal using native JS
            // Note: AdminLTE uses Bootstrap 4, which still requires the jQuery .modal() 
            // trigger unless you use a pure Vanilla Bootstrap 5 setup.
            $('#editOrgModal').modal('show'); 
        }
    });

   
});