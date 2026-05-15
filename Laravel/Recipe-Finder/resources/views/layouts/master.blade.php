<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Recipe Finder</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
</head>
<body>

@include('includes.header')

@yield('content')

@include('includes.footer')

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/API_Ops.js') }}"></script>
<script src="{{ asset('js/featuredRecipes.js') }}"></script>
<script src="{{ asset('js/myRecipes.js') }}"></script>

</body>
</html>
