<?php 

require_once 'auth.php';

class Account {
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

    public function update()
    {
        $q = "SELECT * FROM {$this->table} WHERE email = '{$_POST['email']}'";
        $profile = mysqli_query($this->koneksi, $q);

        if (mysqli_num_rows($profile)) {

            $updated = mysqli_query($this->koneksi, "UPDATE {$this->table} SET 
                        npwp_usaha = '{$_POST['npwp_usaha']}',
                        no_hp_outlet = '{$_POST['no_hp_outlet']}',
                        alamat = '{$_POST['alamat']}',
                        nama_pemilik = '{$_POST['nama_pemilik']}'
                        WHERE email = '{$_POST['email']}' 
                    ");

            if ($updated) {
                $q = "SELECT * FROM {$this->table} WHERE email = '{$_POST['email']}'";
                $profile = mysqli_query($this->koneksi, $q);

                $hasil['success']= true;
                $hasil['message']= 'Data has been updated!';
                $hasil['data']= mysqli_fetch_assoc($profile);

                echo json_encode($hasil);
            } else {
                
                $hasil['success']= false;
                $hasil['message']= 'Error update data';
                $hasil['message']= mysqli_error($this->koneksi);

                echo json_encode($hasil);
            }

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'No result found!';
            
            echo json_encode($hasil);
        }
    }

    public function updateImage()
    {
        $auth = new Auth();

        $updated = mysqli_query($this->koneksi, "UPDATE {$this->table} SET 
            foto_profil = '{$auth->base64_to_jpeg($_POST['foto_usaha'], 'usaha')}'
            WHERE email = '{$_POST['email']}' 
        ");

        if ($updated) {
            $hasil['success']= true;
            $hasil['message']= 'Image updated!';

            echo json_encode($hasil);
        } else {
            $hasil['success']= false;
            $hasil['message']= 'Image update failed!';

            echo json_encode($hasil);

        }
    }

    public function get_profile()
    {
        $q = "SELECT * FROM {$this->table} WHERE email = '{$_POST['email']}'";
        $profile = mysqli_query($this->koneksi, $q);

        if (mysqli_num_rows($profile)) {

            $hasil['success']= true;
            $hasil['message']= 'Data account!';
            $hasil['data'] = mysqli_fetch_assoc($profile);

            echo json_encode($hasil);

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'No result found!';
            $hasil['data'] = null;
            
            echo json_encode($hasil);
        }
    }
}

$account = new Account();

switch ($_GET['op']) {
    case 'update':
        $account->update();
        break;
    case 'getprofile':
        $account->get_profile();
        break;
    case 'updateImage':
        $account->updateImage();
        break;
    
    default:
        # code...
        break;
}