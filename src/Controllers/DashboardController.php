<?php
namespace App\Controllers;

// 1. BaseControllerን እንዲወርስ (Extends) እናደርጋለን
class DashboardController extends BaseController {
    
    /**
     * የዳሽቦርድ ዋና ገጽ
     */
    public function index() {
        // 2. ለዳሽቦርዱ የሚያስፈልጉ ዳታዎችን እዚህ ማዘጋጀት ትችላለህ
        // ለምሳሌ፡ የተመዘገቡ ተጠቃሚዎችን ብዛት ከሞዴል መጥራት ትችላለህ
        $data = [
            'title' => 'HRM - ዳሽቦርድ',
            'user'  => $_SESSION['user'] ?? null,
            // 'total_users' => $userModel->countAll(), // ወደፊት የምንጨምረው
        ];

        // 3. BaseController ውስጥ ያለውን render በመጠቀም ቪው መጥራት
        // 'dashboard' ማለት views/dashboard.php ማለት ነው
        $this->render('dashboard', $data);
    }
   
}