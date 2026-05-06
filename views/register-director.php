<?php $is_director_page = true; ?>
<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Card -->
    <div class="card card-primary card-outline">
      <div class="card-header bg-white d-flex flex-column flex-md-row align-items-md-center">

  <div class="ml-md-auto">
    <button 
      type="button" 
      class="btn btn-primary btn-sm w-100 w-md-auto"
      data-toggle="modal" 
      data-target="#orgModal"
    >
      <i class="fas fa-plus mr-1"></i>
      ዳይሬክተር መዝግብ
    </button>
  </div>

</div>

      <div class="card-body">
        <!-- Header -->
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0 text-dark">
      የዳይሬክተር ዝርዝር
    </h6>
  </div>
      <table id="example1" data-empty-msg="ምንም ዳይሬክተር የለም።" class="table table-bordered table-hover dataTable dtr-inline small" style="color: #000;" aria-describedby="example2_info">
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th>ዳይሬክተር ስም </th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($directors)): ?>
        <?php foreach ($directors as $index => $row): ?>
          <tr id="row-<?= $row['id'] ?>">
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($row['director_name']) ?></td>
    
            <td class="text-center align-middle">
  <div class="btn-group btn-group-sm shadow-sm" role="group">

    <button 
      class="btn btn-outline-secondary edit-director" 
      data-id="<?= $row['id'] ?>" 
      data-name="<?= htmlspecialchars($row['director_name']) ?>" 
      title="አስተካክል"
    >
      <i class="fas fa-edit"></i>
    </button>

    <button 
      class="btn btn-outline-danger shadow-sm delete-director" 
      data-id="<?= $row['id'] ?>" 
      data-name="<?= htmlspecialchars($row['director_name']) ?>" 
      data-toggle="tooltip" 
        data-placement="top"
      title="ሰርዝ"

    >
    <i class="fas fa-trash-alt me-1"></i> ሰርዝ
    </button>

  </div>
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
<?php include 'partials/edit-director-modal.php'; ?>

<!-- Modal (place OUTSIDE card) -->
<div class="modal fade" id="orgModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <form id="orgForm" method="POST" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/register-director-process">

        <!-- Header -->
        <div class="modal-header">
        <h6 class="modal-title font-weight-bold">
          <i class="fas fa-plus mr-1"></i> አዲስ ዳይሬክተር መዝግብ
        </h6>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

        <!-- Body -->
        <div class="modal-body">
          <div class="form-group mb-2">
            <label for="director_name" class="mb-1"><small class="font-weight-bold">የዳይሬክተር ስም</small></label>
            <input 
              type="text" 
              id="director_name" 
              class="form-control form-control-sm" 
              name="director_name" 
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
          <button type="submit" class="btn btn-primary btn-sm">መዝግብ</button>
          
        </div>

      </form>

    </div>
  </div>
</div>

