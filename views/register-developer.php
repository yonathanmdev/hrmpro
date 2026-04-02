<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">የባለሙያዎች ዝርዝር (Developer List)</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#devModal">
            <i class="fas fa-plus"></i> አዲስ ባለሙያ መዝግብ
          </button>
        </div>
      </div>
      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>ሙሉ ስም</th>
              <th>ሃላፊነት</th>
              <th>ኢሜይል/ስልክ</th>
              <th>የድጋፍ ሰዓት</th>
              <th>ክፍያ</th>
              <th>እርምጃ</th>
            </tr>
          </thead>
     <tbody>
              <?php foreach ($devdata as $index => $developer): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($developer['full_name']) ?></td>
                  <td><?= htmlspecialchars($developer['position']) ?></td>
                  <td>
                    <?= htmlspecialchars($developer['email']) ?><br>
                    <?= htmlspecialchars($developer['phone']) ?>
                  </td>
                  <td><?= htmlspecialchars($developer['active_time']) ?></td>
                  <td><?= number_format($developer['payment'], 2) ?></td>
                  <td>
                    <button class="btn btn-sm btn-info">Edit</button>
                    <button class="btn btn-sm btn-danger">Delete</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="devModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title">አዲስ ባለሙያ መረጃ መመዝገቢያ</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="/HRM/register-dev-process" method="POST">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>ሙሉ ስም</label>
                <input type="text" class="form-control" name="full_name" required>
              </div>
              <div class="form-group">
                <label>ሃላፊነት / Position</label>
                <input type="text" class="form-control" name="position" required>
              </div>
              <div class="form-group">
                <label>ኢሜይል</label>
                <input type="email" class="form-control" name="email">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>ስልክ ቁጥር</label>
                <input type="text" class="form-control" name="phone">
              </div>
              <div class="form-group">
                <label>የድጋፍ ሰዓት (Active Time)</label>
                <input type="text" class="form-control" placeholder="ለምሳሌ፡ 2:00 LT - 11:00 LT" name="active_time">
              </div>
              <div class="form-group">
                <label>ክፍያ (Payment)</label>
                <input type="number" step="0.01" class="form-control" name="payment">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">ዝጋ</button>
          <button type="submit" class="btn btn-primary">መረጃውን መዝግብ</button>
        </div>
      </form>
    </div>
  </div>
</div>