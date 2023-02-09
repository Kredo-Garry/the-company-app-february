<?php
    include '../classess/User.php';

    # Instantiate object
    $user = new User;


    # Call the method store in User class
    $user->store($_POST);
?>