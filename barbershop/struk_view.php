<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran</title>
</head>
<link rel="stylesheet" href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css">
<body>
<div class="flex justify-center">
    <div class=" lg:w-4/12 max-h-screen bg-white shadow-lg p-4">

        <div class="text-center">
            <div class="text-lg font-semibold text-gray-900">{{ nama_usaha }} </div>
            <div class=" mb-3 text-gray-700">{{ alamat }} </div>
        </div>

        <div class="text-center  text-gray-700">
            --------------------------------------------------------------------------------
        </div>

        <div style="text-transform: uppercase;" class=" text-center text-base text-gray-800 font-medium my-2">
            Struk Pembayaran
        </div>

        <table class="mx-3 text-sm text-gray-800 font-medium">
            <tr>
                <td>Tanggal Order</td>
                <td class="px-4">: </td>
                <td>{{ tgl_order }} </td>
            </tr>
            <tr>
                <td>Nama Customer</td>
                <td class="px-4">: </td>
                <td>{{ nama }}</td>
            </tr>
            <tr>
                <td>Nama Pelayanan</td>
                <td class="px-4">: </td>
                <td>{{ nama_pelayanan }} </td>
            </tr>
            <tr>
                <td>Harga</td>
                <td class="px-4">: </td>
                <td>{{ harga }} </td>
            </tr>
        </table>

        <div class="my-2 text-center  text-gray-700">
        --------------------------------------------------------------------------------
        </div>
        
    </div>
</div>
</body>
</html>