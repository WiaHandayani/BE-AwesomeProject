<?php
error_reporting(0); 
use PHPMailer\PHPMailer\PHPMailer;
require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";

$host   = "localhost";
$user   = "root";
$pass   = "";
$db     = "db_get_haircut";

function random($panjang){
    $karakter = '1234567890';
    $string = '';
    for($i = 0; $i < $panjang; $i++){
        $pos = rand(0, strlen($karakter)-1);
        $string .= $karakter{$pos};
    }
    return $string;
 }

$koneksi = mysqli_connect($host, $user, $pass, $db);

$op= $_GET['op'];

switch ($op) {
    case '': normal();break;
    default: normal();break;
    case 'create': create();break;
    case 'login' : login();break;
    case 'detail': detail();break;
    case 'update': update();break;
    case 'delete': delete();break;
    case 'cek_otp': cek_otp();break;
    case 'registrasi': registrasi();break;
    case 'update_pw': update_pw();break;
    case 'input_email': input_email();break;
    case 'input_otp': input_otp();break;
    case 'ganti_pw': ganti_pw();break;
    case 'cari_salonbarber': cari_salonbarber();break;
    case 'cari': cari();break;
    case 'salon': salon();break;
    case 'barber': barber();break;
}
function create(){
    global $koneksi;
    $email = $_POST['email'];
    $query="select * from tb_users where email = '$email'";
    $email_db = mysqli_query($koneksi, $query);
    if ($email_db->num_rows!=0) {
        $hasil[]='E-mail sudah terdaftar!';
    }else{
        

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host     = "smtp.gmail.com";
        $mail->SMTPAuth = TRUE;
        $mail->Username = "gethaircutapplication@gmail.com";
        $mail->Password = "since2021";
        $mail->Port=465;
        $mail->SMTPSecure= "ssl";
        $otp = random(6); 
        //Settings email
        $mail->IsHTML(true);
        $mail->SetFrom("gethaircutapplication@gmail.com", "getHaircut Application");
        $mail->AddAddress($email);
        $mail->Subject = "Kode OTP";
        $content = "Berikut adalah kode verifikasi untuk registrasi.Mohon tidak menyebarkan kode ke orang lain atau pihak getHaircut, demi keamanan akun. <br> $otp"; 
        $mail->Body= $content;

        if($mail->Send()){   
            $sql1 = "insert into tb_users (email, kode_otp) values('$email','$otp')";
            $q1 = mysqli_query($koneksi, $sql1);
            $hasil[]=$email;
            $hasil[]='Silahkan cek e-mail anda!';

        }else{ 
            $hasil='E-mail tidak terdaftar!';
        }
    }
    echo json_encode($hasil);
}

function login(){
    global $koneksi;
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql1 = "select * from tb_users where email = '$email' and password = '$password'";
    $q1 = mysqli_query($koneksi, $sql1);
    foreach($q1 as $user){
        $nama=$user['nama'];
        $id_user=$user['id_users'];
        $email=$user['email'];
        $no_hp=$user['no_hp'];
        $foto_profil=$user['foto_profil'];
        $alamat=$user['alamat_user'];
        $tgl_lahir=$user['tgl_lahir'];
    }
    if ($q1->num_rows==0) {
        $hasil[]="Username atau Password salah";
    }else{
        $hasil[]="Login Berhasil";
        $hasil[]=$nama;
        $hasil[]=$id_user;
        $hasil[]=$email;
        $hasil[]=$no_hp;
        $hasil[]=$foto_profil;
        $hasil[]=$alamat;
        $hasil[]=$tgl_lahir;
    }
    echo json_encode($hasil);
}

function cek_otp(){
    global $koneksi;
    $otp = $_POST['otp'];
    $email = $_POST['email'];
    $sql1 = "select * from tb_users where kode_otp = '$otp' and email = '$email'";
    $q1 = mysqli_query($koneksi, $sql1);
    if ($q1->num_rows==0) {
        $hasil[]="Kode OTP anda salah";
    }else{
        $hasil[]="Registrasi Berhasil";
        $hasil[]=$email;
    }
    echo json_encode($hasil);
}

function registrasi(){
    global $koneksi;
    $nama = $_POST['nama'];
    $password = $_POST['password'];
    $no_hp = $_POST['no_hp'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $tgl_lahir = $_POST['tgl_lahir'];
    if ($nama) {
        $set[] = "nama='$nama'";
    }
    if ($no_hp) {
        $set[] = "no_hp='$no_hp'";
    }
    if ($password) {
        $set[] = "password='$password'";
    }
    if ($alamat) {
        $set[] = "alamat_user='$alamat'";
    }
    if ($tgl_lahir) {
        $set[] = "tgl_lahir='$tgl_lahir'";
    }
    
    if ($nama or $no_hp or $password or $alamat or $tgl_lahir) {
        $sql1 = "update tb_users set ".implode(",",$set)." where email = '$email'";
        $q1 = mysqli_query($koneksi, $sql1);
        if ($q1) {
            $hasil = "Registrasi berhasil";
        }else{
            $hasil= "Gagal melakukan registrasi";
        }
    }
    echo json_encode($hasil);
}

function normal(){
    global $koneksi;
    $sql1 = "select * from tb_users order by id_users desc";
    $q1 = mysqli_query($koneksi, $sql1);
    while ($r1 = mysqli_fetch_array($q1)) {
        $hasil[]= array(
            'id_users' => $r1['id_users'],
            'nama' => $r1['nama'],
            'email' => $r1['email'],
            'password' => $r1['password']  
        );
    }
    $data['data']['result'] = $hasil;
    echo json_encode($data); 
}



function detail(){
    global $koneksi;
    $id = $_GET['id'];
    $sql1 = "select * from tb_users where id_users = '$id'";
    $q1 = mysqli_query($koneksi, $sql1);
    while ($r1 = mysqli_fetch_array($q1)) {
        $hasil[]= array(
            'id_users' => $r1['id_users'],
            'nama' => $r1['nama'],
            'email' => $r1['email'],
            'password' => $r1['password']  
        );
    }
    $data['data']['result'] = $hasil;
    echo json_encode($data); 
}

function update(){
    global $koneksi;
    $id = $_POST['id_user'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $foto_lama = $_POST['foto_lama'];
    $foto_profil = $_FILES['file_attachment']['name'];
    
    if ($nama) {
        $set[] = "nama='$nama'";
    }
    if ($email) {
        $set[] = "email='$email'";
    }
    if ($no_hp) {
        $set[] = "no_hp='$no_hp'";
    }
    if ($foto_profil) {
        $set[] = "foto_profil='$foto_profil'";
    }
    if ($alamat) {
        $set[] = "alamat_user='$alamat'";
    }
    if ($tgl_lahir) {
        $set[] = "tgl_lahir='$tgl_lahir'";
    }
    
    if ($nama or $email or $no_hp or $foto_profil or $alamat or $tgl_lahir) {
        if(!empty($_FILES['file_attachment']['name']))
  {
    $target_dir = "uploads/";
    if (!file_exists($target_dir))
    {
      mkdir($target_dir, 0777);
    }
    $target_file =
      $target_dir . basename($_FILES["file_attachment"]["name"]);
    $imageFileType = 
      strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    // Check if file already exists
    if (file_exists($target_file)) {
        console.log("Sorry, file already exists.");
    }
    // Check file size
    if ($_FILES["file_attachment"]["size"] > 50000000) {
      console.log("Sorry, your file is too large.");
    }
    if (
      move_uploaded_file(
        $_FILES["file_attachment"]["tmp_name"], $target_file
      )
    ) {
      console.log("The file " . 
      basename( $_FILES["file_attachment"]["name"]) .
      " has been uploaded.");
    } else {
      console.log("Sorry, there was an error uploading your file.");
    }
  }
        $sql1 = "update tb_users set ".implode(",",$set)." where id_users = '$id'";
        $q1 = mysqli_query($koneksi, $sql1);
        if ($q1) {
            $hasil[] = "Data berhasil diupdate";
            $hasil[] = $nama;
            $hasil[] = $email;
            $hasil[] = $no_hp;
            if($foto_profil){
                $hasil[] = $foto_profil;
            }else{
                $hasil[] = $foto_lama;
            } 
            $hasil[] = $alamat;
            $hasil[] = $tgl_lahir;
        }else{
            $hasil[]= "Gagal melakukan update data";
        }
    }
    echo json_encode($hasil); 
}

function update_pw(){
    global $koneksi;
    $id = $_POST['id_user'];
    $password = $_POST['password'];
    
    if ($password) {
        $set[] = "password='$password'";
    }
    
    if ($password) {
       
        $sql1 = "update tb_users set ".implode(",",$set)." where id_users = '$id'";
        $q1 = mysqli_query($koneksi, $sql1);
        if ($q1) {
            $hasil = "Data berhasil diupdate";
        }else{
            $hasil= "Gagal melakukan update data";
        }
    }
    echo json_encode($hasil); 
}



function delete(){
    global $koneksi;
    $id = $_GET['id'];
    $sql1 = "delete from tb_users where id_users = '$id'";
    $q1 = mysqli_query($koneksi, $sql1);
    if ($q1) {
        $hasil = "Berhasil menghapus data";
    }else{
        $hasil = "Gagal menghapus data";
    }
    $data['data']['result'] = $hasil;
    echo json_encode($data); 
}

function input_email(){
    global $koneksi;
    $email = $_POST['email'];
    
        

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host     = "smtp.gmail.com";
        $mail->SMTPAuth = TRUE;
        $mail->Username = "gethaircutapplication@gmail.com";
        $mail->Password = "since2021";
        $mail->Port=465;
        $mail->SMTPSecure= "ssl";
        $otp = random(6); 
        //Settings email
        $mail->IsHTML(true);
        $mail->SetFrom("gethaircutapplication@gmail.com", "getHaircut Application");
        $mail->AddAddress($email);
        $mail->Subject = "Kode OTP";
        $content = "Berikut adalah kode verifikasi untuk mengganti kata sandi anda.Mohon tidak menyebarkan kode ke orang lain atau pihak getHaircut, demi keamanan akun. <br> $otp"; 
        $mail->Body= $content;

        if($mail->Send()){ 
            $sql1 = "update tb_users set kode_otp='$otp' where email = '$email'";
            $q1 = mysqli_query($koneksi, $sql1);
            $hasil[]=$email;
            $hasil[]='Silahkan cek e-mail anda!';

        }else{ 
            $hasil='E-mail tidak terdaftar!';
        }
    echo json_encode($hasil);
}

function input_otp(){
    global $koneksi;
    $otp = $_POST['otp'];
    $email = $_POST['email'];
    $sql1 = "select * from tb_users where kode_otp = '$otp' and email = '$email'";
    $q1 = mysqli_query($koneksi, $sql1);
    if ($q1->num_rows==0) {
        $hasil[]="Kode OTP anda salah";
    }else{
        $hasil[]="Input OTP berhasil";
        $hasil[]=$email;
    }
    echo json_encode($hasil);
}

function ganti_pw(){
    global $koneksi;
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if ($password) {
        $set[] = "password='$password'";
    }
    
    if ($password) {
       
        $sql1 = "update tb_users set ".implode(",",$set)." where email = '$email'";
        $q1 = mysqli_query($koneksi, $sql1);
        if ($q1) {
            $hasil = "Kata sandi berhasil diupdate";
        }else{
            $hasil= "Gagal melakukan update kata sandi";
        }
    }
    echo json_encode($hasil); 
}

function cari_salonbarber(){
    global $koneksi;
    $sql1 = "select * from tb_usaha order by id_usaha desc";
    $q1 = mysqli_query($koneksi, $sql1);
    while ($r1 = mysqli_fetch_array($q1)) {
        $hasil[]= array(
            'id_usaha' => $r1['id_usaha'],
            'nama_usaha' => $r1['nama_usaha'],
            'alamat' => $r1['alamat'],
            'foto_profil' => $r1['foto_profil']  
        );
    }
    $data= $hasil;
    echo json_encode($data); 
}

function cari(){
    global $koneksi;
    $nama_usaha=  $_POST['nama_usaha'];
    $sql1 = "select * from tb_usaha where nama_usaha like '%$nama_usaha%'";
    $q1 = mysqli_query($koneksi, $sql1);
    while ($r1 = mysqli_fetch_array($q1)) {
        $hasil[]= array(
            'id_usaha' => $r1['id_usaha'],
            'nama_usaha' => $r1['nama_usaha'],
            'alamat' => $r1['alamat'],
            'foto_profil' => $r1['foto_profil']  
        );
    }
    $data= $hasil;
    echo json_encode($data); 
}

function barber(){
    global $koneksi;
    $sql1 = "select * from tb_usaha where jenis_usaha = 'barber'";
    $q1 = mysqli_query($koneksi, $sql1);
    while ($r1 = mysqli_fetch_array($q1)) {
        $hasil[]= array(
            'id_usaha' => $r1['id_usaha'],
            'nama_usaha' => $r1['nama_usaha'],
            'alamat' => $r1['alamat'],
            'foto_profil' => $r1['foto_profil']  
        );
    }
    $data= $hasil;
    echo json_encode($data); 
}

function salon(){
    global $koneksi;
    $sql1 = "select * from tb_usaha where jenis_usaha = 'salon'";
    $q1 = mysqli_query($koneksi, $sql1);
    while ($r1 = mysqli_fetch_array($q1)) {
        $hasil[]= array(
            'id_usaha' => $r1['id_usaha'],
            'nama_usaha' => $r1['nama_usaha'],
            'alamat' => $r1['alamat'],
            'foto_profil' => $r1['foto_profil']  
        );
    }
    $data= $hasil;
    echo json_encode($data); 
}
?>