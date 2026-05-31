<?php
use App\Helpers\EthiopianDateHelper; 
$is_employee_archive_page = true;

// Extract UUID from URL
$url_parts = explode('/', $_SERVER['REQUEST_URI']);
$uuid = end($url_parts);
?>
<section class="content">
  <div class="container-fluid">
    <div class="card-header bg-white d-flex flex-column flex-md-row align-items-md-center card-primary card-outline">
      <div class="ml-md-auto">
        <button
          type="button"
          class="btn btn-primary btn-sm w-100 w-md-auto"
          data-toggle="modal"
          data-target="#fileAttachmentModal"
        >
          <i class="fas fa-user-plus mr-2"></i>
          ፋይል መዝግብ
        </button>
      </div>
    </div>

    <div class="card-body">
      <table id="example1" data-empty-msg="ምንም የተያያዘ ፋይል አልተገኘም።" class="table table-bordered table-striped">
        <thead class="thead-light">
          <tr>
            <th>#</th>
            <th>የፋይል አይነት</th>
            <th>የተያያዘ ፋይል</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $counter = 1; ?>

          <?php if (!empty($employee['employee_file201'])): ?>
            <tr>
              <td><?= $counter++ ?></td>
              <td>ሲመዘገቡ የተያያዘ ፋይል</td>
              <td>
                <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['employee_file201']) ?>&type=document"
                   target="_blank" class="btn btn-xs btn-outline-primary">
                  <i class="fas fa-file-pdf"></i> ክፈት
                </a>
              </td>
              <td>—</td>
            </tr>
          <?php endif; ?>

          <?php if (!empty($employeeGuarantor['guarantor_letter'])): ?>
            <tr>
              <td><?= $counter++ ?></td>
              <td>የተያዥ ፋይል</td>
              <td>
                <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employeeGuarantor['guarantor_letter']) ?>&type=document"
                   target="_blank" class="btn btn-xs btn-outline-primary">
                  <i class="fas fa-file-pdf"></i> ክፈት
                </a>
              </td>
              <td>—</td>
            </tr>
          <?php endif; ?>

       <?php if (!empty($documentData)): ?>
  <?php foreach ($documentData as $document): ?>
    <?php
      $type = $document['entity_type'];
      if ($type === 'REMOVAL') {
          $type = 'ዋስትና የተነሳበት';
      }
    ?>
    <tr>
      <td><?= $counter++ ?></td>
      <td><?= htmlspecialchars($type) ?></td>
      <td>
        <?php if (!empty($document['file_url'])): ?>
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($document['file_url']) ?>&type=document"
             target="_blank" class="btn btn-xs btn-outline-info">
            <i class="fas fa-file-pdf"></i> ክፈት
          </a>
        <?php endif; ?>
      </td>
      <td>
        <?php if (isset($document['owner_type']) && $document['owner_type'] === 'employee'): ?>
          <button
            type="button"
            class="btn btn-xs btn-outline-warning btn-edit-document"
            data-toggle="modal"
            data-target="#fileAttachmentModal"
            data-mode="edit"
            data-id="<?= htmlspecialchars($document['id']) ?>"
            data-type="<?= htmlspecialchars($document['entity_type']) ?>"
          >
            <i class="fas fa-edit"></i> አስተካክል
          </button>
        <?php else: ?>
          <span class="text-muted">—</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
<?php endif; ?>

        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Single Upload/Edit Modal -->
<div class="modal fade" id="fileAttachmentModal" tabindex="-1" role="dialog" aria-labelledby="fileAttachmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h6 class="modal-title font-weight-bold" id="modalTitle">
          <i class="fas fa-plus mr-1"></i> መረጃ ማህደር ጋር ማያያዝ
        </h6>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <!-- Single form — action switches between upload and edit -->
      <form id="documentForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/upload-certificate" method="POST" enctype="multipart/form-data">

        <!-- Mode: 'upload' or 'edit' -->
        <input type="hidden" name="_mode" id="formMode" value="upload">

        <!-- Document ID (only used in edit mode) -->
        <input type="hidden" name="document_id" id="documentId" value="">

        <!-- Employee UUID -->
        <input type="hidden" name="employee_uuid" value="<?= htmlspecialchars($uuid) ?>">

        <div class="modal-body">

          <!-- Employee UUID display -->
          <div class="form-group">
            <label>የሰራተኛ መለያ</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($uuid) ?>" readonly>
          </div>

          <!-- Certificate Type select -->
          <div class="form-group">
            <label for="certificate_type">የሚያያዘው ፋይል ዓይነት <span class="text-danger">*</span></label>
            <select name="certificate_type" id="certificate_type" class="form-control" required>
              <option value="" disabled selected>-- አይነት ይምረጡ --</option>
              <option value="የትምህርት ምስክር ወረቀት">የትምህርት ምስክር ወረቀት</option>
              <option value="የልምድ ምስክር ወረቀት">የልምድ ምስክር ወረቀት</option>
              <option value="የስልጠና ምስክር ወረቀት">የስልጠና ምስክር ወረቀት</option>
              <option value="የስኮላርሺፕ ምስክር ወረቀት">የስኮላርሺፕ ምስክር ወረቀት</option>
              <option value="የዕድገት ምስክር ወረቀት">የዕድገት ምስክር ወረቀት</option>
              <option value="ሌላ">ሌላ</option>
            </select>
          </div>

          <!-- ሌላ custom input -->
          <div class="form-group" id="other_type_group" style="display:none;">
            <label for="certificate_type_other">እባክዎ ዓይነቱን በጽሁፍ ያስገቡ <span class="text-danger">*</span></label>
            <input
              type="text"
              name="certificate_type_other"
              id="certificate_type_other"
              class="form-control"
              placeholder="የምስክር ወረቀቱን አይነት ይጻፉ..."
              disabled
            >
          </div>

          <!-- File input -->
          <div class="form-group">
            <label for="attachment" id="fileLabel">
              ፋይል ያያይዙ <span class="text-danger" id="fileRequired">*</span>
            </label>
            <!-- Edit mode note -->
            <p id="editFileNote" class="text-muted small" style="display:none;">
              <i class="fas fa-info-circle"></i> አዲስ ፋይል ካልመረጡ፣ የነበረው ፋይል ይቆያል።
            </p>
            <div class="custom-file">
              <input
                type="file"
                name="certificate_file"
                class="custom-file-input"
                id="attachment"
                accept=".pdf,.jpg,.jpeg,.png"
              >
              <label class="custom-file-label" for="attachment">
                ፋይል ይምረጡ (PDF/Image)...
              </label>
            </div>
            <small class="text-muted">የሚፈቀዱ ፋይሎች: PDF, JPG, PNG — ከፍተኛ መጠን: 5MB</small>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> ሰርዝ
          </button>
          <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="fas fa-save mr-1"></i> መዝግብ
          </button>
        </div>

      </form>
    </div>
  </div>
</div>