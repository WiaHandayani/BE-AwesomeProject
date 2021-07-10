<?php
error_reporting(0); 

use PHPMailer\PHPMailer\PHPMailer;
require_once "../PHPMailer/PHPMailer.php";
require_once "../PHPMailer/SMTP.php";
require_once "../PHPMailer/Exception.php";

class Auth {
    private $koneksi;
    private $host   = "localhost";
    private $user   = "root";
    private $pass   = "";
    private $db     = "db_get_haircut";
    //
    private $table = 'tb_usaha';

    public function __construct()
    {
        $this->koneksi = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
        
    }

    public function sendOtp()
    {
        if ($_POST) {
            $q = "SELECT * FROM {$this->table} WHERE email = '{$_POST['email']}' ";
            $cek = mysqli_query($this->koneksi, $q);
            if ($cek) {
                return $this->sendToMail($_POST['email']);   
            }  else {
                $hasil['success']= false;
                $hasil['message']= 'Email sudah ada!';

                echo json_encode($hasil);
            }
        }
    }
    
    public function verifOtp()
    {
        $q = "SELECT * FROM {$this->table} WHERE kode_otp = '{$_POST['otp']}' AND email = '{$_POST['email']}' ";
        $usaha = mysqli_query($this->koneksi, $q);
        
        if (mysqli_num_rows($usaha)) {
            $hasil['success']= true;
            $hasil['message']= 'Pendaftaran email berhasil!';

            echo json_encode($hasil);
        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'Kode OTP Salah!';

            echo json_encode($hasil);
        }
    }

    public function register()
    {
        // $values = "'".join("', '", $_POST)."'";
        // $keys = "'".join("', '", array_keys($_POST))."'";
        
        $update = mysqli_query($this->koneksi, "UPDATE {$this->table} SET 
            jenis_usaha = '{$_POST['jenis_usaha']}',
            nama_usaha = '{$_POST['nama_usaha']}',
            npwp_usaha = '{$_POST['npwp_usaha']}',
            no_hp_outlet = '{$_POST['no_hp_outlet']}',
            alamat = '{$_POST['alamat']}',
            nama_pemilik = '{$_POST['nama_pemilik']}',
            no_hp_pemilik = '{$_POST['no_hp_pemilik']}',
            no_identitas_pemilik = '{$_POST['no_identitas_pemilik']}',
            foto_ktp = '{$this->base64_to_jpeg($_POST['foto_ktp'], 'ktp')}',
            foto_profil = '{$this->base64_to_jpeg($_POST['foto_profil'], 'usaha')}',
            longLat = '{$_POST['longLat']}',
            password = '{$_POST['password']}',
            verif_data = 1
            WHERE email = '{$_POST['email']}' 
        ");
        if ($update) {

            $hasil['success']= true;
            $hasil['message']='Pendaftaran berhasil!';

            echo json_encode($hasil);
        } else {
            $hasil['success']= false;
            $hasil['message']= 'Pendaftaran gagal';

            echo json_encode($hasil);
        }
    }

    public function login()
    {
        $q = "SELECT * FROM {$this->table} WHERE email = '{$_POST['email']}' AND password = '{$_POST['password']}'";
        $auth = mysqli_query($this->koneksi, $q);
        if (mysqli_num_rows($auth)) {

            $hasil['success']= true;
            $hasil['message']= 'Login berhasil!';
            $hasil['data'] = mysqli_fetch_assoc($auth);

            echo json_encode($hasil);

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'Email atau password salah';
            $hasil['data'] = null;
            
            echo json_encode($hasil);
        }

    }

    /**
     * 
     * 
     */

    public function base64_to_jpeg($base64_string, $output_file) {
		$fileName = 'foto_'.$output_file.'/'.$output_file.'_'.time().'.jpeg';
		// open the output file for writing
		$ifp = fopen( '../'.$fileName, 'w' ); 

		if ($ifp) {
			// split the string on commas
			// $data[ 0 ] == "data:image/png;base64"
			// $data[ 1 ] == <actual base64 string>
			$data = explode( ',', $base64_string );
		
			// we could add validation here with ensuring count( $data ) > 1
			fwrite( $ifp, base64_decode( $data[1] ) );
		
			// clean up the file resource
			fclose( $ifp ); 

			return $fileName; 
		}

		return $fileName;
	
	}

    public function random($panjang){
        $result = '';
    
        for($i = 0; $i < $panjang; $i++) {
            $result .= mt_rand(0, 9);
        }
    
        return $result;
    }

    private function sendToMail($email){
    
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host     = "ssl://smtp.gmail.com";
        $mail->SMTPAuth = TRUE;
        $mail->Username = "gethaircutapplication@gmail.com";
        $mail->Password = "since2021";
        $mail->Port=465;
        $mail->SMTPSecure= "ssl";
        $otp = $this->random(6); 
        //Settings email
        $mail->IsHTML(true);
        $mail->SetFrom("gethaircutapplication@gmail.com", "getHaircut Application");
        $mail->AddAddress($email);
        $mail->Subject = "Kode OTP";
        $content = "Berikut adalah kode verifikasi untuk melakukan pendaftaran anda. Mohon tidak menyebarkan kode ke orang lain atau pihak getHaircut, demi keamanan akun. <br> <p style=\"padding: .25rem\"><b>$otp</b></p>"; 
        $mail->Body= $content;

        $hasil = [];
        
        if($mail->Send()){ 
            $insert_query = "INSERT INTO {$this->table} (email, password, kode_otp) VALUES (
                '$email', '', '$otp'
            )";

            $insert = mysqli_query($this->koneksi, $insert_query);
            if ($insert) {
                $hasil['success']= true;
                $hasil['message']='OTP berhasil dikirim. Silahkan cek e-mail anda!';

                echo json_encode($hasil);
            } else {
                $hasil['success']= false;
                $hasil['message']='Gagal insert';

                echo json_encode($hasil);
            }

        }else{ 
            
            $hasil['success']= false;
            $hasil['message']='Gagal kirim email';
            $hasil['data'] = $mail->ErrorInfo;

            echo json_encode($hasil);
        }
    }
}

$auth = new Auth();

switch ($_GET['op']) {
    case 'sendotp':
        $auth->sendOtp();
        break;
    case 'verifotp':
        $auth->verifOtp();
        break;
    case 'register':
        $auth->register();
        break;
    case 'login':
        $auth->login();
        break;
    
    default:
        # code...
        break;
}