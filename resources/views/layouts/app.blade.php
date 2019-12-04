<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | @yield('subtitle')</title>

    <!-- Scripts -->
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
    <link rel="icon" href="{{ asset('images/logo.webp') }}">

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
            color: black;
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
        <nav class="navbar navbar-light bg-white shadow-sm">
            <div class="container">
                <!-- Image and text -->
                <nav class="navbar">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="{{ asset('images/logo.webp') }}" alt="logo" style="max-height: 50px;">
                        <span class="d-none d-lg-inline">{{ config('app.name', 'Laravel') }} | @yield('subtitle')</span>
                    </a>
                </nav>

                <div class="clock-widget">
                    <span id="time">Time</span>
                    <span id="date">Date</span>
                </div>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        @auth
                            <li class="nav-item active">
                                <a class="nav-link" href="{{ route('landing_page') }}">Home <span class="sr-only">(current)</span></a>
                            </li>

                            <li class="nav-item active">
                                <a class="nav-link" href="{{ route('home') }}">Availabilities <span class="sr-only">(current)</span></a>
                            </li>
                            
                            <li class="nav-item active">
                                <a class="nav-link" href="{{ route('payment.create') }}">Anticipi/Incassi <span class="sr-only">(current)</span></a>
                            </li>

                            <li class="nav-item active">
                                <a class="nav-link" href="{{ route('articles.index') }}">Articles & Roles</a>
                            </li>

                            @if(Auth::user()->access_levels()->whereHas('info', function($q) {
                            $q->where('code', 'admin');
                            })->first())
                            <li class="nav-item active">
                                <a class="nav-link" href="{{ route('tourguide.index') }}">Tour Guides Listing</a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link" href="/tours">Tours Listing</a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link" href="/tourcalendar">Calendars</a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link" href="/notification">Notifications</a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link" href="/statistics">Economics & Statistics</a>
                            </li>
                            @else
                            <li class="nav-item active">
                                <a class="nav-link" href="/guide/statistics">Economics</a>
                            </li>
                            @endif
                            
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown active">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->full_name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('myprofile') }}">
                                        {{ __('Profile') }}
                                    </a>

                                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                                        {{ __('Account Setting') }}
                                    </a>
                                    
                                    <!-- <a class="dropdown-item" href="{{ route('settings.index') }}">
                                        {{ __('Settings') }}
                                    </a> -->

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
