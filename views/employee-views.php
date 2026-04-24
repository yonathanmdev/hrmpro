<?php
use App\Helpers\EthiopianDateHelper; 
?>
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">የተመዘገበ ሰራተኛ መረጃ</h3>
        <div class="card-tools">
          <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/employee-views" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> ተመለስ
          </a>
        </div>
      </div>

      <div class="card-body">
         <input type="hidden" name="uuid" value="<?= htmlspecialchars($employee['uuid'] ?? '') ?>">
<div class="row border-bottom pb-3 mb-4">
    
    <div class="col-md-2 text-center">
        <div class="form-group">
            <label class="d-block">ፎቶ</label>
            <?php if (!empty($employee['employee_image'])): ?>
                <img src="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['employee_image']) ?>&type=image" 
                     alt="Employee Photo" 
                     class="img-thumbnail rounded shadow-sm" 
                     style="width: 140px; height: 160px; object-fit: cover; border: 2px solid #dee2e6;">
            <?php else: ?>
                <div class="img-thumbnail d-flex align-items-center justify-content-center bg-light" style="width: 140px; height: 160px; margin: 0 auto;">
                    <i class="fas fa-user-circle fa-5x text-muted"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-10">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="employee_id" class="text-primary">የሰራተኛው መለያ ቁጥር</label>
                    <input type="text" class="form-control bg-light" id="employee_id" name="employee_id" value="<?= htmlspecialchars($employee['employee_id'] ?? '') ?>" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="pension_number" class="text-primary">የጡረታ መለያ ቁጥር</label>
                    <input type="text" class="form-control bg-light" id="pension_number" name="pension_number" value="<?= htmlspecialchars($employee['pension_number'] ?? '') ?>" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="first_name" class="text-primary">ስም</label>
                    <input type="text" class="form-control bg-light font-weight-bold" id="first_name" name="first_name" value="<?= htmlspecialchars($employee['first_name'] ?? '') ?>" readonly>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="father_name">የአባት ስም</label>
                    <input type="text" class="form-control bg-light" id="father_name" name="father_name" value="<?= htmlspecialchars($employee['father_name'] ?? '') ?>" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="g_father_name">የአያት ስም</label>
                    <input type="text" class="form-control bg-light" id="g_father_name" name="g_father_name" value="<?= htmlspecialchars($employee['g_father_name'] ?? '') ?>" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="mother_name">የእናት ሙሉ ስም</label>
                    <input type="text" class="form-control bg-light" id="mother_name" name="mother_name" value="<?= htmlspecialchars($employee['mother_name'] ?? '') ?>" readonly>
                </div>
            </div>
        </div>
    </div>
</div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="sex">ጾታ</label>
                <input type="text" class="form-control" id="sex" name="sex" readonly" value="<?= htmlspecialchars($employee['sex'] ?? '') ?>" readonly>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="birth_date">የትውልድ ቀን</label>
                <?php
                // Split the database date (YYYY-MM-DD)
$dateParts = explode('-', $employee['birth_date']);
$ethDate = EthiopianDateHelper::toEthCalendar($dateParts[2], $dateParts[1], $dateParts[0]);
?>
                <input type="text" class="form-control" id="birth_date" name="birth_date" value="<?= EthiopianDateHelper::getMonthName($ethDate['month']) ?> <?= $ethDate['day'] ?> <?= $ethDate['year'] ?> ዓ.ም." readonly>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="phone_number">ስቁ.</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= htmlspecialchars($employee['phone_number'] ?? '') ?>" readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="yegabcha_huneta">የጋብቻ ሁኔታ</label>
              <input type="text" class="form-control" id="yegabcha_huneta" name="yegabcha_huneta"   value="<?= htmlspecialchars($employee['yegabcha_huneta'] ?? '') ?>" readonly>
                   
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="job_property_id">የስራ መደብ</label>
                 <input type="text" class="form-control" id="job_property_id" name="job_property_id"  value="<?= htmlspecialchars($employee['job_name'] ?? '') ?>" readonly>
               
                </div>
            </div>
          
            <div class="col-md-4">
              <div class="form-group">
                <label for="date_of_employed">የቅጥር ቀን</label>
                  <?php
                // Split the database date (YYYY-MM-DD)
$empdateParts = explode('-', $employee['date_of_employed']);
$empethDate = EthiopianDateHelper::toEthCalendar($empdateParts[2], $empdateParts[1], $empdateParts[0]);
?>
                <input type="text" class="form-control" id="date_of_employed" name="date_of_employed" value="<?= EthiopianDateHelper::getMonthName($empethDate['month']) ?> <?= $empethDate['day'] ?>  <?= $empethDate['year'] ?> ዓ.ም." readonly>
           
            </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="level_of_education">የትምህርት ደረጃ</label>
                <select id="level_of_education" name="level_of_education" class="form-control" required="" disabled>
                  <option selected="" disable="" value="">-- ይምረጡ --</option>
                  <option value="የቀለም" <?= ($employee['level_of_education'] ?? '') === 'የቀለም' ? 'selected' : '' ?>>የቀለም</option>
                  <option value="8ኛ_ያጠናቀቀ" <?= ($employee['level_of_education'] ?? '') === '8ኛ_ያጠናቀቀ' ? 'selected' : '' ?>>8ኛ ያጠናቀቀ</option>
                  <option value="10ኛ_ያጠናቀቀ" <?= ($employee['level_of_education'] ?? '') === '10ኛ_ያጠናቀቀ' ? 'selected' : '' ?>>10ኛ ያጠናቀቀ</option>
                  <option value="12ኛ_ያጠናቀቀ" <?= ($employee['level_of_education'] ?? '') === '12ኛ_ያጠናቀቀ' ? 'selected' : '' ?>>12ኛ ያጠናቀቀ</option>
                  <option value="ደረጃ_1" <?= ($employee['level_of_education'] ?? '') === 'ደረጃ_1' ? 'selected' : '' ?>>ደረጃ 1</option>
                  <option value="ደረጃ_2" <?= ($employee['level_of_education'] ?? '') === 'ደረጃ_2' ? 'selected' : '' ?>>ደረጃ 2</option>
                  <option value="ደረጃ_3" <?= ($employee['level_of_education'] ?? '') === 'ደረጃ_3' ? 'selected' : '' ?>>ደረጃ 3</option>
                  <option value="ደረጃ_4" <?= ($employee['level_of_education'] ?? '') === 'ደረጃ_4' ? 'selected' : '' ?>>ደረጃ 4</option>
                  <option value="ደረጃ_5" <?= ($employee['level_of_education'] ?? '') === 'ደረጃ_5' ? 'selected' : '' ?>>ደረጃ 5</option>
                  <option value="የመጀመሪያ_ዲግሪ" <?= ($employee['level_of_education'] ?? '') === 'የመጀመሪያ_ዲግሪ' ? 'selected' : '' ?>>የመጀመሪያ ዲግሪ</option>
                  <option value="ሁለተኛ_ዲግሪ" <?= ($employee['level_of_education'] ?? '') === 'ሁለተኛ_ዲግሪ' ? 'selected' : '' ?>>ሁለተኛ ዲግሪ</option>
                  <option value="ሶስተኛ_ዲግሪ" <?= ($employee['level_of_education'] ?? '') === 'ሶስተኛ_ዲግሪ' ? 'selected' : '' ?>>ሶስተኛ ዲግሪ</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="department">የሙያ ዘርፍ</label>
                <input type="text" class="form-control" id="department" name="department" value="<?= htmlspecialchars($employee['department'] ?? '') ?>" readonly>
              </div>
            </div>
          
            <div class="col-md-4">
              <div class="form-group">
                <label for="employment_situation">የቅጥር ሁኔታ </label>
                 <select class="form-control" id="employment_situation" name="employment_situation" required disabled>
                     <option value="">ይምረጡ</option>
                  <option <?= ($employee['employment_situation'] ?? '') === 'ቋሚ' ? 'selected' : '' ?>>ቋሚ</option>
                    <option <?= ($employee['employment_situation'] ?? '') === 'ጊዜያዊ' ? 'selected' : '' ?>>ጊዜያዊ</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="immidate_boss">የቅርብ ተጠሪ</label>
                <input type="text" class="form-control" id="immidate_boss" name="immidate_boss" value="<?= htmlspecialchars($employee['immidate_boss'] ?? '') ?>" required readonly>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="annual_rest">የዓመት እረፍት</label>
                <input type="number" step="1" min="0" class="form-control" id="annual_rest" name="annual_rest" value="<?= htmlspecialchars($employee['annual_rest'] ?? '0') ?>" readonly>
              </div>
            </div>
          
            <div class="col-md-4">
              <div class="form-group">
                <label for="effeciency">Efficiency (%)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="effeciency" name="effeciency" value="<?= htmlspecialchars($employee['effeciency'] ?? '') ?>" readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="level_of_effeciency">Level of Efficiency</label>
                <input type="text" class="form-control" id="level_of_effeciency" name="level_of_effeciency" value="<?= htmlspecialchars($employee['level_of_effeciency'] ?? '') ?>" readonly >
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="no_of_files_in_folder">የማህደር የፋይል ብዛት</label>
                <input type="number" step="1" min="0" class="form-control" id="no_of_files_in_folder" name="no_of_files_in_folder" value="<?= htmlspecialchars($employee['no_of_files_in_folder'] ?? '0') ?>" readonly>
              </div>
            </div>
          
            <div class="col-md-4">
              <div class="form-group">
                <label for="experience">የስራ ልምድ</label>
                <input type="text" class="form-control" id="experience" name="experience" value="<?= htmlspecialchars($employee['experience'] ?? '') ?>" readonly>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="displin_situation">የዲሲፕሊን ሁኔታ</label>
                 <select class="form-control" id="displin_situation" name="displin_situation" required disabled>
                     <option value="" <?= empty($employee['displin_situation']) ? 'selected' : '' ?>>-- ይምረጡ --</option>   
                     <option value="ምንም የቅጣት ሪኮርድ የሌለባቸው" <?= ($employee['displin_situation'] ?? '') === 'ምንም የቅጣት ሪኮርድ የሌለባቸው' ? 'selected' : '' ?>>ምንም የቅጣት ሪኮርድ የሌለባቸው</option>
                     <option value="የጽሁፍ ማስጠንቀቂያ የተሰጣቸው" <?= ($employee['displin_situation'] ?? '') === 'የጽሁፍ ማስጠንቀቂያ የተሰጣቸው' ? 'selected' : '' ?>>የጽሁፍ ማስጠንቀቂያ የተሰጣቸው</option>
                     <option value="እስከ 15 ቀን የሚደርስ የደመወዝ ቅጣት የተቀጡ" <?= ($employee['displin_situation'] ?? '') === 'እስከ 15 ቀን የሚደርስ የደመወዝ ቅጣት የተቀጡ' ? 'selected' : '' ?>>እስከ 15 ቀን የሚደርስ የደመወዝ ቅጣት የተቀጡ</option>
                     <option value="እስከ 3 ወር የሚደርስ የደመወዝ ቅጣት የተቀጡ" <?= ($employee['displin_situation'] ?? '') === 'እስከ 3 ወር የሚደርስ የደመወዝ ቅጣት የተቀጡ' ? 'selected' : '' ?>>እስከ 3 ወር የሚደርስ የደመወዝ ቅጣት የተቀጡ</option>
                     <option value="እስከ 2 ዓመት ለሚደርስ ጊዜ ከደረጃና ከደመወዝ ዝቅ የተደረጉ" <?= ($employee['displin_situation'] ?? '') === 'እስከ 2 ዓመት ለሚደርስ ጊዜ ከደረጃና ከደመወዝ ዝቅ የተደረጉ' ? 'selected' : '' ?>>እስከ 2 ዓመት ለሚደርስ ጊዜ ከደረጃና ከደመወዝ ዝቅ የተደረጉ</option>
                 </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="competency_situation">የብቃት ሁኔታ</label>
                 <select class="form-control" id="competency_situation" name="competency_situation" disabled>
                     <option value="የበቁ" <?= ($employee['competency_situation'] ?? '') === 'የበቁ' ? 'selected' : '' ?>>የበቁ</option>
                     <option value="ያልበቁ" <?= ($employee['competency_situation'] ?? '') === 'ያልበቁ' ? 'selected' : '' ?>>ያልበቁ</option>
                     <option value="ያልተመዘኑ" <?= ($employee['competency_situation'] ?? '') === 'ያልተመዘኑ' ? 'selected' : '' ?>>ያልተመዘኑ</option>
                 </select>
              </div>
            </div>
          </div>

          <div class="row">
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="employee_file201">የት/ት ማስረጃ እና ሌሎች</label>
                <?php if (!empty($employee['employee_file201'])): ?>
            <small class="form-text text-muted">አሁን የሆነ ፋይል: <a href="<?= rtrim($_ENV['BASE_URL'], '/') ?>/serve-file?file=<?= htmlspecialchars($employee['employee_file201']) ?>&type=document" target="_blank">ተመልክት</a></small>
                <?php endif; ?>
              </div>
            </div>
          </div>
      </div>
    </div>
  </div>
</section>