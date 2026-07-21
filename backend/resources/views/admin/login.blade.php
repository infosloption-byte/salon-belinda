<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login · {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,600&family=Jost:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Jost', sans-serif; background: #2F3E2E; }
        .font-display { font-family: 'Cormorant Garamond', serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm bg-[#FBF6F1] p-8">
        <p class="font-display italic text-3xl text-center mb-1" style="color:#2F3E2E;">{{ config('app.name') }}</p>
        <p class="text-xs uppercase tracking-widest text-center mb-8" style="color:#7A2E3A;">Admin Dashboard</p>

        @if ($errors->any())
            <div class="mb-5 px-4 py-3 text-sm" style="background:#F3DEDB; color:#7A2E3A;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5" style="color:#262220; opacity:.6;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-3 py-2.5 border text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5" style="color:#262220; opacity:.6;">Password</label>
                <input type="password" name="password" required
                       class="w-full px-3 py-2.5 border text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button type="submit" class="w-full py-3 text-sm uppercase tracking-wide" style="background:#7A2E3A; color:#FBF6F1;">
                Log In
            </button>
        </form>
    </div>
</body>
</html>
