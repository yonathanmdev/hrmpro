<?php $is_registration_page = true; ?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">ሰራተኛ ዝርዝር</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userModal">
            <i class="fas fa-user-plus"></i> አዲስ ሰራተኛ መዝግብ
          </button>
        </div>
      </div>
      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>ሙሉ ስም</th>
              <th>ኢሜይል</th>
              <th>ተቋም / ቅርንጫፍ</th>
              <th>ሃላፊነት (Role)</th>
              <th>ሁኔታ</th>
              <th>ተግባር</th>
            </tr>
          </thead>
          
        </table>
      </div>
    </div>
  </div>
</section>

<!-- User Registration Modal -->
<div class="modal fade" id="userModal">
  <div class="modal-dialog