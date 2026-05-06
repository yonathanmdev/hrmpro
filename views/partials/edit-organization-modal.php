<div class="modal fade" id="editOrgModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editOrgForm">
        <!-- 1. Modal Header -->
        <div class="modal-header">
          <h6 class="modal-title font-weight-bold">
            <i class="fas fa-edit mr-1"></i> ተቋም ማስተካከያ
          </h6>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_org_id" name="id">
          <div class="form-group  mb-2">
            <label for="edit_org_name" class="mb-1"><small class="font-weight-bold">የተቋሙ ስም</small></label>
            <input type="text" id="edit_org_name" name="org_name" class="form-control form-control-sm" required>
          </div>
           <div class="form-group mb-2">
            <label for="edit_org_description" class="mb-1"><small class="font-weight-bold">የተቋሙ ዓይነት</small></label>
            <input 
              type="text" 
              id="edit_org_description" 
              class="form-control form-control-sm" 
              name="edit_org_description" 
              placeholder="ዓይነት ያስገቡ" 
              required
            >
          </div>
        </div>
       <div class="modal-footer justify-content-between">
             <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
            ዝጋ
          </button>
          <button type="submit" class="btn btn-warning btn-sm">አስተካክል</button>
        </div>
      </form>
    </div>
  </div>
</div>