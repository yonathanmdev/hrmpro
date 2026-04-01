// edit-org.js
$(document).ready(function () {
    $(document).on("click", ".edit-org", function () {
        const id = $(this).data("id");

        // Fill the hidden input for ID
        $("#edit_user_id").val(id);

        // Open the modal
        $("#editUserModal").modal("show");
    });
});