$(document).ready(function () {

    $(document).on("click", ".edit-org", function () {

        const id = $(this).data("id");

        console.log("User ID:", id);

       fetch(`${BASE_URL}/edit-user?id=${id}`)
            .then(res => res.json())
            .then(data => {

                if (data.status === 'success') {

                    const user = data.data;

                    // Fill form fields
                    $("#edit_user_id").val(user.id);
                    $("#edit_firstname").val(user.first_name);
                    $("#edit_fathername").val(user.father_name);
                    $("#edit_grandfathername").val(user.grand_father_name);
                    $("#edit_phone").val(user.phone);
                    $("#edit_email").val(user.email);
                    // Show modal
                    $("#editUserModal").modal("show");

                } else {
                    alert(data.message || "Failed to load user");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error fetching data");
            });

    });

});