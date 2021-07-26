<?php 

require_once 'auth.php';

class Service {
    private $koneksi;
    private $host   = "localhost";
    private $user   = "root";
    private $pass   = "";
    private $db     = "db_get_haircut";
    //
    private $table = 'tb_pelayanan';

    public function __construct()
    {
        $this->koneksi = mysqli_connect($this->host, $this->user, $this->pass, $this->db);    
    }

    public function getAll()
    {
        $q = "SELECT * FROM {$this->table} WHERE id_usaha = {$_POST['id_usaha']}";
        $services = mysqli_query($this->koneksi, $q);

        if (mysqli_num_rows($services)) {

            $hasil['success']= true;
            $hasil['message']= 'Data services!';
            $hasil['data'] = mysqli_fetch_all($services, MYSQLI_ASSOC);

            echo json_encode($hasil);

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'No result found!';
            $hasil['data'] = $q;
            
            echo json_encode($hasil);
        }
    }
    
    public function addNew()
    {
        $auth = new Auth();
        $q = "INSERT INTO {$this->table} (id_usaha, nama_pelayanan, harga, deskripsi, foto, estimasi_waktu) VALUE ({$_POST['id_usaha']}, '{$_POST['nama_pelayanan']}', {$_POST['harga']}, '{$_POST['deskripsi']}', '{$auth->base64_to_jpeg($_POST['foto'], 'pelayanan')}', {$_POST['estimasi_waktu']}) ";
        $cek = mysqli_query($this->koneksi, $q);
        if ($cek) {
            $hasil['success']= true;
            $hasil['message']= 'Data pelayanan baru berhasil di simpan';

            echo json_encode($hasil);
        }  else {
            $hasil['success']= false;
            $hasil['message']= 'Data pelayanan baru gagal di simpan';

            echo json_encode($hasil);
        }   
    }

    public function delete()
    {
        $q = "SELECT * FROM {$this->table} WHERE id_pelayanan = '{$_POST['id_pelayanan']}'";

        @unlink('../'.mysqli_fetch_assoc(mysqli_query($this->koneksi, $q))['foto']);

        $q = "DELETE FROM {$this->table} WHERE id_pelayanan = {$_POST['id_pelayanan']} ";
        $cek = mysqli_query($this->koneksi, $q);
        if ($cek) {
            $hasil['success']= true;
            $hasil['message']= 'Data pelayanan baru berhasil di hapus';

            echo json_encode($hasil);
        }  else {
            $hasil['success']= false;
            $hasil['message']= 'Data pelayanan baru gagal di hapus';


            echo json_encode($hasil);
        }   
    }

    public function update()
    {
        $auth = new Auth();
        $q = "UPDATE {$this->table} SET 
            nama_pelayanan = '{$_POST['nama_pelayanan']}',
            harga = {$_POST['harga']},
            estimasi_waktu = {$_POST['estimasi_waktu']},
            deskripsi = '{$_POST['deskripsi']}'";
        if ($_POST['changeImage'] !== "false") {
            $q .= ", foto = '{$auth->base64_to_jpeg($_POST['foto'], 'pelayanan')}'";
        }
            $q .= "WHERE id_pelayanan = {$_POST['id_pelayanan']} ";
        $cek = mysqli_query($this->koneksi, $q);
        if ($cek) {
            $hasil['success']= true;
            $hasil['message']= 'Data pelayanan baru berhasil di ubah';

            echo json_encode($hasil);
        }  else {
            $hasil['success']= false;
            $hasil['message']= 'Data pelayanan baru gagal di ubah';


            echo json_encode($hasil);
        }   
    }
}

$service = new Service();

switch ($_GET['op']) {
    case 'addnew':
        $service->addNew();
        break;
    case 'getall':
        $service->getAll();
        break;
    case 'update':
        $service->update();
        break;
    case 'delete':
        $service->delete();
        break;
    
    default:
        # code...
        break;
}