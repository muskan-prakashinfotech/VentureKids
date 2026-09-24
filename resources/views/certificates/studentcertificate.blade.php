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
            
            margin: 10px;
            color: #364D64;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 30pt;
        }

        .assignment {
            margin: 0;
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
            font-size: 18px;
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
        }

        .sign-text {
            margin-top: 10px;
        }

        .top-shape img {
            width: 100%;
        }

        .certificate-logos-table {
            margin: 15px auto 0 auto;
            border-collapse: collapse;
        }

        .logo-frame {
            width: 180px;
            height: 100px;
            text-align: center;
            vertical-align: middle;
        }

        .logo-gap {
            width: 20px;
        }

        .logo-frame img {
            max-width: 180px;
            max-height: 100px;
            width: auto;
            height: auto;
            vertical-align: middle;
        }

        /* .kp-logo {
            padding-top: 8px;
        } */
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
                <td colspan="5" style="text-align: center; padding: 0 50px;"></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px 0 0px 0;"><div class="marquee">CERTIFICATE OF COMPLETION</div></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: center; padding: 0 50px;"><div class="assignment">This certificate is awarded to</div></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; padding: 0 50px;"></td>
                <td colspan="1" style="text-align: center; padding: 0 0;"><div class="person">{{ $student->name }}</div></td>
                <td colspan="2" style="text-align: left; padding: 0 0px;"><div class="logo"><span><img src="{{ asset('asset/dist/img/Wing_Colour_Org.png') }}" width="100px" /></span></div> </td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px 200px;">
                    <div class="reason">
                        {{ $custom_quote ?: $grade->cert_description }}
                    </div>
                </td>
            
            </tr>
            <tr>
                <td colspan="5" style="text-align: center; padding: 0 50px;">
                    @php
                        $defaultLogoPath = asset('asset/images/logo.png');
                        $hasUploadedSchoolLogo = !empty($schoolLogoPath) && $schoolLogoPath !== $defaultLogoPath;
                    @endphp

                    @if($hasUploadedSchoolLogo)
                        <table class="certificate-logos-table">
                            <tr>
                                <td class="logo-frame">
                                    <img src="{{ $schoolLogoPath }}" />
                                </td>
                                <td class="logo-gap"></td>
                                <td class="logo-frame kp-logo">
                                    <img src="{{ $defaultLogoPath }}" />
                                </td>
                            </tr>
                        </table>
                    @else
                        <table class="certificate-logos-table">
                            <tr>
                                <td class="logo-frame kp-logo">
                                    <img src="{{ $defaultLogoPath }}" />
                                </td>
                            </tr>
                        </table>
                    @endif
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
                                    <div class="" style="font-size: 14px;">TANYA SARIN</br>CHIEF LEARNING OFFICER</div>
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
                                    <div style="font-size: 14px;">SWATI GAUBA KOCHAR</br>THINKER IN CHIEF</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 2%"></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: center; padding: 25px 0 25px 0px;" class="footer-text">{{ $grade->cert_quote }}</td>
            </tr>
            
            <tr>
                <td colspan="5" style="text-align: center;"></td>
            </tr>

        </tbody>
    </table>
    
                        
<div style="position: absolute; bottom: 0; left: 5%; right:auto; z-index: 1000; text-align: left; padding: 10px; margin: 0; width: 50%;">
                    <div class="assignment" style="font-size: 8pt; text-align: left; margin: 0;">{{ $certificate->unique_id }}</div>
</div>
                
           
                <div style="position: absolute; bottom: 0; left: auto; right:5%; z-index: 1000; text-align: right; padding: 10px; margin: 0; width:50%; ">
                    <div class="assignment" style="font-size: 8pt; text-align: right; margin: 0;">{{ \Carbon\Carbon::parse($certificate->issue_date)->format('F j, Y') }}</div>
</div>

    <div class="bottom-shape"><img src="{{ asset('asset/images/bottom.png') }}" style="width: 100%;bottom: 0;height: 80px;left: 0;right: 0; position: absolute;"></div>

</body>

</html>
