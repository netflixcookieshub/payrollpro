<?php
/**
 * Authentication Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class AuthController extends Controller {
    
    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            $this->loadView('auth/login', [
                'csrf_token' => $this->generateCSRFToken(),
                'error' => $_GET['error'] ?? '',
                'timeout' => $_GET['timeout'] ?? false
            ]);
        }
    }
    
    public function logout() {
        $this->logActivity('logout');
        session_destroy();
        $this->redirect('/login');
    }
    
    private function handleLogin() {
        $username = $this->sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        // Validate CSRF token
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->redirect('/login?error=invalid_token');
            return;
        }
        
        // Check for login attempts
        if ($this->isAccountLocked($username)) {
            $this->redirect('/login?error=account_locked');
            return;
        }
        
        $userModel = $this->loadModel('User');
        $user = $userModel->authenticate($username, $password);
        
        if ($user) {
            // Reset login attempts
            $this->resetLoginAttempts($username);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['permissions'] = $user['permissions'];
            $_SESSION['last_activity'] = time();
            
            $this->logActivity('login');
            $this->redirect('/dashboard');
        } else {
            $this->recordLoginAttempt($username);
            $this->redirect('/login?error=invalid_credentials');
        }
    }
    
    public function changePassword() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                $this->jsonResponse(['success' => false, 'message' => 'Passwords do not match'], 400);
                return;
            }
            
            if (strlen($newPassword) < 6) {
                $this->jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
                return;
            }
            
            $userModel = $this->loadModel('User');
            $result = $userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
            
            if ($result['success']) {
                $this->logActivity('change_password');
                $this->jsonResponse(['success' => true, 'message' => 'Password changed successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']], 400);
            }
        } else {
            $this->loadView('auth/change-password', [
                'csrf_token' => $this->generateCSRFToken()
            ]);
        }
    }
    
    public function profile() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => $this->sanitizeInput($_POST['full_name'] ?? ''),
                'email' => $this->sanitizeInput($_POST['email'] ?? '')
            ];
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
                return;
            }
            
            $userModel = $this->loadModel('User');
            $result = $userModel->updateProfile($_SESSION['user_id'], $data);
            
            if ($result) {
                $_SESSION['full_name'] = $data['full_name'];
                $this->logActivity('update_profile');
                $this->jsonResponse(['success' => true, 'message' => 'Profile updated successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update profile'], 400);
            }
        } else {
            $userModel = $this->loadModel('User');
            $user = $userModel->findById($_SESSION['user_id']);
            
            $this->loadView('auth/profile', [
                'user' => $user,
                'csrf_token' => $this->generateCSRFToken()
            ]);
        }
    }
    
    private function isAccountLocked($username) {
        $attempts = $_SESSION['login_attempts'][$username] ?? [];
        $recentAttempts = 0;
        $lockoutTime = time() - LOCKOUT_DURATION;
        
        foreach ($attempts as $attempt) {
            if ($attempt > $lockoutTime) {
                $recentAttempts++;
            }
        }
        
        return $recentAttempts >= MAX_LOGIN_ATTEMPTS;
    }
    
    private function recordLoginAttempt($username) {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        if (!isset($_SESSION['login_attempts'][$username])) {
            $_SESSION['login_attempts'][$username] = [];
        }
        
        $_SESSION['login_attempts'][$username][] = time();
        
        // Clean old attempts
        $cutoff = time() - LOCKOUT_DURATION;
        $_SESSION['login_attempts'][$username] = array_filter(
            $_SESSION['login_attempts'][$username],
            function($timestamp) use ($cutoff) {
                return $timestamp > $cutoff;
            }
        );
    }
    
    private function resetLoginAttempts($username) {
        if (isset($_SESSION['login_attempts'][$username])) {
            unset($_SESSION['login_attempts'][$username]);
        }
    }
}