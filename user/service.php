<?php 

require_once '../barbershop/auth.php';

class Service
{
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
       
       $sql = "SELECT * FROM {$this->table} ";

       $services =mysqli_query($this->koneksi, $sql);

        if (mysqli_num_rows($services)) {

            $hasil['success']= true;
            $hasil['message']= 'Data services!';
            $hasil['data'] = mysqli_fetch_all($services, MYSQLI_ASSOC);

            echo json_encode($hasil);

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'No result found!';
            $hasil['data'] = null;
            
            echo json_encode($hasil);
        }
    }

    public function getAllWhereUsaha()
    {
       
        $sql = "SELECT * FROM {$this->table} WHERE id_usaha = $_POST[id_usaha]";

        $services =mysqli_query($this->koneksi, $sql);

        if (mysqli_num_rows($services)) {

            $hasil['success']= true;
            $hasil['message']= 'Data services where id usaha!';
            $hasil['data'] = mysqli_fetch_all($services, MYSQLI_ASSOC);

            echo json_encode($hasil);

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'No result found!';
            $hasil['data'] = null;
            
            echo json_encode($hasil);
        }
    }

    public function getAllWhereOrder()
    {
       
        $sql = "SELECT o.*, t.harga, t.nama_pelayanan, u.nama FROM tb_order as o
            JOIN {$this->table} as t
            ON t.id_pelayanan = o.id_pelayanan
            LEFT OUTER JOIN tb_users as u
            ON u.id_users = o.id_user
            WHERE o.id_usaha = $_POST[id_usaha] 
            AND o.status_order IS NOT NULL 
            AND o.tgl_order = '".date('Y-m-d')."'";

        $services =mysqli_query($this->koneksi, $sql);

        if (mysqli_num_rows($services)) {

            $hasil['success']= true;
            $hasil['message']= 'Data orders where id usaha!';
            $hasil['data'] = mysqli_fetch_all($services, MYSQLI_ASSOC);

            echo json_encode($hasil);

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'No result found!';
            $hasil['data'] = null;
            
            echo json_encode($hasil);
        }
    }


}

$service = new Service();


switch ($_GET['op']) {
    case 'getall':
        echo $service->getAll();
        break;
    case 'getwhereusaha':
        echo $service->getAllWhereUsaha();
        break;
    case 'getwherequeue':
        echo $service->getAllWhereOrder();
        break;
    default: break;
}