<?php
    include '../classess/User.php';

    # Instantiate an object
    $user = new User;

    #call the method login
    $user->login($_POST);

?>