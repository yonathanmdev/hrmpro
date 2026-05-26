<?php
use App\Helpers\EthiopianDateHelper; 

$is_anual_rest_registration = true; ?>
<!-- ሰንጠረዥ እና ዋና ይዘት gyugy fffddf - -->
<section class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0 card-primary card-outline">

            <!-- Header -->
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-2">
                <h6 class="m-0 font-weight-bold text-dark">
                    <?= htmlspecialchars(trim(($employee['first_name'] ?? '') . ' ' . ($employee['father_name'] ?? ''))) ?> የአመት እረፍት
                </h6>
                <div class="ml-auto">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addannualRestModal">
                        <i class="fas fa-plus mr-1"></i> የአመት እረፍት መዝግብ
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="card-body p-0">
                <table id="example1" data-empty-msg="ምንም የአመት እረፍት አልተመዘገበም።" class="table table-bordered table-striped table-hover small mb-0" style="color: #000;">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>ሙሉ ስም</th>
                            <th>በጀት</th>
                            <th>የአመት እረፍት ብዛት (በቀን)</th>
                            <th>ሁኔታ</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody> 
                      <?php if (!empty($anualRestData)): $no =0;?>
                            <?php 
                            $no = 1;
                            foreach ( $anualRestData as $rest):
                                 ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($employee['first_name'] ?? '') . ' ' . htmlspecialchars($employee['father_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($rest['budget_year'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($rest['restaamout'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php 
                                            $status = $rest['status'] ?? 'unknown';
                                            $badgeClass = match ($status) {
                                                'approved' => 'success',
                                                'pending' => 'warning',
                                                'rejected' => 'danger',
                                                default => 'secondary',
                                            };
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>">
                                            <?= ucfirst(htmlspecialchars($status)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Action buttons (e.g., Edit, Delete) can go here -->
                                        <button class="btn btn-sm btn-info">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                           <?php endif;?>
                    </tbody> 
                </table>
            </div>
        </div>
    </div>
</section>

<?php // include 'partials/edit-experience-modal.php'; ?>

<!-- ===== Add Annual Leave Modal ===== -->
<div class="modal fade" id="addannualRestModal" tabindex="-1" role="dialog" aria-labelledby="addannualRestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            
            <form id="addAnnualLeaveForm" action="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-rest-store" method="post">
                
                <!-- 1. Modal Header -->
                <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                    <h6 class="modal-title font-weight-bold text-dark" id="addannualRestModalLabel">
                        <i class="fas fa-calendar-plus text-primary mr-1"></i> አዲስ ያልተጠቀመ የአመት እረፍት መዝግብ
                    </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- 2. Modal Body -->
                <div class="modal-body py-3">   
                    <!-- ሰራተኛውን ለመለየት የሚረዳ ድብቅ አይዲ -->
                    <input type="hidden" name="employee_uuid" id="employee_uuid" value="<?= htmlspecialchars($employee['uuid'] ?? '') ?>">

                    <div class="row">
                        <!-- በጀት አመት (Budget Year) -->
                        <div class="col-md-6 form-group mb-3">
                            <label for="budget_year" class="mb-1">
                                <small class="font-weight-bold" style="color: #000;">የበጀት አመት (ዓ.ም)</small> <span class="text-danger">*</span>
                            </label>
                            <select name="budget_year" id="budget_year" class="form-control form-control-sm" style="color: #000;" required>
                                <option value="">-- በጀት አመት ይምረጡ --</option>
                                <?php 
                                $current_year = 2018; 
                                for ($i = 0; $i < 4; $i++) {
                                    $year = $current_year - $i;
                                    echo "<option value='{$year}'>{$year} በጀት አመት</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- ያልተጠቀመበት የእረፍት ቀን ብዛት -->
                        <div class="col-md-6 form-group mb-3">
                            <label for="leave_days" class="mb-1">
                                <small class="font-weight-bold" style="color: #000;">የአመት እረፍት ብዛት (በቀን)</small> <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="leave_days" id="leave_days" class="form-control form-control-sm" 
                                   min="1" max="30" placeholder="ከ 1 እስከ 30" autocomplete="off" style="color: #000;" required>
                            <span id="days_error_msg" class="text-danger mt-1" style="display:none; font-size:11px; font-weight:bold;">
                                <i class="fas fa-exclamation-circle mr-1"></i> በአንድ በጀት አመት ከ 30 ቀን መብለጥ አይችልም!
                            </span>
                        </div>
                    </div>

                    <!-- ጠቅላላ መረጃ ማሳያ ካርድ -->
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="p-2 rounded" style="background-color: #f1f3f5; border-left: 4px solid #17a2b8;">
                                <small class="text-dark d-block" style="font-size: 11px; line-height: 1.4;">
                                    <i class="fas fa-info-circle text-info mr-1"></i> <strong>የህግ ማሳሰቢያ፦</strong> 
                                    በአንድ በጀት አመት የሚመዘገብ ከፍተኛው የእረፍት ቀን <strong>30 ቀን</strong> ሲሆን፣ የ3 ተከታታይ አመታት ድምር ከ <strong>90 ቀናት</strong> መብለጥ የለበትም።
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Modal Footer -->
                <div class="modal-footer d-flex justify-content-between" style="background-color: #f8f9fa; border-top: 1px solid #dee2e6;">
                    <button type="button" class="btn btn-default btn-sm font-weight-bold" data-dismiss="modal">ዝጋ</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold" id="submitLeaveBtn">
                        <i class="fas fa-save mr-1"></i> መረጃውን መዝግብ
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>