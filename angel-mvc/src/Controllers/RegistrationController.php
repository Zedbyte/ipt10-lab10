<?php 

namespace App\Controllers;

use App\Models\User;

// require_once '../../vendor/autoload.php';


class RegistrationController extends BaseController
{

    public function index() {
        $template = 'registration-form';
        $output = $this->render($template);
        return $output;
    }

    public function register() {
        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $errors = [];

        try {

            $errors = $this->userDetailValidation($_POST);

            $username = $_POST['username'];
            $email = $_POST['email'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $password = $_POST['password'];

            if (!empty($errors)) {
                // Registration form with errors
                $data = [
                    'errors' => $errors,
                    'username' => $username,
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ];
    
                return $this->render('registration-form', $data);
            }

            $user_data = [
                'username' => $username,
                'email' => $email,
                'first_name' => $first_name ?? null,
                'last_name' => $last_name ?? null,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT)
            ];

            // If all checks pass, save the data using the User model
            $user = new User();
            $save_result = $user->save($user_data);

            if ($save_result > 0) {
                $reg_data = [
                    'title' => 'Registration Successful',
                    'message' => 'Successful Registration! You may proceed to the login form.',
                    'login' => true
                ];
            } else {
                $reg_data = [
                    'title' => 'Registration Error',
                    'message' => 'Something went wrong with your registration. Try again.',
                    'login' => false
                ];
            }

            return $this->render('success', $reg_data);
        } catch (\Exception $e) {
            // Catch and display any errors
            echo "Error: " . $e->getMessage();
        }
    }

    public function userDetailValidation($data) {

        $errors = [];

        // Required field check
        if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['confirm_password'])) {
            $errors[] = "Please fill up required fields.";
        }

        // Password length check
        if (strlen($data['password']) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }

        // Numeric character check
        if (!preg_match('/[0-9]/', $data['password'])) {
            $errors[] = "Password must contain at least one numeric character.";
        }

        // Non-numeric character check
        if (!preg_match('/[a-zA-Z]/', $data['password'])) {
            $errors[] = "Password must contain at least one non-numeric character.";
        }

        // Special character check
        if (!preg_match('/[\W]/', $data['password'])) {
            $errors[] = "Password must contain at least one special character (!@#$%^&*-+).";
        }

        // Password confirmation check
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = "Passwords do not match.";
        }

        return $errors;
    }
}



