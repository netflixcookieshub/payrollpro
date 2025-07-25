<?php
/**
 * User Controller
 */

require_once __DIR__ . '/../core/Controller.php';

class UserController extends Controller {
    
    public function index() {
        $this->checkAuth();
        $this->checkPermission('users');
        
        $userModel = $this->loadModel('User');
        $page = max(1, intval($_GET['page'] ?? 1));
        
        $result = $userModel->getUsersWithRoles('u.status != :status', ['status' => 'deleted'], $page);
        
        $this->loadView('users/index', [
            'users' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }
    
    public function create() {
        $this->checkAuth();
        $this->checkPermission('users');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $this->showCreateForm();
        }
    }
    
    public function edit($id) {
        $this->checkAuth();
        $this->checkPermission('users');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate($id);
        } else {
            $this->showEditForm($id);
        }
    }
    
    private function handleCreate() {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $userModel = $this->loadModel('User');
        $result = $userModel->createUser($data);
        
        if ($result['success']) {
            $this->logActivity('create_user', 'users', $result['id']);
            $this->redirect('/users?success=created');
        } else {
            $this->showCreateForm($result['errors'] ?? ['message' => $result['message']]);
        }
    }
    
    private function handleUpdate($id) {
        $data = $this->sanitizeInput($_POST);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token'], 400);
            return;
        }
        
        $userModel = $this->loadModel('User');
        $result = $userModel->updateUser($id, $data);
        
        if ($result['success']) {
            $this->logActivity('update_user', 'users', $id);
            $this->redirect('/users?success=updated');
        } else {
            $this->showEditForm($id, $result['errors'] ?? ['message' => $result['message']]);
        }
    }
    
    private function showCreateForm($errors = []) {
        $roles = $this->db->fetchAll("SELECT * FROM roles ORDER BY name ASC");
        
        $this->loadView('users/create', [
            'roles' => $roles,
            'errors' => $errors,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
    
    private function showEditForm($id, $errors = []) {
        $userModel = $this->loadModel('User');
        $user = $userModel->findById($id);
        
        if (!$user) {
            $this->loadView('errors/404');
            return;
        }
        
        $roles = $this->db->fetchAll("SELECT * FROM roles ORDER BY name ASC");
        
        $this->loadView('users/edit', [
            'user' => $user,
            'roles' => $roles,
            'errors' => $errors,
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }
}