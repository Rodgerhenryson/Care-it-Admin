<?php
 require_once 'Connection.php';
 $response = array();
 if(isset($_GET['action'])) {
    switch($_GET['action']){
        case 'signup':
        if(isValid(array('email','name','phone','password'))) {
            $email = $_POST['email']; 
            $name = $_POST['name']; 
            $phone = $_POST['phone']; 
            $password = md5($_POST['password']);
            
            $stmt = $conn->prepare("SELECT email FROM customer_details WHERE phone = ?");
            $stmt->bind_param("ss", $email, $phone);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 0) {
                 //if user is new creating an insert
                $stmt = $conn->prepare("INSERT INTO customer_details (email,name,phone,password) VALUES (?, ?, ?,?)");
                $stmt->bind_param("sss", $email, $name, $phone, $password);      
                //if the user is successfully added to the database 
                if($stmt->execute()){
                    $stmt = $conn->prepare("SELECT email,name phone, passsword FROM customer_details WHERE email = ?"); 
                    $stmt->bind_param("s",$email);
                    $stmt->execute();
                    $stmt->bind_result($email, $name, $phone);
                    $stmt->fetch();

                    $user = array(
                        'email'=>$email, 
                        'name'=>$name, 
                        'phone'=>$phone
                    );
                    //adding the user data in response 
                    $response['error'] = false; 
                    $response['message'] = 'User registered successfully.'; 
                    $response['user'] = $user; 
                } else {
                    $response['error'] = true; 
                    $response['message'] = 'Unable to create user.'; 
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'User already registered.';
            }
            $stmt->close();
        } else {
            $response['error'] = true; 
            $response['message'] = 'Incomplete data.';
        }
        break; 
        case 'login':
        if(isValid(array('email', 'password'))){
            //getting values 
            $username = $_POST['email'];
            $password = md5($_POST['password']); 
            
            //creating the check query 
            $stmt = $conn->prepare("SELECT email,name, phone FROM customer_details WHERE email = ? AND password = ?");
            $stmt->bind_param("ss",$email, $password);
            $stmt->execute();
            $stmt->store_result();
            
            //if the user exist with given credentials 
            if($stmt->num_rows > 0) {
                $stmt->bind_result($id, $username, $email);
                $stmt->fetch();
               $user = array(
                        'email'=>$email, 
                        'name'=>$name, 
                        'phone'=>$phone
                );
                $response['error'] = false; 
                $response['message'] = 'Login successfull'; 
                $response['user'] = $user; 
            }else{
                //if the user not found 
                $response['error'] = true; 
                $response['message'] = 'Invalid username or password';
            }
        } else {
            $response['error'] = true; 
            $response['message'] = 'Invalid data.';
        }
        break;
        default;
            $response['error'] = true; 
            $response['message'] = 'Invalid Action.';
        break;
    }
 } else {
    $response['error'] = true; 
    $response['message'] = 'Invalid Request.';
 }
 function isValid($params){
    foreach($params as $param) {
        //if the paramter is not available or empty
        if(isset($_POST[$param])) {
            if(empty($_POST[$param])){
                return false;
            }
        } else {
            return false;
        }
    }
    //return true if every param is available and not empty 
    return true; 
}
echo json_encode($response);
?>