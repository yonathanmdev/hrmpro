<div class="modal fade" id="editUserModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editUserForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/edit-user-process" method="POST">
        <div class="modal-header bg-info">
          <h4 class="modal-title">ተቆጣጣሪ ማስተካከያ</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_user_id" name="id">
          <div class="form-group">
            <label>ስም</label>
        <input type="text" class="form-control" placeholder="ስም ያስገቡ" name="edit_firstname" id="edit_firstname" required>
        </div>
           <div class="form-group">
            <label>የአባት ስም</label>
        <input type="text" class="form-control" placeholder="የአባት ስም ያስገቡ" name="edit_fathername" id="edit_fathername" required>
     </div>
           <div class="form-group">
            <label>የአያት ስም</label>
        <input type="text" class="form-control" placeholder="የአያት ስም ያስገቡ" name="edit_grandfathername" id="edit_grandfathername" required>
   </div>
           <div class="form-group">
            <label>ስልክ ቁጥር</label>
        <input type="text" class="form-control" placeholder="ስልክ ቁጥር ያስገቡ" name="edit_phone" id="edit_phone"  required>
          </div>

   <div class="form-group">
        <label>ኢሜይል</label>
        <input type="email" class="form-control" placeholder="ኢሜይል ያስገቡ" name="edit_email"  id="edit_email">
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
