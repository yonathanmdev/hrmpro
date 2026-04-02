<?php
namespace App\Controllers;
use App\Models\Developer;
// 1. BaseControllerን እንዲወርስ (Extends) እናደርጋለን
class TeddyController extends BaseController {
    
    /**
     * የዳሽቦርድ ዋና ገጽ
     */
 
 
    public function showemployee() {
        // 2. ለዳሽቦርዱ የሚያስፈልጉ ዳታዎችን እዚህ ማዘጋጀት ትችላለን
        // ለምሳሌ፡ የተመዘገቡ ተጠቃሚዎችን ብዛት ከሞዴል መጥራት ትችላለህ
        $data = [
            'title' => 'HRM - ሰራተኛ መመዝገቢያ',
            'user'  => $_SESSION['user'] ?? null,
            // 'total_users' => $userModel->countAll(), // ወደፊት የምንጨምረው
        ];

        // 3. BaseController ውስጥ ያለውን render በመጠቀም ቪው መጥራት
        // 'dashboard' ማለት views/dashboard.php ማለት ነው
        $this->render('register-employee', $data);
    }
    public function showdeveloper() {
        // 2. ለዳሽቦርዱ የሚያስፈልጉ ዳታዎችን እዚህ ማዘጋጀት ትችላለን
        // ለምሳሌ፡ የተመዘገቡ ተጠቃሚዎችን ብዛት ከሞዴል መጥራት ትችላለህ
        $developerModel = new Developer($this->db);
        $devdata = $developerModel->getAllDevelopers();
        $data = [
            'title' => 'HRM - አካውንት መመዝገቢያ',
            'user'  => $_SESSION['user'] ?? null,
            'devdata' => $devdata

        ];
    
        // 3. BaseController ውስጥ ያለውን render በመጠቀም ቪው መጥራት
        // 'dashboard' ማለት views/dashboard.php ማለት ነው
        $this->render('register-developer', $data);
    }
}
?>
    
