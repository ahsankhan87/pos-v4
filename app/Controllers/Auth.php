<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        helper('audit'); // Load the audit helper for logging actions
        // Initialize the UserModel
        $this->userModel = new UserModel();
        $this->storeModel = new \App\Models\StoreModel(); // Assuming you have a StoreModel for store-related operations
    }

    public function login()
    {
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'username' => 'required',
                'password' => 'required|min_length[5]'
            ];
            // Validate the input
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            $user = $this->userModel->getUserByUsername($username);

            if (!$user || !password_verify($password, $user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Invalid username or password');
            }

            if (!$user['is_active']) {
                return redirect()->back()->withInput()->with('error', 'Your account is inactive');
            }

            // Set user session
            $session = session();
            $session->set([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'is_logged_in' => true,
                'role_id' => $user['role_id'],
            ]);

            // Check user's stores
            $userStores = $this->userModel->getUserStores($user['id']);

            if (count($userStores) === 0) {
                session()->destroy();
                return redirect()->to('/login')->with('error', 'You are not assigned to any active store.');
            }

            // Find default store
            $defaultStore = null;
            foreach ($userStores as $store) {
                if (!empty($store['is_default'])) {
                    $defaultStore = $store;
                    break;
                }
            }

            $session->set(['stores' => $userStores]);

            if ($defaultStore) {
                $session->set([
                    'store_id' => $defaultStore['id'],
                    'store_name' => $defaultStore['name'],
                    'store_address' => $defaultStore['address'],
                    'store_phone' => $defaultStore['phone'],
                    'currency_code' => $defaultStore['currency_code'],
                    'currency_symbol' => $defaultStore['currency_symbol']
                ]);
                logAction('login', 'User logged in: ' . $user['username'] . ' to default store: ' . $defaultStore['name']);
                return redirect()->to('/');
            }

            // If only one store, login to it
            if (count($userStores) === 1) {
                $store = $userStores[0];
                $session->set([
                    'store_id' => $store['id'],
                    'store_name' => $store['name'],
                    'store_address' => $store['address'],
                    'store_phone' => $store['phone'],
                    'currency_code' => $store['currency_code'],
                    'currency_symbol' => $store['currency_symbol']
                ]);
                // Log the login action
                logAction('login', 'User logged in: ' . $user['username'] . ' to store: ' . $store['name']);
                return redirect()->to('/');
            }

            // More than one store, redirect to selection page
            return redirect()->to('/stores/select');
        }

        $data = [
            'title' => 'Login'
        ];

        return  view('auth/login', $data);
    }

    public function register()
    {
        if ($this->request->getMethod() === 'POST') {

            $rules = [
                'username' => 'required|min_length[3]|max_length[50]|is_unique[pos_users.username]',
                'email' => 'required|valid_email|is_unique[pos_users.email]',
                'password' => 'required|min_length[5]',
                'password_confirm' => 'required|matches[password]',
                'name' => 'required|min_length[3]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'name' => $this->request->getPost('name'),
                'phone' => $this->request->getPost('phone'),
                'is_active' => 1 // Set to 0 if you want admin approval
            ];

            $this->userModel->save($data);
            // Log the registration action

            logAction('registration', 'New user registered: ' . $data['username'] . ' with email: ' . $data['email']);
            //
            // Redirect to login page with success message
            return redirect()->to('/login')->with('message', 'Registration successful! Please login.');
        }

        $data = [
            'title' => 'Register'
        ];

        return view('auth/register', $data);
    }

    public function forgotPassword()
    {
        if ($this->request->getMethod() === 'POST') {
            $rules = ['email' => 'required|valid_email'];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $email = $this->request->getPost('email');
            $user = $this->userModel->getUserByEmail($email);

            if (!$user) {
                return redirect()->back()->with('error', 'Email not found');
            }

            $token = $this->userModel->createResetToken($email);

            // In a real app, you would send an email here
            // For now, we'll just display the reset link
            $resetLink = base_url("reset-password/$token");
            // You can use a mailer library to send the reset link via email
            // For example, using CodeIgniter's email library:

            // Log the password reset request
            logAction('password_reset', 'Password reset requested for email: ' . $email);
            //
            return redirect()->back()->with('message', "Password reset link: <a href='$resetLink'>$resetLink</a>");
        }

        $data = [
            'title' => 'Forgot Password'
        ];

        return view('auth/forgot_password', $data);
    }

    public function resetPassword($token = null)
    {
        if (!$token) {
            return redirect()->to('/forgot-password');
        }

        $user = $this->userModel->verifyResetToken($token);
        if (!$user) {
            return redirect()->to('/forgot-password')->with('error', 'Invalid or expired token');
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'password' => 'required|min_length[6]',
                'password_confirm' => 'required|matches[password]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $this->userModel->update($user['id'], [
                'password' => $this->request->getPost('password'),
                'reset_token' => null,
                'reset_expires' => null
            ]);

            return redirect()->to('/login')->with('message', 'Password reset successfully');
        }

        $data = [
            'title' => 'Reset Password',
            'token' => $token
        ];

        return view('auth/reset_password', $data);
    }

    public function logout()
    {
        // session()->remove(['user_id', 'username', 'name', 'is_logged_in', 'store_id']);
        logAction('logout', 'User logged out: ' . session('username'));
        // Log the logout action
        session()->destroy();
        return redirect()->to('/login')->with('message', 'You have been logged out');
    }
}
