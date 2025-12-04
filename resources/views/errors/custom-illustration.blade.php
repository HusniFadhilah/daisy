<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

    <!-- My CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;800&display=swap" rel="stylesheet">
    <!-- End of My CSS -->

    <title>@yield('title')</title>
    <style>
        /* CSS */

        /* MOBILE DEVICE STYLE (DEFAULT) */
        body {
            background-color: #0D1128;
        }

        /* all content except illustration css*/
        .content {
            margin-top: 0%;
        }

        /* illustration class css */
        .illustration-image {
            /* hide illustration in mobile device */
            max-width: 0%;
        }

        /* logo (image) class css */
        .logo {
            margin-top: 10px;
            max-width: 70px;
        }

        /* logo (container) class css */
        .class-logo {
            margin-top: 24px;
            text-align: center;
        }

        /* main title css */
        .main-title h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 32px;
            text-align: center;
            margin-top: 40px;
            color: white;
        }

        /* secondary text css */
        p {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            color: white;
        }

        /* another service css */
        .another-services {
            margin-top: 100px;
        }

        /* all button css */
        .btn {
            margin-top: 8px;
            margin-left: auto;
            border-radius: 100px;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
        }

        /* dbo button css */
        .dbo {
            margin-left: 20%;
            margin-right: 20%;
            width: 60%;
        }

        /* dmw button css */
        .dmw {
            margin-left: 20%;
            margin-right: 20%;
            width: 60%;
        }

        /* bukulink button css */
        .bukulink {
            margin-left: 20%;
            margin-right: 20%;
            width: 60%;
        }

        /* END OF MOBILE DEVICE CSS */


        /* DESKTOP CSS */
        @media (min-width: 992px) {

            body {
                background-color: #0D1128;
                overflow: hidden;
            }

            /* illustration css */
            .illustration-image {
                position: absolute;
                max-width: 50%;
                margin-left: 55%;
                margin-right: -40px;
                margin-top: -80px;
            }

            /* content except the illustration css */
            .content {
                z-index: 1;
                position: relative;
            }

            /* main title css */
            .main-title h1 {
                font-family: 'Poppins', sans-serif;
                font-weight: 800;
                font-size: 40px;
                text-align: left;
                margin-top: 40px;
                color: white;
            }

            /* all paragraph text css */
            p {
                margin-top: 30px;
                text-align: left;
                font-size: 16px;
                font-family: 'Poppins', sans-serif;
                font-weight: 400;
                color: white;
            }


            /* logo (image) css */
            .class-logo {
                margin-top: 30px;
                text-align: left;
            }

            /* another service text css */
            .another-services {
                margin-top: 40px;
            }

            /* all button css */
            .btn {
                width: 150px;
                margin-top: 8px;
                margin-right: 20px;
                border-radius: 100px;
                font-family: 'Poppins', sans-serif;
                font-weight: 400;
            }

            /* dbo button css */
            .dbo {
                margin-left: 0;
                margin-right: 40px;
                width: 180px;
            }

            /* dmw button css */
            .dmw {
                margin-left: 0;
                margin-right: 40px;
                width: 140px;
            }

            /* bukulink button css */
            .bukulink {
                margin-left: 0;
                margin-right: 40px;
                width: 140px;
            }

            /* END OF DESKTOP CSS */
        }


        /* END OF CSS */

    </style>
</head>
<body>
    <!-- Image Illustration Top-Right -->
    <img src="{{ asset('assets/img/illustration/illustration.png') }}" alt="" class="illustration-image">

    <!-- Content except illustration -->
    <div class="content">

        <!-- AUDI's logo container -->
        <div class="container class-logo">
            <img src="{{ asset('assets/img/logo/logo-bg.png') }}" alt="AUDI Logo" class="logo">
        </div>
        <!-- End of AUDI's logo container -->

        <!-- Main title -->
        <div class="container main-title">
            <h1>Opss!<br>Error: @yield('code', __('Oh no'))</h1>
        </div>
        <!-- End of Main title -->

        <!-- Secondary title -->
        <div class="container secondary-text">
            <div class="row">
                <div class="col-lg-4">
                    <p>@yield('message')</p>
                </div>
            </div>
        </div>
        <!-- End of Secondary title -->

        <!-- Another Services -->
        <div class="container">
            <a href="{{ app('router')->has('home') ? route('home') : url('/') }}"><button type="button" class="btn btn-light dbo">Return Home</button></a>
        </div>
        <!-- End of Another Services -->

    </div>
    <!-- End content except illustration -->


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>
</html>
