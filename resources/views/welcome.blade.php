<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | @yield('subtitle')</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('js/manifest.js') }}" defer></script>
    <script src="{{ asset('js/vendor.js') }}" defer></script>
    <script src="{{ asset('js/bootstrap.js') }}" defer></script>
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    
    <link
      href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons"
      rel="stylesheet"
    >
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css"
      rel="stylesheet"
    >
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/4.4.95/css/materialdesignicons.min.css"
      rel="stylesheet"
    >

    <!-- Icon -->
    <link rel="icon" href="{{ asset('images/logo.png') }}">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    <style>
        .container {
            width: 100%!important;
        }
        .form-group {
            margin-bottom: unset!important;
        }
        .clock-widget span{
            color: white;
            font-size: 20px;
        }
    </style>
    <script type="text/javascript"> 
        function display_c(){
            var refresh=1000; // Refresh rate in milli seconds
            mytime=setTimeout('display_ct()',refresh)
        }

        function display_ct() {
            var x = new Date()
            var hours = x.getHours()
            var minutes = x.getMinutes()
            var seconds = x.getSeconds()
            var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
            var day = x.getDate()
            var month = months[x.getMonth()]
            var year = x.getFullYear()

            if(hours < 10) {
                hours = '0' + hours
            }

            if(minutes < 10) {
                minutes = '0' + minutes
            }

            if(seconds < 10) {
                seconds = '0' + seconds
            }

            document.getElementById('time').innerHTML = hours + ':' + minutes + ':' + seconds
            document.getElementById('date').innerHTML = month + ' ' + day + ', ' + year
            display_c()
        }
    </script>
</head>
<body onload=display_ct();>
    <div id="app">
        <nav class="navbar navbar-dark bg-blue">
            <div class="container">
                <!-- Image and text -->
                <nav class="navbar">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="{{ asset('images/logo_white.png') }}" alt="logo" style="max-height: 60px;">
                        <span class="d-none d-lg-inline" style="font-family: serif;">{{ config('app.name', 'Laravel') }} | Home</span>
                    </a>
                </nav>
                <div class="clock-widget">
                    <span id="time"></span>
                    <span id="date"></span>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">    
                <div class="row menu">
                    <div class="col-md-3">
                        <a href="/home">
                            <div class="card-body availability">
                                <div class="col-md-12 text-center">
                                    <span>Availabilities</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/payment/create">
                            <div class="card-body anticipi-incassi">
                                <div class="col-md-12 text-center">
                                    <span>Acticipi & Incassi</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('articles.index') }}">
                            <div class="card-body articles">
                                <div class="col-md-12 text-center">
                                    <span>Articles</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    @if(Auth::user()->access_levels()->whereHas('info', function($q) {
                    $q->where('code', 'admin');
                    })->first())
                    <div class="col-md-3">
                        <a href="/tours">
                            <div class="card-body tours-listing">
                                <div class="col-md-12 text-center">
                                    <span>Tours Listing</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/tourcalendar">
                            <div class="card-body calendars">
                                <div class="col-md-12 text-center">
                                    <span>Calendars</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('tourguide.index') }}">
                            <div class="card-body tour-guides-listing">
                                <div class="col-md-12 text-center">
                                    <span>Tour Guides Listing</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/notification">
                            <div class="card-body notifications">
                                <div class="col-md-12 text-center">
                                    <span>Notifications</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/statistics">
                            <div class="card-body economics-statistics">
                                <div class="col-md-12 text-center">
                                    <span>Economics & Statistics</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @else
                    <div class="col-md-3">
                        <a href="/guide/statistics">
                            <div class="card-body economics-statistics">
                                <div class="col-md-12 text-center">
                                    <span>Economics</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <a href="/myprofile">
                            <div class="card-body my-profile">
                                <div class="col-md-12 text-center">
                                    <span>My Profile</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/profile">
                            <div class="card-body account-setting">
                                <div class="col-md-12 text-center">
                                    <span>Account Setting</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            <div class="card-body logout">
                                <div class="col-md-12 text-center">
                                    <span>Logout</span>
                                </div>
                            </div>
                        </a>
                        
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>