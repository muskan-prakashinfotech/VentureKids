<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Business Plan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            padding: 0;
            margin: 0;
            line-height: 1.6;
        }
        .top-shape img {
            width: 100%;
            height: auto;
            display: block;
        }
        .logo {
            text-align: center;
            margin: 20px 0;
        }
        .logo img {
            height: 60px; /* Adjust as needed */
        }
        
        h1, h2, h3 {
            margin-top: 25px;
            margin-bottom: 10px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 6px;
            text-align: center;
        }
        ul {
            margin: 10px 0 20px 20px;
        }
        li {
            margin-bottom: 5px;
        }
        /* li:before {
            content: "\2610"; //Unicode checkbox 
            margin-right: 8px;
        } */
    </style>
</head>
<body>
     <div class="top-shape">
        <img src="{{ public_path('asset/images/top.png') }}" alt="Header Image">
    </div>

    {{-- Logo --}}
    <div class="logo">
        <img src="{{ public_path('asset/images/logo.png') }}" alt="School Logo">
    </div>

    {!! $html !!}
</body>
</html>
