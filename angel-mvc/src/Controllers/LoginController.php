<?php 

namespace App\Controllers;

use App\Models\User;

class LoginController extends BaseController
{
    private $sessionStarted = false;

    private function initializeSession() {
        if (!$this->sessionStarted && session_status() == PHP_SESSION_NONE) {
            session_start();
            $this->sessionStarted = true;
        }
    }

    public function index() {
        $this->initializeSession();

        // Default value for remaining attempts
        $data = [
            'remaining_attempts' => null
        ];

        return $this->renderView('login-form', $data);
    }

    public function login() {
        $this->initializeSession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                return $this->handleLoginFailure(["Username and password are required."]);
            }

            $user = new User();
            $password_hash = $user->getPassword($username)['password_hash'] ?? '';

            if ($this->isPasswordValid($password, $password_hash)) {
                $this->onSuccessfulLogin($username);
            } else {
                return $this->incrementLoginAttempts();
            }
        } else {
            return $this->index();
        }
    }

    private function isPasswordValid($inputPassword, $storedHash) {
        return password_verify($inputPassword, $storedHash);
    }

    private function onSuccessfulLogin($username) {
        // Reset attempts and store session data
        $_SESSION['login_attempts'] = 0;
        $_SESSION['is_logged_in'] = true;
        $_SESSION['username'] = $username;

        // Redirect after successful login
        header("Location: /welcome");
        exit;
    }

    private function incrementLoginAttempts() {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $remainingAttempts = 3 - $_SESSION['login_attempts'];

        if ($remainingAttempts <= 0) {
            return $this->handleLoginFailure(
                ["Too many failed login attempts. Please try again later."], true
            );
        }

        return $this->handleLoginFailure(
            ["Invalid username or password. Attempts remaining: $remainingAttempts."], false, $remainingAttempts
        );
    }

    private function handleLoginFailure($errors, $formDisabled = false, $remainingAttempts = null) {
        return $this->renderView('login-form', [
            'errors' => $errors,
            'form_disabled' => $formDisabled,
            'remaining_attempts' => $remainingAttempts
        ]);
    }

    public function logout() {
        $this->initializeSession();
        session_destroy();
        header("Location: /login-form");
        exit;
    }

    private function renderView($template, $data = []) {
        return $this->render($template, $data);
    }
}
