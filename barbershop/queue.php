<?php

use PHPMailer\PHPMailer\PHPMailer;
require_once "../PHPMailer/PHPMailer.php";
require_once "../PHPMailer/SMTP.php";
require_once "../PHPMailer/Exception.php";
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
        $post = $_POST;
        if (isset($post['limit'])) {
            $q = "SELECT tb_order.*, tb_pelayanan.nama_pelayanan, tb_pelayanan.harga, tb_users.foto_profil, tb_users.nama FROM {$this->table} LEFT OUTER JOIN tb_users ON tb_order.id_user = tb_users.id_users JOIN tb_pelayanan ON tb_pelayanan.id_pelayanan = tb_order.id_pelayanan WHERE tb_order.status_order IS NULL AND tb_order.id_usaha = '{$_POST['id_usaha']}' AND tb_order.tgl_order = '".date('Y-m-d')."' LIMIT 4";
        } else {
            $q = "SELECT tb_order.*, tb_pelayanan.nama_pelayanan, tb_pelayanan.harga, tb_users.foto_profil, tb_users.nama FROM {$this->table} LEFT OUTER JOIN tb_users ON tb_order.id_user = tb_users.id_users JOIN tb_pelayanan ON tb_pelayanan.id_pelayanan = tb_order.id_pelayanan WHERE tb_order.status_order IS NULL AND tb_order.id_usaha = '{$_POST['id_usaha']}' AND tb_order.tgl_order = '".date('Y-m-d')."'";
        }
        
        $queues = mysqli_query($this->koneksi, $q);

        if (mysqli_num_rows($queues)) {

            $hasil['success']= true;
            $hasil['message']= $q;
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
        $q = "SELECT tb_order.*, tb_pelayanan.nama_pelayanan, tb_pelayanan.harga, tb_users.foto_profil, tb_users.nama FROM {$this->table} LEFT OUTER JOIN tb_users ON tb_order.id_user = tb_users.id_users JOIN tb_pelayanan ON tb_pelayanan.id_pelayanan = tb_order.id_pelayanan WHERE tb_order.status_order = 'belum selesai' AND tb_order.id_usaha = '{$_POST['id_usaha']}' AND tb_order.tgl_order = '".date('Y-m-d')."'";
        $queues = mysqli_query($this->koneksi, $q);

        if ($queues) {

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
        
        $last_queue = mysqli_fetch_assoc(mysqli_query(
            $this->koneksi, 
            "SELECT no_antri FROM {$this->table} 
                WHERE id_usaha = {$_POST['id_usaha']} 
                AND status_order = 'belum selesai'
                AND tgl_order = '".date('Y-m-d')."' 
                ORDER BY no_antri DESC "
            ));
        $last_queue['no_antri'] += 1;

        $pelayanan = mysqli_fetch_assoc(mysqli_query($this->koneksi, "SELECT estimasi_waktu FROM tb_pelayanan WHERE id_pelayanan = $_POST[id_pelayanan]"));

        $sum_estWaktu = mysqli_fetch_assoc(mysqli_query($this->koneksi, "SELECT SUM(estimasi_waktu) as estimasi_waktu FROM {$this->table} WHERE id_usaha = {$_POST['id_usaha']} AND tgl_order = '".date('Y-m-d')."' ORDER BY no_antri DESC "));
        $sum_estWaktu['estimasi_waktu'] = (int) $sum_estWaktu['estimasi_waktu'] + $pelayanan['estimasi_waktu'];

        $q = "INSERT INTO {$this->table} 
            (id_usaha, id_pelayanan, nama_pemesan, tgl_order, no_antri, status_order, estimasi_waktu) 
            VALUE 
            ({$_POST['id_usaha']}, {$_POST['id_pelayanan']}, '{$_POST['nama_pemesan']}', '".date('Y-m-d')."', ".(int) $last_queue['no_antri'] .", 'belum selesai', $sum_estWaktu[estimasi_waktu]) ";
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
            $qq = "UPDATE {$this->table}
                SET
                    estimasi_waktu = 
                        estimasi_waktu - (SELECT estimasi_waktu FROM tb_order WHERE id_order = $_POST[id_order])
                WHERE tgl_order = '".date('Y-m-d')."'";
            // $cek = mysqli_query($this->koneksi, $qq);

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
        $last_queue = mysqli_fetch_assoc(mysqli_query($this->koneksi, "SELECT no_antri FROM {$this->table} WHERE id_usaha = {$_POST['id_usaha']} AND tgl_order = '".date('Y-m-d')."' ORDER BY no_antri DESC "));
        $last_queue['no_antri'] = (int) $last_queue['no_antri'] + 1;

        $pelayanan = mysqli_fetch_assoc(mysqli_query($this->koneksi, "SELECT p.estimasi_waktu FROM {$this->table} as t JOIN tb_pelayanan as p ON p.id_pelayanan = t.id_pelayanan WHERE t.id_order = $_POST[id_order] "));

        $sum_estWaktu = mysqli_fetch_assoc(mysqli_query($this->koneksi, "SELECT SUM(estimasi_waktu) as estimasi_waktu FROM {$this->table} WHERE id_usaha = {$_POST['id_usaha']} AND tgl_order = '".date('Y-m-d')."' ORDER BY no_antri DESC "));
        $sum_estWaktu['estimasi_waktu'] = (int) $sum_estWaktu['estimasi_waktu'] + $pelayanan['estimasi_waktu'];

        $q = "UPDATE {$this->table} 
            SET 
                no_antri = $last_queue[no_antri],
                status_order = 'belum selesai',
                estimasi_waktu = $sum_estWaktu[estimasi_waktu]
            WHERE id_usaha = $_POST[id_usaha] AND id_order = $_POST[id_order] ";
        $cek = mysqli_query($this->koneksi, $q);
        if ($cek) {
            // get email
            $user = mysqli_query($this->koneksi, "
                SELECT u.email FROM {$this->table} as t
                JOIN tb_users as u
                ON u.id_users = t.id_user
                WHERE t.id_usaha = $_POST[id_usaha] AND t.id_order = $_POST[id_order]
            ");

            if ($user) {
                $email = mysqli_fetch_row($user)[0];

                $data = mysqli_fetch_assoc(mysqli_query($this->koneksi, "
                    SELECT u.nama, us.nama_usaha, us.alamat, t.nama_pemesan, p.nama_pelayanan, p.harga, t.tgl_order
                    FROM {$this->table} as t
                    JOIN tb_users as u
                    ON u.id_users = t.id_user
                    JOIN tb_usaha as us
                    ON us.id_usaha = t.id_usaha
                    JOIN tb_pelayanan as p
                    ON p.id_pelayanan = t.id_pelayanan
                    WHERE t.id_usaha = $_POST[id_usaha] AND t.id_order = $_POST[id_order]
                "));

                $contents = file_get_contents('struk_view.php');
                $contents = str_replace([
                    '{{ nama }}',
                    '{{ nama_usaha }}',
                    '{{ alamat }}',
                    '{{ tgl_order }}',
                    '{{ nama_pelayanan }}',
                    '{{ harga }}',
                ], [
                    $data['nama'] ? $data['nama'] : $data['nama_pemesan'],
                    $data['nama_usaha'],
                    $data['alamat'],
                    $data['tgl_order'],
                    $data['nama_pelayanan'],
                    $data['harga'],
                ], $contents);

                [$a, $b] = $this->sendEmail($email, 'Struk Pembayaran', $contents);
                $hasil['email'] = true;
            }
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

    private function sendEmail($email, $subject, $content)
    {
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host     = "ssl://smtp.gmail.com";
        $mail->SMTPAuth = TRUE;
        $mail->Username = "gethaircutapplication@gmail.com";
        $mail->Password = "since2021";
        $mail->Port=465;
        $mail->SMTPSecure= "ssl";
        //Settings email
        $mail->IsHTML(true);
        $mail->SetFrom("gethaircutapplication@gmail.com", "getHaircut Application");
        $mail->AddAddress($email);
        $mail->Subject = $subject;
        $content = $content; 
        $mail->Body= $content;
        
        if($mail->Send()){ 
            return [true, ''];
        }else{ 
            return [false, $mail->ErrorInfo];
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