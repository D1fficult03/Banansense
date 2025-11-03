<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization");

include 'config.php';

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->username) &&
    !empty($data->email) &&
    !empty($data->password)
){
    $username = $data->username;
    $email = $data->email;
    $password = password_hash($data->password, PASSWORD_DEFAULT);

    // Check if user exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if($check_stmt->num_rows > 0){
        http_response_code(400);
        echo json_encode(array("error" => "Пользователь уже существует"));
        exit;
    }

    // Create user
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if($stmt->execute()){
        $user_id = $stmt->insert_id;
        
        http_response_code(201);
        echo json_encode(array(
            "message" => "Регистрация успешна",
            "user" => array(
                "id" => $user_id,
                "username" => $username,
                "email" => $email,
                "downloads" => 0,
                "isPremium" => false
            )
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("error" => "Ошибка сервера"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("error" => "Все поля обязательны"));
}
?>