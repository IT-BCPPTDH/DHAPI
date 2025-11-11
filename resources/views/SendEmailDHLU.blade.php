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
            background-color: rgb(93, 203, 247);
        }
    </style>
</head>
<body>
    <h1>Dear, Team</h1>

    <h2>
        Berikut ini adalah informasi mengenai <i>Log Operator Line Up</i> pada aplikasi <i>Line Up Operator</i>.
    </h2>

    <h3>
        A. Summary Audiens yang melakukan tahap uji coba.
    </h3>

    <div>
        <table style="width: 50%">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 10%">Tanggal Sesi</th>
                    <th style="width: 10%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ( $data2 as $item2 )
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item2->date_lineup }}</td>
                        <td>{{ $item2->summary }}</td>
                @endforeach
            </tbody>
        </table>
    </div>

    <h3>
        B. Details Audiens yang melakukan tahap uji coba.
    </h3>
    <div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 10%">JDE</th>
                    <th style="width: 15%">Name</th>
                    <th style="width: 15%">Log</th>
                    <th style="width: 15%">Address</th>
                    <th style="width: 20%">Device & Platform</th>
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
                        <td>{{ $item->device }} - {{ $item->platform }}</td>
                    </tr>
                @endforeach
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

