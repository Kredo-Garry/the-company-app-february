<?php
    require_once 'Database.php';

    class User extends Database{

        public function store($request){
            $first_name = $request['firstname'];
            $last_name = $request['lastname'];
            $username = $request['username'];
            $user_password = $request['password'];

            $password = password_hash($user_password, PASSWORD_DEFAULT);

            # Insert the data to database
            $sql = "INSERT INTO users(`first_name`, `last_name`, `username`, `password`) VALUES('$first_name', '$last_name', '$username', '$password')";

            if ($this->conn->query($sql)) {
                header('location: ../views'); // index.php
                exit(); // same as die
            }else {
                die("Error in inserting data " . $this->conn->error);
            }
        }

        public function login($request){
            $username = $request['username'];
            $password = $request['password'];

            $sql = "SELECT * FROM users WHERE username='$username'";
            $result = $this->conn->query($sql);
                #check for the username if available
            if ($result->num_rows == 1) { //boolean true or false
                #check if the password matched
                $user = $result->fetch_assoc();
                //$user = ['id' => 1, 'first_name' => 'john', 'last_name' => 'Doe, 'username' => 'john.doe', 'password' => '2y$10$MITUSNtTR5'];

                if (password_verify($password, $user['password'])) {
                    # do this only if the password is verified, or if they are equal
                    session_start();
                    $_SESSION['id'] = $user['id']; // id of the logged-in user
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['fullname'] = $user['first_name'] . " " . $user['last_name'];

                    header('location: ../views/dashboard.php');
                    exit;
                }else {
                    die("Password is incorrect.");
                }
            }else {
                die("Username not found.");
            }
            
        }

        public function logout(){
            session_start();
            session_unset();
            session_destroy();

            header('location: ../views'); //index.php
            exit;
        }

        public function getAllUsers(){
            $sql = "SELECT id, first_name, last_name, username, photo FROM users";
            if ($result = $this->conn->query($sql)) {
                return $result;
            }else {
                die("Error in retrieving users. " . $this->conn->error);
            }
        }

        public function getUser(){
            // session_start();
            $id = $_SESSION['id'];

            $sql = "SELECT first_name, last_name, username, photo FROM users WHERE id='$id'";

            if ($result = $this->conn->query($sql)) {
                return $result->fetch_assoc();
            }else {
                die("Error in retrieving users: " . $this->conn->error);
            }
        }

        public function update($request, $files){
            // session_start();
            $id = $_SESSION['id'];
            $first_name = $request['first_name'];
            $last_name = $request['last_name'];
            $username = $request['username'];

            $photo = $files['photo']['name'];
            $tmp_photo = $files['photo']['tmp_name'];

            $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', username = '$username' WHERE id='$id'";

            if ($this->conn->query($sql)) { // if true, no error
                $_SESSION['username'] = $username;
                $_SESSION['fullname'] = "$first_name $last_name";

                # If there is a photo uploaded, save it to the db (filename) and save the image to the images folder
                if ($photo) {
                    $sql = "UPDATE users SET photo = '$photo' WHERE id='$id'";
                    $destination = "../assets/images/$photo";

                    # Save the image
                    if ($this->conn->query($sql)) {
                        # Move the image to the folder
                        if (move_uploaded_file($tmp_photo, $destination)) {
                            header("location: ../views/dashboard.php");
                            exit;
                        }else {
                            die("Error moving the file to the destination.");
                        }
                    }else {
                        die("Error uploading photo: " . $this->conn->error);
                    }
                }
                header("location: ../views/dashboard.php");
                exit;
            }else {
                die("Error in updating the user. " . $this->conn->error);
            }
        }

        public function delete(){
            # it needs the ID of the user to delete
            session_start();
            $id = $_SESSION['id'];

            $sql = "DELETE FROM users WHERE id='$id'";

            if ($this->conn->query($sql)) {
                $this->logout();
            }else {
                die("Error deleting your account: " . $this->conn->error);
            }
        }
    }

?>