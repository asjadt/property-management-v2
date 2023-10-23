<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>


    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
    </style>

</head>
<body style="font-family: 'Roboto', sans-serif;">
    <!--HEADER -->
    <table style="width:100%;">
        <tbody>
            <tr style="width:100%">
                <td style="width:50%">
                    <img  style="width:30%; height:auto;"  src="https://i.ibb.co/ZMqCYgY/Colorful-Abstract-Fluid-Globe-Networking-Logo.png" />
                </td>
                <td style="width:50%">
                    <div style="text-align:right;">
                        <h2 style="margin:0">{{$business->name}}</h2>
                        <address> {{$business->address_line_1}} </address>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <hr style="height:0.2px;color:#aaa;" />

    <!--BILLING -->
    <div>
        <h4 style="margin:0;">Bill to:</h4>
        <p style="font-size:13px;margin:0;">Landlor Name</p>
        <p style="font-size:13px;margin:0;">Role: Landlord</p>
        <p style="font-size:13px;margin:0;">0293092039008</p>
        <p style="font-size:13px;margin:0;">testmail@mail.com</p>
    </div>

    <div>
        <!--TITLE -->
        <h2 style="text-align:center;">Invoice Report</h2>

        <div>
            <h4 style="margin-bottom:5px;">1. This is a Address of this landlord</h4>
            <table style="width:100%; border-collapse: collapse;">
                <thead style="background:#B27D10;color:#fff;">
                    <tr>
                        <td style="border:none;">Status</td>
                        <td style="border:none;">Invoice Date</td>
                        <td style="border:none;">Reference</td>
                        <td style="border:none;">Due Date</td>
                        <td style="border:none;">Total</td>
                        <td style="text-align:right;">Amount Due</td>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:0.5px solid #aaa">
                        <td>Paid</td>
                        <td>September 13, 2023</td>
                        <td >#0900</td>
                        <td>October 24, 2023</td>
                        <td>£4565</td>
                        <td style="text-align:right;">£49248</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div>
            <h4 style="margin-bottom:5px;">2. This is a Address of this landlord</h4>
            <table style="width:100%; border-collapse: collapse;">
                <thead style="background:#B27D10;color:#fff;">
                    <tr>
                        <td style="border:none;">Status</td>
                        <td style="border:none;">Invoice Date</td>
                        <td style="border:none;">Reference</td>
                        <td style="border:none;">Due Date</td>
                        <td style="border:none;">Total</td>
                        <td style="text-align:right;">Amount Due</td>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:0.5px solid #aaa;">
                        <td>Paid</td>
                        <td>September 13, 2023</td>
                        <td >#0900</td>
                        <td>October 24, 2023</td>
                        <td>£4565</td>
                        <td style="text-align:right;">£49248</td>
                    </tr>
                </tbody>
            </table>
            <div style="text-align:right">
                <table style="width:80%; border-collapse: collapse;">
                <thead style="background:#B27D10;color:#fff;">
                    <tr>
                        <td style="border:none;">Items</td>
                        <td style="border:none;">Quantity</td>
                        <td style="border:none;">Price</td>
                        <td style="border:none;">Amount</td>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:0.5px solid #aaa;">
                        <td>
                            <div>
                                <p>Commission</p>
                                <span>Commission on sale</span>
                            </div>
                        </td>
                        <td>10</td>
                        <td >£300</td>
                        <td>£3000</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

    </div>
</body>
</html>
