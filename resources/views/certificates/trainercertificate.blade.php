<html>

<head>
    <style type='text/css'>
        body,
        html {
            margin: 0;
            padding: 0;
            margin: 0 auto;
        }

        body {
            color: black;
            display: table;
            font-family: Georgia, serif;

            text-align: center;
        }

        .container {
            /* border: 1px solid black; */
            /* width: 1380;
            height: 800px; */
            /* display: table-cell; */
            vertical-align: middle;
        }

        .logo {
            color: tan;
        }

        .marquee {
            font-size: 45px;
            margin: 20px;
            color: #364D64;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 24pt;
        }

        .assignment {
            margin: 20px;
            color: #364D64;
            font-family: "Trebuchet MS", sans-serif;
            font-style: normal;
            font-weight: 400;
            text-decoration: none;
            font-size: 13.5pt;
        }

        .person {
            border-bottom: 2px solid black;
            font-size: 32px;
            font-style: italic;
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
            width: 400px;
        }

        .reason {
            margin-top: 0px;
            /* margin: 20px; */
            color: #364D64;
            font-family: "Arial Black", sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
        }

        .sign {
            position: relative;
            /* left: 72%; */
            float: left;    
            width: 50%;
            /* border-top: 4px solid black; */
        }

        .disp{
            float: left;
            width: 100%;
        }

        .certificate {
            padding: 20px;
            background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url("back.png");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: right;
        }

        .des {
            margin-top: 40px;
            margin-bottom: 80px;
            color: #5B5B5B;
            font-family: "Arial Black", sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 14pt;
            font-weight: 600;
        }

        .footer-text {
            font-family: "Calibri", sans-serif;
            font-style: normal;
            font-weight: 400;
            text-decoration: none;
            font-size: 12pt;
            width: 95%;
            margin-top: 15px;
        }

        .sign-text {
            margin-top: 10px;
        }

        .top-shape img {
            width: 100%;
        }
    </style>
</head>

<body>
    <table cellpadding="0" cellmargin="0" width="100%" style="padding: 0;">
        <tbody>
            <tr>
                <td colspan="5" style="text-align: center;">
                    <div class="top-shape"><img src="{{ asset('asset/images/top.png') }}" style="height: 80px;"></div>
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: center; padding: 0 50px;">
                    <div class="logo">
                        <span><img src="{{ asset('asset/images/logo.png') }}"></span>
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: center; padding: 0 50px;">
                    <div class="marquee">
                        CERTIFIED EARLY ENTREPRENEURSHIP TRAINER
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: center; padding: 0 50px;">
                    <div class="assignment">
                        Congratulations to
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: center; padding: 0 50px;">
                    <div class="person">
                        {{ $trainer->trainer_name }}
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: center; padding: 0 50px;">
                    <div class="reason">
                        for completing teacher training program
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: center; padding: 0 50px;">
                    <div class="des">
                        &nbsp;
                    </div>
                </td>
            </tr>

            <tr>
                <td style="width: 2%"></td>
                <td style="text-align: left; width: 28%;">
                    <table style="text-align: center; border-bottom: 1px solid #e1e1e1; width: 100%; margin: 0">
                        <tbody>
                            <tr>
                                <td>
                                    <img src="{{ asset('asset/images/tanya-sarin.png') }}" width="200px">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 40%;"></td>
                <td style="text-align: right; width: 28%;">
                    <table style="text-align: center; border-bottom: 1px solid #e1e1e1; width: 100%; margin: 0">
                        <tbody>
                            <tr>
                                <td>
                                    <img src="{{ asset('asset/images/swati-gauba.png') }}" width="200px">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 2%"></td>
            </tr>

            <tr>
                <td style="width: 2%"></td>
                <td style="text-align: left; width: 28%;">
                    <table style="text-align: center; width: 100%; margin: 0">
                        <tbody>
                            <tr>
                                <td class="" style="text-align: center; font-family: Arial Black, sans-serif;">
                                    <div class="">TANYA SARIN</br>CHIEF LEARNING OFFICER</div>
                                </td>    
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 40%;"></td>
                <td style="text-align: right; width: 28%;">
                    <table style="text-align: center; width: 100%; margin: 0">
                        <tbody>
                            <tr>
                                <td class="" width="100%" style="text-align: center; font-family: Arial Black, sans-serif;">
                                    <div class="">SWATI GAUBA KOCHAR</br>THINKER IN CHIEF</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 2%"></td>
            </tr>

            
            <tr>
                <td colspan="5" style="text-align: center; padding: 0 50px;">
                    <div class="footer-text">
                        "Children are not things to be molded, but are people to be unfolded." - Jess Lair
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align: center;height: 30px;"></td>
            </tr>

        </tbody>

    </table>

    <div class="bottom-shape"><img src="{{ asset('asset/images/bottom.png') }}" style="width: 100%;position: absolute;bottom: 0;height: 60px;left: 0;right: 0;"></div>
    
</body>

</html>
