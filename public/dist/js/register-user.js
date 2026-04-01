document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelector');
    const orgSelect = document.getElementById('orgSelector');
    const hiddenContainer = document.getElementById('hiddenInputs');

    // ከ Session የመጣው የባለቤቱ ድርጅት ID
    const userOrgId = orgSelect.getAttribute('data-user-org');

    function updateOrgStatus() {
        const selectedRole = roleSelect.value;
        const isSystemAdmin = <?= json_encode($isSystemAdmin) ?>;

        if (isSystemAdmin) {
            // System Admin ከሆነ ሁልጊዜ ክፍት ነው (ወይም ቀድሞ የሰራኸው logic ይተገበራል)
            return;
        }

        if (selectedRole === 'org_admin') {
            // Admin ከሆነ ድርጅት እንዲመርጥ ፍቀድለት
            orgSelect.disabled = false;
            hiddenContainer.innerHTML = '';
        } else if (selectedRole !== "") {
            // ሌላ Role ከሆነ የራሱ ድርጅት ላይ ቆልፈው
            orgSelect.value = userOrgId;
            orgSelect.disabled = true;
            
            // Disabled ስለሆነ ዳታው እንዲላክ hidden input ፍጠር
            hiddenContainer.innerHTML = `<input type="hidden" name="organization_id" value="${userOrgId}">`;
            
            // ምርጫው ተቀይሮ ከሆነ ወደ ሰርቨር ፌች ማድረግ ካስፈለገ እዚህ ይደረጋል
            // fetchOrgDetails(userOrgId); 
        }
    }

    roleSelect.addEventListener('change', updateOrgStatus);
    
    // ገጹ ሲከፈት መጀመሪያ ቼክ ለማድረግ
    updateOrgStatus();
});