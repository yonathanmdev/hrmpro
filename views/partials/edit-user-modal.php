<div class="modal fade" id="editUserModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editUserForm">
        <div class="modal-header bg-info">
          <h4 class="modal-title">ተቆጣጣሪ ማስተካከያ</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_user_id" name="id">
          <div class="form-group">
            <label>ስም</label>
        <input type="text" class="form-control" placeholder="ስም ያስገቡ" name="edit_firstname" required>
        </div>
           <div class="form-group">
            <label>የአባት ስም</label>
        <input type="text" class="form-control" placeholder="የአባት ስም ያስገቡ" name="edit_fathername" required>
     </div>
           <div class="form-group">
            <label>የአያት ስም</label>
        <input type="text" class="form-control" placeholder="የአያት ስም ያስገቡ" name="edit_grandfathername" required>
   </div>
           <div class="form-group">
            <label>ስልክ ቁጥር</label>
        <input type="text" class="form-control" placeholder="ስልክ ቁጥር ያስገቡ" name="edit_phone" required>
          </div>
        <div class="form-group">
            <label>Role</label>
        <?php 
        // ቼክ ለማድረግ እንዲመቸን ቫሪያብል ላይ እናስቀምጠው
        $isSystemAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'system_admin');
    ?>

    <select class="form-control" name="edit_role" id="editRoleSelector" required <?php echo $isSystemAdmin ? 'disabled' : ''; ?>>
        <?php if ($isSystemAdmin): ?>
            <option value="org_admin" selected>Admin</option>
        <?php else: ?>
            <option value="">-- ምረጥ --</option>
             <option value="org_admin">Admin</option>
            <option value="hr_director">ዳይሬክትር</option>
            <option value="hr_officer">ባለሙያ</option>
        <?php endif; ?>
    </select>

    <?php if ($isSystemAdmin): ?>
        <input type="hidden" name="edit_role" value="org_admin">
    <?php endif; ?>
 </div>
<div class="form-group">
         <label>የተቁሙ ስም</label>
      <?php $sessionOrgId = $_SESSION['user']['branch_id'] ?? ''; 
         $branchNameString = htmlspecialchars($branchName['name'] ?? $branchName->name ?? '');
      
      ?>

<select class="form-control" name="edit_organization_id" id="editOrgSelector"  data-session-org="<?= htmlspecialchars($sessionOrgId) ?>" required>
    <option value="">-- ተቁሙን ይምረጡ --</option>

    <?php if (!empty($organizations)): ?>
        <?php foreach ($organizations as $row): ?>
            <option value="<?= htmlspecialchars($row['id']) ?>">
                <?= htmlspecialchars($row['name']) ?>
            </option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
   </div>
   <div class="form-group">
        <label>ኢሜይል</label>
        <input type="email" class="form-control" placeholder="ኢሜይል ያስገቡ" name="edit_email">
   </div>
   <div class="form-group">
       <label>Password</label>
    <input type="password" class="form-control" placeholder="Password ያስገቡ" name="edit_password" required>
   </div>
        </div>
       <div class="modal-footer justify-content-between">
             <button type="button" class="btn btn-default" data-dismiss="modal">
            ዝጋ
          </button>
          <button type="submit" class="btn btn-info">አስተካክል</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const  editRoleSelector = document.getElementById("editRoleSelector");
    const editOrgSelector = document.getElementById("editOrgSelector");
    const sessionOrgId = editOrgSelector.dataset.sessionOrg;

    const  editbranchName = <?= json_encode($branchNameString) ?>; // ONLY the name as string

    function edithandleRoleChange() {
        if (editRoleSelector.value !== "org_admin") {
            editOrgSelector.innerHTML = `<option value="${sessionOrgId}" selected>${editbranchName}</option>`;
            editOrgSelector.disabled = true;
        } else {
            editOrgSelector.disabled = false;
            editOrgSelector.innerHTML = `<option value="">-- ተቁሙን ይምረጡ --</option>
                <?php foreach ($organizations as $row): ?>
                    <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endforeach; ?>`;
        }
    }

    edithandleRoleChange();
    editRoleSelector.addEventListener("change", edithandleRoleChange);
});
</script>