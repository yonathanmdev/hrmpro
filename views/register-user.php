   <?php $is_register_user_page = true; ?>
<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Card -->
    <div class="card card-default">
      <div class="card-header">

        <h3 class="card-title">ተቆጣጣሪ</h3>

        <div class="card-tools">
           <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#userModal">
            አዲስ ተቆጣጣሪ መዝግብ
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
        <th>የተጠቃሚ ስም </th>
          <th>ኢሚል</th>
          <th>ቅርንጫፍ</th>
        <th>Role</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($users)): ?>
        <?php foreach ($users as $index => $row): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($row['first_name'].' '.$row['father_name'].' '.$row['grand_father_name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['branch_name']) ?></td>
            <td>
<?php
$role = $row['role'];

$roleMap = [
    'system_admin' => 'System Admin',
    'org_admin' => 'Admin',
    'hr_director' => 'ዳይሬክተር',
];

echo htmlspecialchars($roleMap[$role] ?? 'ባለሙያ');
?>
</td>
            <td>
               <button class="btn btn-primary btn-sm edit-org" 
                      data-id="<?= $row['id'] ?>"  title="አስተካክል"  >
                <i class="fas fa-edit"></i>
              </button> 
              <button class="btn btn-danger btn-sm delete-org" 
                      data-id="<?= $row['id'] ?>"  title="ሰርዝ">

                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="3" class="text-center">ምንም የተመዘገበ ተጠቃሚ የለም።</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
      </div>

    </div>
    <!-- /.card -->

  </div>
</section>
<?php include 'partials/edit-user-modal.php'; ?>

<!-- Modal (place OUTSIDE card) -->
<div class="modal fade" id="userModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

     <form id="userForm" action="/HRM/register-process" method="POST">

        <!-- Header -->
        <div class="modal-header bg-secondary">
          <h4 class="modal-title">አዲስ ተጠቃሚ መዝግብ</h4>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <!-- Body -->
        <div class="modal-body">
  
  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label>ስም</label>
        <input type="text" class="form-control" placeholder="ስም ያስገቡ" name="firstname" required>
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label>የአባት ስም</label>
        <input type="text" class="form-control" placeholder="የአባት ስም ያስገቡ" name="fathername" required>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label>የአያት ስም</label>
        <input type="text" class="form-control" placeholder="የአያት ስም ያስገቡ" name="grandfathername" required>
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label>ስልክ ቁጥር</label>
        <input type="text" class="form-control" placeholder="ስልክ ቁጥር ያስገቡ" name="phone" required>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label>Role</label>
        <?php 
        // ቼክ ለማድረግ እንዲመቸን ቫሪያብል ላይ እናስቀምጠው
        $isSystemAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'system_admin');
    ?>

    <select class="form-control" name="role" id="roleSelector" required <?php echo $isSystemAdmin ? 'disabled' : ''; ?>>
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
        <input type="hidden" name="role" value="org_admin">
    <?php endif; ?>

      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label>የተቁሙ ስም</label>
      <?php $sessionOrgId = $_SESSION['user']['branch_id'] ?? ''; 
         $branchNameString = htmlspecialchars($branchName['name'] ?? $branchName->name ?? '');
      
      ?>

<select class="form-control" name="organization_id" id="orgSelector"  data-session-org="<?= htmlspecialchars($sessionOrgId) ?>" required>
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
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
         <label>ኢሜይል</label>
        <input type="email" class="form-control" placeholder="ኢሜይል ያስገቡ" name="email">
      </div>
    </div>
    <div class="col-md-6">
  <div class="form-group">
    <label>Password</label>
    <input type="password" class="form-control" placeholder="Password ያስገቡ" name="password" required>
  </div>
  </div>
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
<script>
document.addEventListener("DOMContentLoaded", function () {

    const roleSelector = document.getElementById("roleSelector");
    const orgSelector = document.getElementById("orgSelector");
    const sessionOrgId = orgSelector.dataset.sessionOrg;

    const branchName = <?= json_encode($branchNameString) ?>; // ONLY the name as string

    function handleRoleChange() {
        if (roleSelector.value !== "org_admin") {
            orgSelector.innerHTML = `<option value="${sessionOrgId}" selected>${branchName}</option>`;
            orgSelector.disabled = true;
        } else {
            orgSelector.disabled = false;
            orgSelector.innerHTML = `<option value="">-- ተቁሙን ይምረጡ --</option>
                <?php foreach ($organizations as $row): ?>
                    <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endforeach; ?>`;
        }
    }

    handleRoleChange();
    roleSelector.addEventListener("change", handleRoleChange);
});
</script>