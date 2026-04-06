<?php
namespace App\Controllers;

use App\Models\Employee;
use Ramsey\Uuid\Uuid;

class EmployeeController extends BaseController {
    public function showmenuemployee() {
        $data = [
            'title' => 'HRM - ሰራተኛ መመዝገቢያ',
            'user'  => $_SESSION['user'] ?? null,
        ];
        $this->render('register-employee', $data);
    }

    public function handleFullEmployeeRegistration() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. የፋይል አያያዝ (Image and File 201)
            $image_name = $this->uploadFile('employee_image', 'uploads/images/');
            $file201_name = $this->uploadFile('employee_file201', 'uploads/documents/');
// በ handleFullEmployeeRegistration() ውስጥ የሚጨመር

// 1. የልደት ቀንን ማቀናጀት (ለምሳሌ: 12/05/1985)
$birth_date = $_POST['eth_year'] . '-' . $_POST['eth_month'] . '-' . $_POST['eth_day'];

// 2. የቅጥር ቀንን ማቀናጀት
$date_of_employed = $_POST['eth_startyear'] . '-' . $_POST['eth_startmonth'] . '-' . $_POST['eth_startday'];

// 3. የዳታ ስብስብ (ከ Modal input names ጋር የተጣጣመ)
$data = [
    'uuid' => Uuid::uuid4()->toString(),
    'employee_id' => trim($_POST['employee_companyid']), // input name='employee_companyid' ስለሆነ
    'first_name' => trim($_POST['employee_firstname']),
    'father_name' => trim($_POST['employee_middlename']),
    'g_father_name' => trim($_POST['employee_lastname']),
    'sex' => $_POST['sex'],
    'birth_date' => $birth_date,
    'phone_number' => trim($_POST['employee_contact']),
    'yegabcha_huneta' => $_POST['Yegabcha_huneta'],
    'organization_id' => $_SESSION['user']['organization_id'] ?? 1,
    'branch_id' => $_POST['employee_branches'], 
    'job_property_id' => $_POST['employee_position'],
    'date_of_employed' => $date_of_employed,
    'level_of_education' => $_POST['ttdereja'],
    'department' => $_POST['dpt'],
    'employment_situation' => $_POST['yektrhuneta'],
    'experience' => $_POST['sraafesasem'] ?? 'New', 
    'annual_rest' => (int)$_POST['ametreft'],
    'displin_situation' => $_POST['disipilin'],
    'competency_situation' => $_POST['bkathuneta'],
    'no_of_files_in_folder' => (int)$_POST['filebzat'],
    'pention_withdrawal' => $_POST['tmeleyaku'],
    'remark' => trim($_POST['mrmera']),
    'status' => 'active',
    'reg_by' => $_SESSION['user']['id']
];

            // 3. ወደ ሞዴል መላክ
            $employeeModel = new Employee($this->db);
            if ($employeeModel->createEmployee($data)) {
                $_SESSION['success'] = "ሙሉ የሰራተኛ መረጃ በትክክል ተመዝግቧል!";
            } else {
                $_SESSION['error'] = "መመዝገብ አልተሳካም!";
            }

            header("Location: " . $_ENV['BASE_URL'] . "/employee-list");
            exit();
        }
    }

    // ፋይል ለመስቀል የሚረዳ Helper Function
    private function uploadFile($inputName, $path) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === 0) {
            $fileName = time() . '_' . $_FILES[$inputName]['name'];
            if (!is_dir($path)) mkdir($path, 0777, true);
            move_uploaded_file($_FILES[$inputName]['tmp_name'], $path . $fileName);
            return $fileName;
        }
        return null;
    }


}