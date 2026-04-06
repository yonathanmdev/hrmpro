document.addEventListener("DOMContentLoaded", function () {

    const roleSelector = document.getElementById("roleSelector");
    const orgSelector  = document.getElementById("orgSelector");
    const sessionOrgId = orgSelector.dataset.sessionOrg;

    function handleRoleChange() {
        if (roleSelector.value !== "org_admin") {
            orgSelector.innerHTML = `<option value="${sessionOrgId}" selected>${BRANCH_NAME}</option>`;
            orgSelector.disabled = true;
        } else {
            orgSelector.disabled = false;

            let options = `<option value="">-- ተቁሙን ይምረጡ --</option>`;
            ORGANIZATIONS.forEach(function(org) {
                options += `<option value="${org.id}">${org.name}</option>`;
            });
            orgSelector.innerHTML = options;
        }
    }

    handleRoleChange();
    roleSelector.addEventListener("change", handleRoleChange);
});