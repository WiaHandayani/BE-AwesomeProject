<?php

require_once 'auth.php';

class Queue {
    private $koneksi;
    private $host   = "localhost";
    private $user   = "root";
    private $pass   = "";
    private $db     = "db_get_haircut";
    //
    private $table = 'tb_order';

    public function __construct()
    {
        $this->koneksi = mysqli_connect($this->host, $this->user, $this->pass, $this->db);    
    }

    public function getWaiting()
    {
        $q = "SELECT tb_order.*, tb_pelayanan.nama_pelayanan, tb_pelayanan.harga, tb_users.foto_profil, tb_users.nama FROM {$this->table} LEFT OUTER JOIN tb_users ON tb_order.id_user = tb_users.id_users JOIN tb_pelayanan ON tb_pelayanan.id_pelayanan = tb_order.id_pelayanan WHERE tb_order.status_order IS NULL AND tb_order.id_usaha = '{$_POST['id_usaha']}'";
        $queues = mysqli_query($this->koneksi, $q);

        if (mysqli_num_rows($queues)) {

            $hasil['success']= true;
            $hasil['message']= 'Data order menunggu konfirmasi!';
            $hasil['data'] = mysqli_fetch_all($queues, MYSQLI_ASSOC);

            echo json_encode($hasil);

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'No result found!';
            // $hasil['message']= $q;
            $hasil['data'] = null;
            
            echo json_encode($hasil);
        }
    }

    public function getAccept()
    {
        $q = "SELECT tb_order.*, tb_pelayanan.nama_pelayanan, tb_pelayanan.harga, tb_users.foto_profil, tb_users.nama FROM {$this->table} LEFT OUTER JOIN tb_users ON tb_order.id_user = tb_users.id_users JOIN tb_pelayanan ON tb_pelayanan.id_pelayanan = tb_order.id_pelayanan WHERE tb_order.status_order = 'belum selesai' AND tb_order.id_usaha = '{$_POST['id_usaha']}'";
        $queues = mysqli_query($this->koneksi, $q);

        if (mysqli_num_rows($queues)) {

            $hasil['success']= true;
            $hasil['message']= 'Data order antrian!';
            $hasil['data'] = mysqli_fetch_all($queues, MYSQLI_ASSOC);

            echo json_encode($hasil);

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'No result found!';
            // $hasil['message']= mysqli_error($this->koneksi);
            $hasil['data'] = $q;
            
            echo json_encode($hasil);
        }
    }

    public function getHistory()
    {
        $q = "SELECT tb_order.*, DATE_FORMAT(tb_order.tgl_order, '%d/%M') as tgl, tb_pelayanan.nama_pelayanan, tb_pelayanan.harga, tb_users.foto_profil, tb_users.nama FROM {$this->table} LEFT OUTER JOIN tb_users ON tb_order.id_user = tb_users.id_users JOIN tb_pelayanan ON tb_pelayanan.id_pelayanan = tb_order.id_pelayanan WHERE tb_order.status_order = 'selesai' AND tb_order.id_usaha = '{$_POST['id_usaha']}'";
        $queues = mysqli_query($this->koneksi, $q);

        if (mysqli_num_rows($queues)) {

            $hasil['success']= true;
            $hasil['message']= 'Data history order!';
            $hasil['data'] = mysqli_fetch_all($queues, MYSQLI_ASSOC);

            echo json_encode($hasil);

        } else {
            
            $hasil['success']= false;
            $hasil['message']= 'No result found!';
            // $hasil['message']= mysqli_error($this->koneksi);
            $hasil['data'] = $q;
            
            echo json_encode($hasil);
        }
    }

    public function addNewQueue()
    {
        $auth = new Auth();
        $lats_queue = mysqli_fetch_assoc(mysqli_query($this->koneksi, "SELECT no_antri FROM {$this->table} WHERE id_usaha = {$_POST['id_usaha']} ORDER BY no_antri DESC "));
        $lats_queue['no_antri'] += 1;

        $q = "INSERT INTO {$this->table} 
            (id_usaha, id_pelayanan, nama_pemesan, tgl_order, no_antri, status_order) 
            VALUE 
            ({$_POST['id_usaha']}, {$_POST['id_pelayanan']}, '{$_POST['nama_pemesan']}', '".date('Y-m-d')."', ".(int) $lats_queue['no_antri'] .", 'belum selesai') ";
        $cek = mysqli_query($this->koneksi, $q);
        if ($cek) {
            $hasil['success']= true;
            $hasil['message']= 'Data antrian berhasil di simpan';

            echo json_encode($hasil);
        }  else {
            $hasil['success']= false;
            $hasil['message']= 'Data antrian gagal di simpan';
            // $hasil['message']= mysqli_error($this->koneksi);

            echo json_encode($hasil);
        }   
    }

    public function finishOrder()
    {

        $q = "UPDATE {$this->table} 
            SET 
                status_order = 'selesai'
            WHERE id_usaha = $_POST[id_usaha] AND id_order = $_POST[id_order] ";
        $cek = mysqli_query($this->koneksi, $q);
        if ($cek) {
            $hasil['success']= true;
            $hasil['message']= 'Order berhasil di selesaikan';

            echo json_encode($hasil);
        }  else {
            $hasil['success']= false;
            $hasil['message']= 'Order gagal  di selesaikan';
            // $hasil['message']= mysqli_error($this->koneksi);

            echo json_encode($hasil);
        }     
    }

    public function confirmOrder()
    {
        $lats_queue = mysqli_fetch_assoc(mysqli_query($this->koneksi, "SELECT no_antri FROM {$this->table} WHERE id_usaha = {$_POST['id_usaha']} ORDER BY no_antri DESC "));
        $lats_queue['no_antri'] = (int) $lats_queue['no_antri'] + 1;

        $q = "UPDATE {$this->table} 
            SET 
                no_antri = $lats_queue[no_antri],
                status_order = 'belum selesai'
            WHERE id_usaha = $_POST[id_usaha] AND id_order = $_POST[id_order] ";
        $cek = mysqli_query($this->koneksi, $q);
        if ($cek) {
            $hasil['success']= true;
            $hasil['message']= 'Order berhasil di konfirmasi';

            echo json_encode($hasil);
        }  else {
            $hasil['success']= false;
            $hasil['message']= 'Order gagal  di konfirmasi';
            // $hasil['message']= mysqli_error($this->koneksi);

            echo json_encode($hasil);
        }     
    }

    public function rejectOrder()
    {

        $q = "UPDATE {$this->table} 
            SET 
                status_order = 'ditolak'
            WHERE id_usaha = $_POST[id_usaha] AND id_order = $_POST[id_order] ";
        $cek = mysqli_query($this->koneksi, $q);
        if ($cek) {
            $hasil['success']= true;
            $hasil['message']= 'Order berhasil di tolak';

            echo json_encode($hasil);
        }  else {
            $hasil['success']= false;
            $hasil['message']= 'Order gagal  di tolak';
            // $hasil['message']= mysqli_error($this->koneksi);

            echo json_encode($hasil);
        }     
    }
}

$queue = new Queue();

switch ($_GET['op']) {
    case 'addnew':
        $queue->addNewQueue();
        break;
    case 'getwaiting':
        $queue->getWaiting();
        break;
    case 'getqueue':
        $queue->getAccept();
        break;
    case 'reject':
        $queue->rejectOrder();
        break;
    case 'confirm':
        $queue->confirmOrder();
        break;
    case 'history':
        $queue->getHistory();
        break;
    case 'finish':
        $queue->finishOrder();
        break;
    
    default:
        # code...
        break;
}