<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <div style="


        background-color: #b2c3ce;
        padding: 50px;
        width: 100%;
        box-sizing: border-box;
        "
        class="d-flex justify-content-center align-items-center text-center"
        >
    <div class="main_container" style="
            width: 450px;
            height: auto;
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 1px 1px 10px #555;
            border-radius: 20px;
            ">

        <div style="padding: 30px 20px;">
            <div class="business_details" style="

                "
                   class="d-flex justify-content-center align-items-center flex-column text-center"
                >
                <img style="
                    width: 200px;
                    height: 200px;
                    object-fit: contain;
                    margin-bottom: 20px;
                    " class="business_logo me-auto" src="https://i.ibb.co/M8YmF13/Porsche-logo-PNG2.png" alt="">
                <h2 class="business_title" style="
                    color: #000000;
                    font-size: 40px;
                    line-height: 0px;
                    ">Payment Recipt</h2>
                <span style="line-height: 30px; font-size: 20px;">Invoice No: {{$invoice->invoice_number}}</span>
                <div
                    style="margin-top: 10px;"   class="d-flex justify-content-center align-items-center flex-column text-center">
                    <span style="line-height: 23px;  color: #999; font-size: 16px;">for {{$invoice->business_name}}</span>
                    <span style="line-height: 23px;  color: #999; font-size: 16px;">Paid on {{$invoice_payment->payment_date}}
                        {{-- Jul 12,
                        2023 --}}
                    </span>
                </div>

                <div
                    style="margin-top: 30px;"   class="d-flex justify-content-center align-items-center flex-column text-center">
                    <span style="line-height: 20px; font-weight: 700; color: #999; font-size: 13px;">
                        {{$invoice->business_name}}

                    </span>
                    <span style="line-height: 20px; font-weight: 700; color: #999; font-size: 13px;">{{$invoice->business_address}}</span>
                </div>
            </div>
        </div>
        <div style="width: 100%; position: relative; margin-top: 90px">
            <div
                style="width: 40px; height: 40px;overflow: hidden;border-radius: 30px; position: absolute; top: 50%;left: 50%;transform: translate(-50%,-50%);">
                <img style="width: 40px; height: 40px;object-fit: contain;" class="me-auto" src="https://i.ibb.co/HTBxW8w/OIP.jpg"
                    alt="">
            </div>

            <hr style="border-color: #eeeeee;">
        </div>


        <div class="message_text" style="margin: 50px 0px;line-height: 30px; padding: 30px;">
          {{$invoice_payment->note}}
        </div>


        <hr style="border-color: #eeeeee;">
        <div>
            <span style="width: 100%;display: block;text-align: center;font-size: 22px; color: #444;">Payment Amount:
                <strong>${{$invoice_payment->amount}}  </strong></span>
        </div>
        <hr style="border-color: #eeeeee;">

        <p style="text-align: center;color: #444;font-size: 14px;margin: 30px 0px;">PAYMENT METHOD:
            <strong>{{$invoice_payment->payment_method}}</strong>
        </p>



        <div style="margin: 50px 0px;width: 100%;"   class="d-flex justify-content-center align-items-center text-center">
            <a style="
            padding: 12px 50px;
            border-radius: 30px;
            background-color: #0575b4;
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            border: none;

            ">View Invoice</a>

        </div>

        @if ($invoice->status == "paid")
        <div style="margin-bottom: 50px;"   class="d-flex justify-content-center align-items-center text-center">
            <a href="https://www.flaticon.com/authors/freepik" title="Freepik">
                <img style="width: 100px; height: 100px;transform: rotate(-30deg);"
                  class="me-auto"  src="https://i.ibb.co/44gfbv7/paid-1.png" alt="">
            </a>
        </div>
        @endif

        <div
            style="background-color:#eee; width: 100%; padding: 20px 30px; box-sizing: border-box; text-align: center; border-radius: 0px 0px 20px 20px;">

            Thanks for your business. If this invoice was sent in error, please contact <a
                href="mailto:md.nazmul.islam.javascript@gmail.com"
                style="color: #0575b4;">{{auth()->user()->email}}</a>
        </div>
    </div>
</div>

</body>
</html>
