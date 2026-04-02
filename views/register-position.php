
 
<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Card -->
    <div class="card card-default">
      <div class="card-header">

        <h3 class="card-title">መደብ</h3>

        <div class="card-tools">
          <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#positionModal">
            አዲስ መደብ መዝግብ
          </button>
        </div>

      </div>

      <div class="card-body">
        <!-- Example Table (optional) -->
      <table id="example1" class="table table-bordered table-hover dataTable dtr-inline" aria-describedby="example2_info">
    <thead>
      <tr>
        <th>#</th>
        <th>ዳይሪክተር </th>
        <th>መደብ</th>
        <th>ደረጃ</th>
        <th>ደመወዝ</th>
        <th>የመደብ መታወቂያቁጥር</th>
        <th>እስታተርስ</th>
        <th>action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($positions)): ?>
        <?php foreach ($positions as $index => $row): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($row['director_name']) ?></td>
            <td><?= htmlspecialchars($row['job_name']) ?></td>
            <td><?= htmlspecialchars($row['dereja']) ?></td>
            <td><?= htmlspecialchars($row['salary']) ?></td>
            <td><?= htmlspecialchars($row['job_identifier_no']) ?></td>
            <td><?= $row['status'] ? 'Active' : 'Inactive' ?></td>
    
            <td>
               <button class="btn btn-primary btn-sm edit-position" 
                      data-id="<?= $row['id'] ?>" 
                      data-name="<?= htmlspecialchars($row['job_name']) ?>" title="አስተካክል"  >
                <i class="fas fa-edit"></i>
              </button> 
              <button class="btn btn-danger btn-sm delete-position" 
                      data-id="<?= $row['id'] ?>" 
                      data-name="<?= htmlspecialchars($row['job_name']) ?>" title="ሰርዝ">

                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="9" class="text-center">No positions found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
      </div>

    </div>
    <!-- /.card -->

  </div>
</section>
>

<!-- Modal (place OUTSIDE card) -->
<div class="modal fade" id="positionModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <form id="orgForm" method="POST" action="/HRM/register-position-process">

        <!-- Header -->
        <div class="modal-header bg-secondary">
          <h4 class="modal-title">አዲስ መደብ መዝግብ</h4>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <div class="form-group">
    <label>የስራ ክፍል</label>
    <select name="director_name" class="form-control">
    <option value="" selected disabled>ይምረጡ</option>
    <?php if (!empty($directors)): ?>
        <?php foreach ($directors as $row): ?>
            <option value="<?= htmlspecialchars($row['id']) ?>">
                <?= htmlspecialchars($row['director_name']) ?>
            </option>
        <?php endforeach; ?>
    <?php else: ?>
        <option value="">No directors available</option>
    <?php endif; ?>
</select>
</div>
          <div class="form-group">
            <label> የስራ መደቡ መጠሪያ</label>
                  <input type="text" class="form-control" placeholder="የስራ መደቡ መጠሪያ" name="position_name">
     
          </div>
          <div class="form-group">
            <label> የመደቡ መለያ ቁጥር</label>
                  <input type="text" class="form-control" placeholder="የመደቡ መለያ ቁጥር" name="position_code">
                
          </div>
          <div class="form-group">
            <label>የስራ ደረጃ</label>
               <select name="sera_dereja" class="form-control" required="">
         <option value="" disabled="disabled" selected="selected">← ይምረጡ →</option>
      <option>ሹመት</option>
	    <option>I</option>
	    <option>II</option>
	    <option>III</option>
	    <option>IV</option>
	    <option>V</option>
	    <option>VI</option>
	    <option>VII</option>
	    <option>VIII</option>
	    <option>IX</option>
	    <option>X</option>
	    <option>XI</option>
	    <option>XII</option>
	    <option>XIII</option>
	    <option>XIV</option>
	    <option>XV</option>
	    <option>XVI</option>
	    <option>XVII</option>
	    <option>XVIII</option>
	    <option>XIX</option>
	    <option>XX</option>
	    </select>
          </div>
     <div class="form-group">
             <label> የደረጃ እርከን</label>
               <select name="sera_rken" class="form-control" required="">
         <option value="" disabled="disabled" selected="selected">← ይምረጡ →</option>
             <option>ሹመት</option>
	    <option value="1">1</option>
	    <option value="2">2</option>
	    <option value="3">3</option>
	    <option value="4">4</option>
	    <option value="5">5</option>
	    <option value="6">6</option>
	    <option value="7">7</option>
	    <option value="8">8</option>
	    <option value="9">9</option>
	   
	    </select>     
          </div>
          <div class="form-group">
             <label> የመደቡ ደመወዝ</label>
                  <input type="number" class="form-control" placeholder="ስም ያስገቡ" name="salary">
                
          </div>
          <div class="form-group">
             <label>የተያዥ ሁኔታ</label>
                       <select name="yeteyash_huneta" class="form-control" required="">
<option value="" disabled="disabled" selected="selected">← ይምረጡ →</option>
	    <option value="ተያዥ የሚያስፈልገዉ">ተያዥ የሚያስፈልገዉ </option>
	    <option value="ተያዥ የማያስፈልገዉ">ተያዥ የማያስፈልገዉ </option> 
      </select>
          </div>
 <div class="form-group">
             <label>ነፃ ህክምና</label>
           <input type="checkbox" name="nesa_hkmna" value="yes">
          </div>

        <div class="form-group">
                 <label for="Corporate">ደንብ ልብስ ተጠቃሚ </label><br>
          <input type="checkbox" id="checkBox" onclick="enableDisable(this.checked, 'textBox')"> ተጠቃሚ ነዉ 
    <script language="javascript">
    function enableDisable(bEnable, textBoxID)
    {
         document.getElementById(textBoxID).disabled = !bEnable
    }
</script>
<!--<input type="text" id="textBox" disabled> -->
     <select name="cloth_duration" class="form-control" required id="textBox" disabled>
<option value="" disabled="disabled" selected="selected">&larr; ይምረጡ &rarr;</option>
	    <option value="በአመት አንድ"> በአመት አንድ </option>
	    <option value="በአመት ሁለት">በአመት ሁለት</option>
	     <option value="በአመት አንድ ጥንድ"> በአመት አንድ ጥንድ</option>
         <option value="በአመት ሁለት ጥንድ">በአመት ሁለት ጥንድ</option>
          <option value="በአመት ሶስት ጥንድ">በአመት ሶስት ጥንድ</option>
	    <option value="እንደ ስራ መሳሪያ የሚሰጥ በአመት ሁለት ">እንደ ስራ መሳሪያ የሚሰጥ በአመት ሁለት </option>
	     <option value="እንደ ስራ መሳሪያ የሚሰጥ"> እንደ ስራ መሳሪያ የሚሰጥ</option>
	    <option value="በሦስት ዓመት አንድ ">በሦስት ዓመት አንድ </option>
	     <option value="እንደ ስራ መሳሪያ የሚሰጥ በአመት አንድ">እንደ ስራ መሳሪያ የሚሰጥ በአመት አንድ</option>
	      </select> <br>
	      <textarea rows="4" cols="45" name="description" id="textBox" >
	     
	      
  
</textarea>
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
