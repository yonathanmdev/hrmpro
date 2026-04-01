<?php
namespace App\Controllers;

class BaseController {
    protected $db;

    // ሁሉም ኮንትሮለሮች የዳታቤዝ ኮኔክሽን እንዲኖራቸው
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * ቪው ፋይሎችን በቀላሉ ለመጥራት (Render Views)
     * @param string $viewName የቪው ፋይሉ ስም (ለምሳሌ 'auth/login')
     * @param array $data ወደ ቪው የሚላክ ዳታ
     */
 protected function render($viewName, $data = []) {
    extract($data);
    
    $basePath = __DIR__ . "/../../views/";
    $viewPath = $basePath . $viewName . ".php";

    // 1. የሎጊን ገጽ መሆኑን መለየት (ለምሳሌ 'auth/login' ከሆነ)
    $isLogin = ($viewName === 'auth/login' || $viewName === 'login');

    if ($isLogin) {
        // ለሎጊን ገጽ ከሆነ ቪው ፋይሉን ብቻ አሳይ
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("ስህተት: የሎጊን ቪው ፋይል አልተገኘም: " . $viewPath);
        }
    } else {
        // ለሌሎች ገጾች (Dashboard, ወዘተ) Header እና Footer ጨምር
        $headerPath = $basePath . "layout/header.php";
        $footerPath = $basePath . "layout/allfooter.php";

        if (!file_exists($headerPath)) die("Header አልተገኘም: " . $headerPath);
        if (!file_exists($viewPath))   die("View አልተገኘም: " . $viewPath);
        if (!file_exists($footerPath)) die("Footer አልተገኘም: " . $footerPath);

        require_once $headerPath;
        require_once $viewPath;
        require_once $footerPath;
    }
}
}