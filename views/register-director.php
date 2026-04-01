<?php $is_organization_page = true; ?>
<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Card -->
    <div class="card card-default">
      <div class="card-header">

        <h3 class="card-title">ዳይሬክተር</h3>

        <div class="card-tools">
           <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#orgModal">
            አዲስ ዳይሬክተር መዝግብ
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
        <th>ዳይሬክተር ስም </th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($directors)): ?>
        <?php foreach ($directors as $index => $row): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($row['director_name']) ?></td>
    
            <td>
               <button class="btn btn-primary btn-sm edit-org" 
                      data-id="<?= $row['id'] ?>" 
                      data-name="<?= htmlspecialchars($row['director_name']) ?>" title="አስተካክል"  >
                <i class="fas fa-edit"></i>
              </button> 
              <button class="btn btn-danger btn-sm delete-org" 
                      data-id="<?= $row['id'] ?>" 
                      data-name="<?= htmlspecialchars($row['director_name']) ?>" title="ሰርዝ">

                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="3" class="text-center">ምንም የተመዘገበ ዳይሬክተር የለም።</td>
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

      <form id="orgForm" method="POST" action="/HRM/register-director-process">

        <!-- Header -->
        <div class="modal-header bg-secondary">
          <h4 class="modal-title">አዲስ ዳይሬክተር መዝግብ</h4>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <div class="form-group">
            <label for="org_name">የዳይሬክተር ስም</label>
            <input 
              type="text" 
              id="director_name" 
              class="form-control" 
              name="director_name" 
              placeholder="ስም ያስገቡ" 
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

