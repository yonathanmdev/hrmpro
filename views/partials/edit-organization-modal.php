<div class="modal fade" id="editOrgModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editOrgForm">
        <div class="modal-header bg-info">
          <h4 class="modal-title">ተቋም ማስተካከያ</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_org_id" name="id">
          <div class="form-group">
            <label>የተቋሙ ስም</label>
            <input type="text" id="edit_org_name" name="org_name" class="form-control" required>
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