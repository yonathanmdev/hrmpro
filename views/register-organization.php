<?php $is_organization_page = true; ?>
<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Card -->
    <div class="card card-default">
      <div class="card-header">

        <h3 class="card-title">ተቋም</h3>

        <div class="card-tools">
           <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#orgModal">
            አዲስ ተቋም መዝግብ
          </button>
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
          <button type="button" class="btn btn-tool" data-card-widget="remove">
            <i class="fas fa-times"></i>
          </button>
         
        </div>

      </div>

      <div class="card-body">
        <!-- Example Table (optional) -->
      <table id="example1" class="table table-bordered table-hover dataTable dtr-inline" aria-describedby="example2_info">
    <thead>
      <tr>
        <th>#</th>
        <th>የተቋሙ ስም </th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($organizations)): ?>
        <?php foreach ($organizations as $index => $row): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
               <button class="btn btn-primary btn-sm edit-org" 
                      data-id="<?= $row['id'] ?>" 
                      data-name="<?= htmlspecialchars($row['name']) ?>" title="አስተካክል"  >
                <i class="fas fa-edit"></i>
              </button> 
              <button class="btn btn-danger btn-sm delete-org" 
                      data-id="<?= $row['id'] ?>" 
                      data-name="<?= htmlspecialchars($row['name']) ?>" title="ሰርዝ">

                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="3" class="text-center">ምንም የተመዘገበ ተቋም የለም።</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
      </div>

    </div>
    <!-- /.card -->

  </div>
</section>
<?php include 'partials/edit-organization-modal.php'; ?>

<!-- Modal (place OUTSIDE card) -->
<div class="modal fade" id="orgModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <form id="orgForm" method="POST" action="/HRM/register-organization-process">

        <!-- Header -->
        <div class="modal-header bg-secondary">
          <h4 class="modal-title">አዲስ ተቋም መዝግብ</h4>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <div class="form-group">
            <label for="org_name">የተቋሙ ስም</label>
            <input 
              type="text" 
              id="org_name" 
              class="form-control" 
              name="org_name" 
              placeholder="ስም ያስገቡ" 
              required
            >
          </div>
          <div class="form-group">
            <label for="org_description">የተቋሙ ዓይነት</label>
            <input 
              type="text" 
              id="org_description" 
              class="form-control" 
              name="org_description" 
              placeholder="ዓይነት ያስገቡ" 
              required
            >
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            ዝጋ
          </button>
          <button type="submit" class="btn btn-primary">
            መዝግብ
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

