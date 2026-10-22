<?php
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json; charset=utf-8');
if (!is_admin()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Admin access required.']); exit; }
$method=$_SERVER['REQUEST_METHOD'];
if ($method==='GET') {
    $res=$conn->query("SELECT id,name,email,course FROM users WHERE role='student' ORDER BY id DESC LIMIT 100"); $data=[];
    while($row=$res->fetch_assoc()) $data[]=$row;
    echo json_encode(['success'=>true,'data'=>$data]); exit;
}
if ($method==='POST') {
    $payload=json_decode(file_get_contents('php://input'),true);
    $name=trim($payload['name']??''); $email=trim($payload['email']??''); $course=trim($payload['course']??'');
    if(strlen($name)<2 || !filter_var($email,FILTER_VALIDATE_EMAIL)){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Enter a valid name and email.']);exit;}
    $username='api_'.bin2hex(random_bytes(4)); $hash=password_hash('Student@123',PASSWORD_DEFAULT); $role='student';
    try{$stmt=$conn->prepare('INSERT INTO users(name,email,username,password_hash,role,course) VALUES(?,?,?,?,?,?)');$stmt->bind_param('ssssss',$name,$email,$username,$hash,$role,$course);$stmt->execute();audit_log($conn,(int)$_SESSION['user_id'],'api_student_added',$username);echo json_encode(['success'=>true,'id'=>$stmt->insert_id]);$stmt->close();}
    catch(Throwable $e){http_response_code(409);echo json_encode(['success'=>false,'message'=>'Could not add record; email may already exist.']);} exit;
}
if ($method==='DELETE') {
    $id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
    if(!$id){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Valid student id required.']);exit;}
    $stmt=$conn->prepare("DELETE FROM users WHERE id=? AND role='student'");$stmt->bind_param('i',$id);$stmt->execute();$deleted=$stmt->affected_rows;$stmt->close();
    audit_log($conn,(int)$_SESSION['user_id'],'api_student_deleted','User id '.$id);
    echo json_encode(['success'=>(bool)$deleted,'message'=>$deleted?'Deleted':'Student not found.']);exit;
}
http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']);
