<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>የሰራተኞች አስተዳደር teddy</h1>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-registration">
                    <i class="fas fa-plus"></i> ሰራተኛ መዝግብ
                </button>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <table id="empTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>መለያ ቁጥር</th>
                            <th>ፎቶ</th>
                            <th>ሙሉ ስም</th>
                            <th>ፆታ</th>
                            <th>የስራ መደብ</th>
                            <th>ደመወዝ (ከጆይን)</th>
                            <th>ስልክ</th>
                            <th>ተግባር</th>
                        </tr>
                    </thead>
                   
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-registration">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">አዲስ ሰራተኛ መዝግብ</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="/employee/store" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>የሰራተኛ መለያ ቁጥር *</label>
                            <input type="text" name="employee_id" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>ስም *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>የአባት ስም *</label>
                            <input type="text" name="father_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>የአያት ስም *</label>
                            <input type="text" name="g_father_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>የእናታቸው ስም</label>
                            <input type="text" name="mother_name" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>ፆታ *</label>
                            <select name="sex" class="form-control" required>
                                <option value="ወንድ">ወንድ</option>
                                <option value="ሴት">ሴት</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>የትውልድ ቀን (ቀ/ወ/ዓ) *</label>
                            <div class="d-flex">
                                <input type="number" name="b_day" placeholder="ቀን" class="form-control mr-1" min="1" max="30" required>
                                <input type="number" name="b_month" placeholder="ወር" class="form-control mr-1" min="1" max="13" required>
                                <input type="number" name="b_year" placeholder="ዓመት" class="form-control" min="1940" max="2015" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>የቅጥር ቀን (ቀ/ወ/ዓ) *</label>
                            <div class="d-flex">
                                <input type="number" name="h_day" placeholder="ቀን" class="form-control mr-1" min="1" max="30" required>
                                <input type="number" name="h_month" placeholder="ወር" class="form-control mr-1" min="1" max="13" required>
                                <input type="number" name="h_year" placeholder="ዓመት" class="form-control" min="1980" max="2030" required>
                            </div>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>መስሪያ ቤት (Branch) *</label>
                            <select name="branch_id" class="form-control" required>
                                <?php foreach($branches as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= $b['branch_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>የስራ መደብ (Job) *</label>
                            <select name="job_property_id" class="form-control" required>
                                <?php foreach($jobs as $j): ?>
                                    <option value="<?= $j['id'] ?>"><?= $j['job_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>ስልክ ቁጥር *</label>
                            <input type="text" name="phone_number" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>የትምህርት ደረጃ</label>
                            <input type="text" name="edu_level" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>የፋይል ብዛት</label>
                            <input type="number" name="file_count" class="form-control" value="1">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>ፎቶ</label>
                            <input type="file" name="emp_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>ማህደር (201 File)</label>
                            <input type="file" name="emp_file" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">ዝጋ</button>
                    <button type="submit" class="btn btn-primary">መዝግብ</button>
                </div>
            </form>
        </div>
    </div>
</div>