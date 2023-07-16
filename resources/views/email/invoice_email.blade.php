<div style="

        background-color: #b2c3ce;
        padding: 50px;
        width: 100%;
        box-sizing: border-box;
        "  class="d-flex justify-content-center align-items-center ">
    <div class="main_container" style="
            padding: 20px 20px;
            width: 450px;
            height: auto;
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 1px 1px 5px #ddd;
            ">
        <div class="business_details" style="

        "
        class="d-flex justify-content-center align-items-center flex-column ">
            <img style="
            width: 50px;
            height: 50px;
            object-fit: contain;
            " class="business_logo" src="@if($invoice->logo){{env("APP_URL").str_replace('%20', '+', rawurlencode($invoice->logo))}}@else https://i.ibb.co/M8YmF13/Porsche-logo-PNG2.png @endif" alt="">
            <h2 class="business_title" style="
            color: #555555;
            "> {{$invoice->business_name}}
            @if($invoice->logo)
    {{ env("APP_URL") . str_replace('%2F', '/', str_replace('%20', '+', rawurlencode($invoice->logo))) }}
@else
    https://i.ibb.co/M8YmF13/Porsche-logo-PNG2.png
@endif

</h2>
        </div>
        <hr style="border-color: #eeeeee;">
        <div class="action_container" style="

        margin-top: 10px;

        "
        class="d-flex justify-content-center align-items-center flex-column ">
        <div class="row">
            <div class="col" style="margin: auto">
                @if ($invoice->status != "paid")
                <p style="
                color: #555555;
                font-size: 19px;
                ">Invoice for <strong>&pound;{{$invoice->total_amount - $invoice->invoice_payments()->sum("amount")}}</strong> due by
                <strong>{{$invoice->due_date}}</strong>
                {{-- <strong>Jul 11, 2023</strong> --}}
                </p>
                @endif
             </div>

        </div>

        <div class="row">
            <div class="col" style="margin: auto">
                <a style="
            padding: 15px 50px;
            border-radius: 30px;
            background-color: #0575b4;
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            border: none;

            ">View Invoice</a>
             </div>

        </div>


        </div>

        <div class="row">
            <div class="col" style="margin: auto">
                <div class="message_text" style="margin: 50px 0px;">
                    {{$request_obj["message"]}}
                </div>
             </div>

        </div>


        <div class="row">
            <div class="col" style="margin: auto">
                <div class="footer_container">
                    <small style="
                        text-align: center;
                        display: block;
                        ">Invoice {{$invoice->invoice_reference}}</small>
                    <small class="company_name" style="
                    display: block;
                    text-align: center;
                    font-weight: bold;
                    color: #999;
                    ">
                  {{$invoice->business_name}}
                    </small>
                </div>
             </div>

        </div>

    </div>
</div>
