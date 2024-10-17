<?php

namespace App\Models;

use App\Models\BaseModel;
use \PDO;

class User extends BaseModel
{
    public function save($user_data) {
        $sql = "INSERT INTO users (username, email, first_name, last_name, password_hash) 
                VALUES (:username, :email, :first_name, :last_name, :password_hash)";
        
        $statement = $this->db->prepare($sql);
        
        // Bind parameters
        $statement->bindParam(':username', $user_data['username']);
        $statement->bindParam(':email', $user_data['email']);
        $statement->bindParam(':first_name', $user_data['first_name']);
        $statement->bindParam(':last_name', $user_data['last_name']);
        $statement->bindParam(':password_hash', $user_data['password_hash']);
        
        // Execute the statement
        $statement->execute();
    
        return $statement->rowCount(); // Return the number of affected rows
    }


    public function getPassword($username) {
        $sql = "SELECT password_hash FROM users WHERE username = :username;";
        $statement = $this->db->prepare($sql);

        $statement->execute([
            'username' => $username
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getData() {
        $sql = "SELECT * FROM users;";
        $statement = $this->db->prepare($sql);

        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
}