<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: rgb(204, 24, 24);
        }
    </style>
</head>
<body>
    <h1>Dear, Team</h1>

    <h2>
        Berikut ini adalah informasi mengenai <i><b style="color: red">Karyawan Yang Belum Ter-Registrasi</b></i> pada aplikasi <i>FMS</i>.
    </h2>

    <h3>
        A. List Karyawan(<i>Operator</i>).
    </h3>
    <div>
        <table style="width: 50%">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 10%">JDE</th>
                    <th style="width: 15%">Name</th>
                    <th style="width: 15%">Log</th>
                    <th style="width: 15%">Address</th>
                    <th style="width: 20%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ( $data as $item )
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->jdeno }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->date_lineup }}</td>
                        <td>{{ $item->address }}</td>
                        <td>{{ $item->status }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="5">Jumlah</td>
                    <td>{{ $count->total_data }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <br><br><br>
    With Appreciation,
    <br>
    PT Darma Henwa, Tbk
    <br>
    <br>
    <br>
    Thank you,
    <br>
    <i>This email automatically generate by system, please do not reply</i>
</body>
</html>

