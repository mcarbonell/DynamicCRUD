<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DynamicCRUD')</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        .content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .footer { text-align: center; padding: 20px; color: #666; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>@yield('header', 'DynamicCRUD')</h1>
        <p>@yield('subtitle', 'Template System Demo')</p>
    </div>
    
    <div class="content">
        @yield('content')
    </div>
    
    <div class="footer">
        @include('footer')
    </div>
</body>
</html>