<?php
namespace App\Controllers;

use App\Models\User;

// 1. BaseControllerን እንዲወርስ (Extends) እናደርጋለን
class AuthController extends BaseController {

    public function showLoginForm() {
        // BaseController ውስጥ ያለውን render በመጠቀም ቪው መጥራት
        $this->render('auth/login', [
            'title' => "HRMS - Login"
        ]);
    }

    public function handleLogin() {
        // ሴሽን አስቀድሞ በ index.php ተጀምሯል (session_start())
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1. ዳታውን መቀበል እና trim ማድረግ
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = $_POST['password'] ?? '';

            // 2. የ User ሞዴልን መጥራት (ከ BaseController የመጣውን $this->db በመስጠት)
            $userModel = new User($this->db);
            $user = $userModel->findByEmail($email);

            // 3. ተጠቃሚው መኖሩን እና ፓስወርዱን ማረጋገጥ
            if ($user && password_verify($password, $user['password'])) {
                
                // ለደህንነት ሴሽን ማደስ
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id'                 => $user['id'],
                    'first_name'         => $user['first_name'],
                    'father_name'        => $user['father_name'],
                    'grand_father_name'  => $user['grand_father_name'],
                    'branch_id'          => $user['branch_id'] ?? null,
                    'organization_id'    => $user['organization_id'] ?? null,
                    'email'              => $user['email'],
                    'role'               => $user['role']
                ];

                header("Location: /HRM/dashboard");
                exit();
            } else {
                $_SESSION['error'] = "ኢሜይል ወይም ፓስወርድ አልተዛመደም። እባክዎ እንደገና ይሞክሩ!";
                header("Location: /HRM/login");
                exit();
            }
        }
    }

    /**
     * ተጠቃሚው መግባቱን ማረጋገጫ (Middleware)
     */
 public static function checkAuth() {
    // ተጠቃሚው Login ካላደረገ
    if (!isset($_SESSION['user'])) {
        
        // ጥያቄው የመጣው በ Fetch/AJAX መሆኑን ማረጋገጥ
        // ይህ የሚታወቀው በ Header ውስጥ application/json ሲኖር ነው
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['SERVER_PROTOCOL'], 'HTTP') !== false && 
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            
            header('Content-Type: application/json');
            http_response_code(401); // Unauthorized status code
            echo json_encode([
                'status' => 'error', 
                'message' => 'session_expired',
                'redirect' => '/HRM/login'
            ]);
            exit();
        }

        // ለተለመደ የገጽ ጥያቄ (Direct Page Access)
        header("Location: /HRM/login");
        exit();
    }
}
    /**
     * መውጫ (Logout)
     */
    public function logout() {
        session_destroy();
        header("Location: /HRM/login");
        exit();
    }
}