<style>
    /* Override Bootstrap grid classes */
    .container {
        width: 100% !important;
        padding-left: 20px !important;
        padding-right: 20px !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .row {
        display: flex !important;
        flex-wrap: wrap !important;
        margin-left: -10px !important;
        margin-right: -10px !important;
    }

    .col-4 {
        width: 33.33% !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
    .center-image {
  text-align: center;
}

.center-image img {
  display: inline-block;
}
    /* Add other Bootstrap grid classes as needed */

    /* Add any other custom styles for Gmail or email clients here */
</style>
<div style="
text-align: center;
        background-color: #b2c3ce;
        padding: 50px;
        width: 100%;
        box-sizing: border-box;
        "  class=" ">
    <div class="main_container" style="
    text-align: center;
            padding: 20px 20px;
            width: 450px;
            height: auto;
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 1px 1px 5px #ddd;
            ">


            <div class="center-image">
                <img style="
                width: 50px;
                height: 50px;
                margin: 0 auto;
                " class="business_logo" src="@if($invoice->logo){{(env("APP_URL")."/".$invoice->logo)}}@else https://i.ibb.co/M8YmF13/Porsche-logo-PNG2.png @endif" alt="">
              </div>






            <div class="row">
                <div class="col" style="margin: auto">
                    <h2 class="business_title" style="
                    color: #555555;
                    ">
                     {{$invoice->business_name}}


        </h2>
                </div>
                </div>



        <hr style="border-color: #eeeeee;">
        <div class="action_container" style="

        margin-top: 10px;

        "
        class="">
        <div class="row">
            <div class="col" style="margin: auto; text-align: center;">
                @if ($invoice->status != "paid")
                <p style="
                color: #555555;
                font-size: 19px;
                text-align: center;
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
            padding: 10px 50px;
            border-radius: 30px;
            background-color: #0575b4;
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            border: none;
            text-align: center;
            display:block;
            margin-bottom: 10px;

            ">View Invoice</a>
             </div>

        </div>


        </div>

      


        <div class="row">
            <div class="col footer_container" style="margin: auto">

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
