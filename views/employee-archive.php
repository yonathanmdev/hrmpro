<?php
use App\Helpers\EthiopianDateHelper; 
 $is_employee_registration_page = true; ?>
<section class="content">
  <div class="container-fluid">
    
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">የሰራተኛ ማህደር</h3>
      </div>

      <div class="card-body">
        <table id="example1" data-empty-msg="ምንም የተያያዘ ፋይል አልተገኘም።" class="table table-bordered table-striped">
  <thead class="thead-light">
    <tr>
      <th>#</th>
      <th>የፋይል አይነት</th>
      <th>የተያያዘ ፋይል</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $counter = 1; 
    
    // 1. Manually check and display the Primary Registration File (201)
    if (!empty($employee['employee_file201'])): ?>
      <tr>
        <td><?= $counter++ ?></td>
        <td>ሲመዘገቡ የተያያዘ ፋይል</td>
        <td>
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['employee_file201']) ?>&type=document" target="_blank" class="btn btn-xs btn-outline-primary">
            <i class="fas fa-file-pdf"></i> ክፈት
          </a>
        </td>
      </tr>
    <?php endif; ?>
    <?php if (!empty($documentData)): ?>
      <?php foreach ($documentData as $document): ?>
        <tr>
          <td><?= $counter++ ?></td>
         <?php
$type = $document['entity_type'];

if ($type === 'REMOVAL') {
    $type = 'ዋስትና የተነሳበት';
}
?>
<td><?= htmlspecialchars($type) ?></td>
          
          <td>
            <?php if (!empty($document['file_url'])): ?>
              <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($document['file_url']) ?>&type=document" target="_blank" class="btn btn-xs btn-outline-info">
                <i class="fas fa-file-pdf"></i> ክፈት
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php if ($counter === 1): ?>
      
    <?php endif; ?>
  </tbody>
</table>
      </div>
    </div>
  </div>
</section>
