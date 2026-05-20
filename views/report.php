<?php $is_branch_page = true; ?>
<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Card -->
    <div class="card card-default">
      <div class="card-header">

        <h3 class="card-title">ሪፖርት</h3>

      </div>

      <div class="card-body">
        <!-- Example Table (optional) -->
      <table id="example1" data-empty-msg="በመሰራት ላይ።" class="table table-bordered table-hover dataTable dtr-inline small" style="color: #000;" aria-describedby="example2_info">
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th>Report Change</th>
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
