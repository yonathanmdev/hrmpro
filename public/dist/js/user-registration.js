document.addEventListener("DOMContentLoaded", function () {

    const roleSelector = document.getElementById("roleSelector");
    const orgSelector = document.getElementById("orgSelector");
    const orgHidden = document.getElementById("orgHidden");

    // 👇 get from HTML (not PHP directly)
    const sessionOrgId = orgSelector.dataset.sessionOrg;

    function handleRoleChange() {
        if (roleSelector.value === "org_admin") {

            orgSelector.value = sessionOrgId;
            orgSelector.setAttribute("disabled", "disabled");

            if (orgHidden) {
                orgHidden.value = sessionOrgId;
            }

        } else {
            orgSelector.removeAttribute("disabled");

            if (orgHidden) {
                orgHidden.value = "";
            }
        }
    }

    // run on load
    handleRoleChange();

    // listen change
    roleSelector.addEventListener("change", handleRoleChange);
});
