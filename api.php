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
    $result = '';

    for($i = 0; $i < $panjang; $i++) {
        $result .= mt_rand(0, 9);
    }

    return $result;
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
    case 'cari_barber': cari_barber();break;
    case 'cari_salon': cari_salon();break;
    case 'salon': salon();break;
    case 'barber': barber();break;
    case 'list-barber': listBarber();break;
    case 'addorder': addOrder();break;
    case 'addvisit': addVisit();break;
    case 'seenoften': seenOften();break;
    case 'savingpackage': savingPackage();break;
    case 'order_last': orderLast();break;
    case 'lastvisited': lastVisited();break;
    case 'activity_order': activityOrder();break;
    case 'history_order': historyOrder();break;
    case 'batal_order': batalOrder();break;
}

function orderLast() {
    global $koneksi;
    $post = $_POST;

    $q1 = "SELECT COUNT(*) as six_month FROM tb_order WHERE id_user = $_POST[id_user] AND tgl_order > DATE_SUB(NOW(), INTERVAL 6 MONTH)";
    $q2 = "SELECT COUNT(*) as one_month FROM tb_order WHERE id_user = $_POST[id_user] AND tgl_order > DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        
    $f_q1 = mysqli_fetch_assoc(mysqli_query($koneksi, $q1));
    $f_q2 = mysqli_fetch_assoc(mysqli_query($koneksi, $q2));

    if ($q1 && $q2) {
        echo json_encode([
            'success' => true,
            'message' => 'Get last order 6 month & 1 month',
            'data' => [
                'sm' => $f_q1,
                'om' => $f_q2,
            ],
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error get last orders',
        ]);        
    }
}

function addVisit() {
    global $koneksi;
    $post = $_POST;

    $q = "INSERT INTO tb_visitor 
        (id_user, id_usaha, visit)
        VALUES
        ($post[id_user], $post[id_usaha], 1)";
        
    $insert = mysqli_query($koneksi, $q);

    if ($insert) {
        echo json_encode([
            'success' => true,
            'message' => 'Pesanan berhasil ditambahkan, mohon menunggu konfirmasi dari pihak penyedia layanan',
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $q,
        ]);        
    }
}

function batalOrder() {
    global $koneksi;
    $post = $_POST;

    $q = "DELETE FROM tb_order
        WHERE id_order = $post[id_order]";
        
    $sql = mysqli_query($koneksi, $q);

    if ($sql) {
        echo json_encode([
            'success' => true,
            'message' => 'Pesanan berhasil batalkan',
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No results found!',
        ]);
    }
}

function addOrder() {
    global $koneksi;
    $post = $_POST;

    $q = "SELECT COUNT(*) as total FROM tb_order
        WHERE id_usaha = $post[id_usaha]
        AND status_order = 'belum selesai'
        AND tgl_order = '".date('Y-m-d')."'";
        
    $sql = mysqli_query($koneksi, $q);

    if ($sql) {
        if (mysqli_fetch_assoc($sql)['total'] >= 10) {
            echo json_encode([
                'success' => false,
                'message' => 'Pesanan sudah penuh, harap menunggu beberapa saat!',
            ]);
        } else {
            $q = "INSERT INTO tb_order 
                (id_user, id_usaha, id_pelayanan, tgl_order )
                VALUES
                ($post[id_user], $post[id_usaha], $post[id_pelayanan], '".date('Y-m-d')."')";

            $insert = mysqli_query($koneksi, $q);

            if ($insert) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pesanan berhasil ditambahkan, mohon menunggu konfirmasi dari pihak penyedia layanan',
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menambahkan pesanan',
                ]);        
            }
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No results found!',
        ]);
    }
}

function lastVisited() {
    global $koneksi;

    $q = "SELECT us.* FROM tb_visitor as v
            JOIN tb_usaha as us
            ON v.id_usaha = us.id_usaha
            WHERE v.id_user = $_POST[id_user]
            ORDER BY id_visitor DESC
            LIMIT 3
            -- GROUP BY v.id_usaha, v.id_user
            ";
    $sql = mysqli_query($koneksi, $q);

    if ($sql) {
        echo json_encode([
            'success' => true,
            'message' => 'Data last visited',
            'data' => mysqli_fetch_all($sql, MYSQLI_ASSOC)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No results found!',
            'data' => []
        ]);
    }
}

function seenOften() {
    global $koneksi;

    $q = "SELECT us.*, SUM(v.visit) AS total FROM tb_visitor as v
            JOIN tb_usaha as us
            ON v.id_usaha = us.id_usaha
            WHERE v.id_user = $_POST[id_user]
            GROUP BY v.id_usaha, v.id_user
            ORDER BY total DESC
            ";
    $sql = mysqli_query($koneksi, $q);

    if ($sql) {
        echo json_encode([
            'success' => true,
            'message' => 'Data seen often',
            'data' => mysqli_fetch_all($sql, MYSQLI_ASSOC)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No results found!',
            'data' => []
        ]);
    }
}

function activityOrder() {
    global $koneksi;

    $q = "SELECT o.id_order, o.status_order, o.no_antri, o.tgl_order, o.estimasi_waktu, u.*, p.harga, p.foto, p.nama_pelayanan, p.deskripsi, u.foto_profil
        FROM tb_order as o
        JOIN tb_usaha as u
        ON u.id_usaha = o.id_usaha
        JOIN tb_pelayanan as p
        ON p.id_usaha = o.id_usaha
        WHERE o.id_user = $_POST[id_user]
        AND status_order != 'selesai'
        OR status_order IS NULL
        GROUP BY o.id_order
    ";

    $sql = mysqli_query($koneksi, $q);

    if ($sql) {
        echo json_encode([
            'success' => true,
            'message' => 'Data activity order',
            'data' => mysqli_fetch_all($sql, MYSQLI_ASSOC)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No results found!',
            'data' => $q
        ]);
    }
}

function historyOrder() {
    global $koneksi;

    $q = "SELECT o.status_order, o.no_antri, o.tgl_order, o.estimasi_waktu, u.*, p.harga, p.foto, p.nama_pelayanan, p.deskripsi, u.foto_profil
        FROM tb_order as o
        JOIN tb_usaha as u
        ON u.id_usaha = o.id_usaha
        JOIN tb_pelayanan as p
        ON p.id_usaha = o.id_usaha
        WHERE o.id_user = $_POST[id_user]
        AND status_order = 'selesai'
    ";

    $sql = mysqli_query($koneksi, $q);

    if ($sql) {
        echo json_encode([
            'success' => true,
            'message' => 'Data history order',
            'data' => mysqli_fetch_all($sql, MYSQLI_ASSOC)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No results found!',
            'data' => $q
        ]);
    }
}

function savingPackage() {
    global $koneksi;

    $q = "SELECT p.*, u.nama_usaha FROM tb_pelayanan as p
        JOIN tb_usaha as u
        ON u.id_usaha = p.id_usaha
        ORDER BY p.harga ASC";

    $sql = mysqli_query($koneksi, $q);

    if ($sql) {
        echo json_encode([
            'success' => true,
            'message' => 'Data saving package',
            'data' => mysqli_fetch_all($sql, MYSQLI_ASSOC)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No results found!',
            'data' => $q
        ]);
    }
}

function listBarber() {
    global $koneksi;

    $q = "SELECT * FROM tb_usaha WHERE verif_data = 1";
    $sql = mysqli_query($koneksi, $q);

    if ($sql) {
        echo json_encode([
            'success' => true,
            'message' => 'Data list barber\'s',
            'data' => mysqli_fetch_all($sql, MYSQLI_ASSOC)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No results found!',
            'data' => []
        ]);
    }
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

function cari_barber(){
    global $koneksi;
    $nama_usaha=  $_POST['nama_usaha'];
    $sql1 = "select * from tb_usaha where nama_usaha like '%$nama_usaha%' AND jenis_usaha='barber' ";
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

function cari_salon(){
    global $koneksi;
    $nama_usaha=  $_POST['nama_usaha'];
    $sql1 = "select * from tb_usaha where nama_usaha like '%$nama_usaha%' AND jenis_usaha='salon' ";
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