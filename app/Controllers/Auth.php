<?php

namespace App\Controllers;

use App\Models\PlanModel;
use App\Models\SubscriptionModel;
use App\Models\UserModel;
use App\Services\Tenancy\TenantProvisioningService;

class Auth extends BaseController
{
    protected $userModel;
    protected $storeModel;

    public function __construct()
    {
        helper('audit'); // Load the audit helper for logging actions
        // Initialize the UserModel
        $this->userModel = new UserModel();
        $this->storeModel = new \App\Models\StoreModel(); // Assuming you have a StoreModel for store-related operations
    }

    public function login()
    {
        $session = session();

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
            $session->set([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'is_logged_in' => true,
                'role_id' => $user['role_id'],
            ]);

            $this->applyLocale($user['preferred_locale'] ?? null);

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
            'title' => 'Login',
            'message' => $this->buildRegisteredMessage(),
            'loginUsername' => trim((string) $this->request->getGet('username')),
            'loginEmail' => trim((string) $this->request->getGet('email')),
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
                'name' => 'required|min_length[3]',
                'company_name' => 'required|min_length[2]|max_length[191]',
                'company_slug' => 'required|min_length[3]|max_length[50]|alpha_dash',
                'tenant_base_url' => 'required|valid_url|max_length[255]'
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
                'is_active' => 1,
            ];

            $companyName = trim((string) $this->request->getPost('company_name'));
            $companySlug = trim((string) $this->request->getPost('company_slug'));
            $tenantBaseUrl = $this->normalizeTenantBaseUrl((string) $this->request->getPost('tenant_base_url'));

            if ($tenantBaseUrl === '') {
                return redirect()->back()->withInput()->with('error', 'Tenant base URL is invalid');
            }

            $tenantProvisioning = new TenantProvisioningService();
            $slugCheck = $tenantProvisioning->validateSlug($companySlug);
            if (!$slugCheck[0]) {
                return redirect()->back()->withInput()->with('error', $slugCheck[1]);
            }

            $companySlug = $slugCheck[1];

            $db = \Config\Database::connect();
            $db->transBegin();

            $storeId = $this->storeModel->insert([
                'name' => $companyName,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => '',
                'receipt_header' => $companyName,
                'receipt_footer' => 'Thank you for your business',
                'is_active' => 1,
                'is_default' => 1,
                'currency_code' => 'PKR',
                'currency_symbol' => 'Rs',
                'timezone' => 'Asia/Karachi',
                'website_url' => $tenantBaseUrl . '/' . $companySlug,
            ], true);

            if (!$storeId) {
                $db->transRollback();
                $errors = $this->storeModel->errors();
                $dbError = $db->error();

                if (!empty($dbError['message'])) {
                    log_message('error', 'Registration store creation failed: {message}', [
                        'message' => $dbError['message'],
                    ]);
                }

                $message = 'Unable to create company store';
                if (!empty($errors)) {
                    $message .= ': ' . implode(' | ', $errors);
                } elseif (ENVIRONMENT !== 'production' && !empty($dbError['message'])) {
                    $message .= ': ' . $dbError['message'];
                }

                return redirect()->back()->withInput()->with('error', $message);
            }

            $data['store_id'] = (int) $storeId;

            $userId = $this->userModel->insert($data, true);
            if (!$userId) {
                $db->transRollback();
                $errors = $this->userModel->errors();
                return redirect()->back()->withInput()->with('error', 'Unable to create user account' . (!empty($errors) ? (': ' . implode(' | ', $errors)) : ''));
            }

            $linked = $this->storeModel->addUserToStore((int) $userId, (int) $storeId);
            if (!$linked) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Unable to map user to company store');
            }

            $planModel = new PlanModel();
            $starter = $planModel->findByCode('starter');
            if (!$starter) {
                $starterId = $planModel->insert([
                    'code' => 'starter',
                    'name' => 'Starter',
                    'price_monthly' => 0,
                    'price_yearly' => 0,
                    'currency' => 'USD',
                    'trial_days' => 14,
                    'features' => json_encode(['analytics' => false, 'backups' => false, 'api' => false, 'multi_warehouse' => false, 'whatsapp' => false, 'import_export' => true]),
                    'is_active' => 1,
                ], true);

                if (!$starterId) {
                    $db->transRollback();
                    $errors = $planModel->errors();
                    $dbError = $db->error();

                    if (!empty($dbError['message'])) {
                        log_message('error', 'Registration starter plan creation failed: {message}', [
                            'message' => $dbError['message'],
                        ]);
                    }

                    $message = 'Unable to create starter plan';
                    if (!empty($errors)) {
                        $message .= ': ' . implode(' | ', $errors);
                    } elseif (ENVIRONMENT !== 'production' && !empty($dbError['message'])) {
                        $message .= ': ' . $dbError['message'];
                    }

                    return redirect()->back()->withInput()->with('error', $message);
                }

                $starter = $planModel->find($starterId);
            }

            if ($starter && (int) ($starter['trial_days'] ?? 0) <= 0) {
                $planModel->update((int) $starter['id'], ['trial_days' => 14]);
                $starter = $planModel->find((int) $starter['id']);
            }

            $subs = new SubscriptionModel();
            $trialDays = max(0, (int) ($starter['trial_days'] ?? 0));
            $trialEndsAt = $trialDays > 0 ? date('Y-m-d H:i:s', strtotime('+' . $trialDays . ' days')) : null;
            $subId = $subs->insert([
                'user_id' => (int) $userId,
                'store_id' => (int) $storeId,
                'plan_id' => (int) ($starter['id'] ?? 0),
                'status' => $trialDays > 0 ? 'trialing' : 'active',
                'is_trial' => $trialDays > 0 ? 1 : 0,
                'trial_ends_at' => $trialEndsAt,
                'renews_at' => $trialEndsAt,
                'ends_at' => null,
                'provider' => 'manual',
            ]);

            if (!$subId) {
                $db->transRollback();
                $errors = $subs->errors();
                $dbError = $db->error();

                if (!empty($dbError['message'])) {
                    log_message('error', 'Registration starter subscription creation failed: {message}', [
                        'message' => $dbError['message'],
                    ]);
                }

                $message = 'Unable to create starter subscription';
                if (!empty($errors)) {
                    $message .= ': ' . implode(' | ', $errors);
                } elseif (ENVIRONMENT !== 'production' && !empty($dbError['message'])) {
                    $message .= ': ' . $dbError['message'];
                }

                return redirect()->back()->withInput()->with('error', $message);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Registration failed while preparing company data');
            }

            try {
                $provision = $tenantProvisioning->provision((int) $storeId, $companyName, $companySlug, (int) $userId, $tenantBaseUrl);
            } catch (\Throwable $e) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Company provisioning failed: ' . $e->getMessage());
            }

            if (!$provision[0]) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Company provisioning failed: ' . $provision[1]);
            }

            $tenantData = $provision[1] ?? [];
            $tenantAppUrl = preg_replace('#/public/?$#i', '', trim((string) ($tenantData['app_url'] ?? ''))) ?? '';
            if ($tenantAppUrl !== '') {
                $this->storeModel->update((int) $storeId, ['website_url' => $tenantAppUrl]);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Registration failed while finalizing company setup');
            }

            $db->transCommit();
            // Log the registration action
            logAction('registration', 'New user registered: ' . $data['username'] . ' with email: ' . $data['email'], [
                'user_id' => (int) $userId,
                'store_id' => (int) $storeId,
            ]);

            $welcomeEmailSent = $this->sendRegistrationWelcomeEmail(
                $data['email'],
                $data['username'],
                $companyName,
                $tenantAppUrl !== '' ? $tenantAppUrl : ($tenantBaseUrl . '/' . $companySlug)
            );

            $tenantLoginUrl = '/login';
            if ($tenantAppUrl !== '') {
                $tenantLoginUrl = rtrim($tenantAppUrl, '/') . '/login?registered=1'
                    . '&username=' . rawurlencode((string) $data['username'])
                    . '&email=' . rawurlencode((string) $data['email'])
                    . '&welcome_email=' . ($welcomeEmailSent ? '1' : '0');
            }

            $message = 'Registration successful! Please login using the username and password you created.';
            if ($welcomeEmailSent) {
                $message .= ' A welcome email has been sent to ' . $data['email'] . '.';
            }

            return redirect()->to($tenantLoginUrl)->with('message', $message);
        }

        $data = [
            'title' => 'Register',
            'tenant_base_url_default' => $this->getDefaultTenantBaseUrl(),
        ];

        return view('auth/register', $data);
    }

    private function getDefaultTenantBaseUrl()
    {
        $uri = $this->request->getUri();
        $origin = $uri->getScheme() . '://' . $uri->getAuthority();

        $segments = array_values($uri->getSegments());
        $last = strtolower((string) end($segments));
        if (in_array($last, ['register', 'login'], true)) {
            array_pop($segments);
        }

        $rootFolder = strtolower(trim((string) basename(rtrim(ROOTPATH, "\\/"))));
        $last = strtolower((string) end($segments));
        if ($last === $rootFolder) {
            array_pop($segments);
        }

        $basePath = empty($segments) ? '' : ('/' . implode('/', $segments));
        return rtrim($origin . $basePath, '/');
    }

    private function normalizeTenantBaseUrl($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (!preg_match('#^https?://#i', $url)) {
            return '';
        }

        return rtrim($url, '/');
    }

    private function buildRegisteredMessage()
    {
        if ($this->request->getGet('registered') !== '1') {
            return null;
        }

        $username = trim((string) $this->request->getGet('username'));
        $email = trim((string) $this->request->getGet('email'));
        $welcomeEmailSent = $this->request->getGet('welcome_email') === '1';

        $parts = ['Registration successful. Sign in with the credentials you created during registration.'];

        if ($username !== '') {
            $parts[] = 'Username: ' . $username . '.';
        }

        if ($email !== '') {
            $parts[] = 'Email: ' . $email . '.';
        }

        if ($welcomeEmailSent && $email !== '') {
            $parts[] = 'A welcome email was sent to ' . $email . '.';
        }

        return implode(' ', $parts);
    }

    private function sendRegistrationWelcomeEmail($toEmail, $username, $companyName, $loginUrl)
    {
        $toEmail = trim((string) $toEmail);
        if ($toEmail === '') {
            return false;
        }

        try {
            $emailConfig = config('Email');
            if (empty($emailConfig->fromEmail)) {
                log_message('warning', 'Welcome email skipped because Email.fromEmail is not configured.');
                return false;
            }

            $email = service('email');
            $email->clear(true);
            $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName ?: 'POS System');
            $email->setTo($toEmail);
            $email->setSubject('Welcome to ' . $companyName);

            $message = implode("\n", [
                'Welcome to ' . $companyName . '.',
                '',
                'Your business workspace is ready.',
                'Login URL: ' . rtrim((string) $loginUrl, '/') . '/login',
                'Username: ' . $username,
                'Email: ' . $toEmail,
                'Password: Use the password you created during registration.',
                '',
                'If you forget your password, use the password reset option on the login page.',
            ]);

            $email->setMessage($message);

            if (!$email->send()) {
                log_message('warning', 'Welcome email failed for {email}: {error}', [
                    'email' => $toEmail,
                    'error' => method_exists($email, 'printDebugger') ? strip_tags((string) $email->printDebugger(['headers'])) : 'unknown mail error',
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            log_message('warning', 'Welcome email exception for {email}: {message}', [
                'email' => $toEmail,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
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

    public function setLanguage($locale = null)
    {
        $normalizedLocale = $this->normalizeLocale($locale);
        $this->applyLocale($normalizedLocale);

        $userId = (int) session('user_id');
        if ($userId > 0) {
            $this->persistUserLocale($userId, $normalizedLocale);
        }

        $returnUrl = (string) $this->request->getGet('return');
        if ($returnUrl !== '' && $this->isSafeReturnUrl($returnUrl)) {
            return redirect()->to($returnUrl);
        }

        try {
            return redirect()->back();
        } catch (\Throwable $e) {
            return redirect()->to('/');
        }
    }

    private function normalizeLocale($locale)
    {
        $supportedLocales = config('App')->supportedLocales ?? ['en'];
        $normalizedLocale = strtolower(trim((string) $locale));

        if (! in_array($normalizedLocale, $supportedLocales, true)) {
            return (string) (config('App')->defaultLocale ?? 'en');
        }

        return $normalizedLocale;
    }

    private function applyLocale($locale)
    {
        $normalizedLocale = $this->normalizeLocale($locale);

        session()->set('locale', $normalizedLocale);
        $this->request->setLocale($normalizedLocale);
        service('language')->setLocale($normalizedLocale);
    }

    private function persistUserLocale($userId, $locale)
    {
        try {
            $hasLocaleColumn = ! empty($this->userModel->db
                ->query("SHOW COLUMNS FROM `pos_users` LIKE 'preferred_locale'")
                ->getResultArray());

            if (! $hasLocaleColumn) {
                return;
            }

            $this->userModel->update($userId, ['preferred_locale' => $locale]);
        } catch (\Throwable $e) {
        }
    }

    private function isSafeReturnUrl($url)
    {
        if (! is_string($url) || trim($url) === '') {
            return false;
        }

        $base = parse_url(base_url());
        $target = parse_url($url);

        if ($target === false) {
            return false;
        }

        if (! isset($target['host'])) {
            return substr($url, 0, 1) === '/';
        }

        return isset($base['host']) && strcasecmp($target['host'], $base['host']) === 0;
    }
}
