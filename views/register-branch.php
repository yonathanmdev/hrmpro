<?php $is_branch_page = true; ?>
<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Card -->
    <div class="card card-default">
      <div class="card-header">

        <h3 class="card-title">ቅርንጫፍ</h3>

        <div class="card-tools">
           <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#branchModal">
            <i class="fas fa-plus mr-1"></i>ቅርንጫፍ መዝግብ
          </button>
          
        </div>

      </div>

      <div class="card-body">
        <!-- Example Table (optional) -->
      <table id="example1" data-empty-msg="ምንም ቅርንጫፍ የለም።" class="table table-bordered table-hover dataTable dtr-inline small" style="color: #000;" aria-describedby="example2_info">
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th>የቅርንጫፍ ስም </th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($organizations)): ?>
        <?php foreach ($organizations as $index => $row): ?>
          <tr id="row-<?= htmlspecialchars($row['id']) ?>">
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>
               <button class="btn btn-primary btn-sm edit-branch" 
                      data-id="<?= $row['id'] ?>" 
                      data-name="<?= htmlspecialchars($row['name']) ?>" title="አስተካክል"  >
                <i class="fas fa-edit"></i>
              </button> 
              <button class="btn btn-danger btn-sm delete-branch" 
                      data-id="<?= $row['id'] ?>" 
                      data-name="<?= htmlspecialchars($row['name']) ?>" title="ሰርዝ">

                <i class="fas fa-trash-alt me-1"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
      </div>

    </div>
    <!-- /.card -->

  </div>
</section>
<?php include 'partials/edit-branch-modal.php'; ?>

<!-- Modal (place OUTSIDE card) -->
<div class="modal fade" id="branchModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <form id="orgForm" method="POST" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/register-branch-process">

        <div class="modal-header">
        <h6 class="modal-title font-weight-bold">
          <i class="fas fa-plus mr-1"></i> አዲስ ቅርንጫፍ መዝግብ
        </h6>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

        <!-- Body -->
        <div class="modal-body">
          <div class="form-group mb-2">
            <label for="org_name" class="mb-1"><small class="font-weight-bold">የቅርንጫፍ ስም</small></label>
            <input 
              type="text" 
              id="branch_name" 
              class="form-control form-control-sm" 
              name="branch_name" 
              placeholder="ስም ያስገቡ" 
              required
            >
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
            ዝጋ
          </button>
          <button type="submit" class="btn btn-primary btn-sm">
            መዝግብ
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

