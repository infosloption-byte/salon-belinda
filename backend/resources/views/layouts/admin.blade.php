<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · Salon Belinda Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,600&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ivory: #FBF6F1; --ivory-dim: #F3ECE3; --ink: #262220;
            --green: #2F3E2E; --gold: #B98A4A; --gold-light: #D6B076;
            --blush-light: #F3DEDB; --maroon: #7A2E3A;
        }
        body { font-family: 'Jost', sans-serif; background: var(--ivory-dim); color: var(--ink); }
        .font-display { font-family: 'Cormorant Garamond', serif; }
        .nav-link { display: flex; align-items: center; gap: .6rem; padding: .55rem .9rem; font-size: .875rem; border-radius: .25rem; color: rgba(251,246,241,0.8); }
        .nav-link:hover { background: rgba(251,246,241,0.08); color: #fff; }
        .nav-link.active { background: var(--gold); color: var(--ink); font-weight: 500; }
        .btn { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1rem; font-size:.825rem; border-radius:.25rem; }
        .btn-primary { background: var(--maroon); color:#fff; }
        .btn-outline { border:1px solid rgba(38,34,32,0.2); color: var(--ink); }
        .badge { display:inline-block; padding:.15rem .55rem; border-radius:9999px; font-size:.7rem; text-transform:uppercase; letter-spacing:.03em; }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex min-h-screen">
        <aside class="w-60 shrink-0 hidden md:flex flex-col" style="background: var(--green);">
            <div class="px-5 py-6">
                <p class="font-display italic text-2xl text-white">Salon Belinda</p>
                <p class="text-[0.65rem] uppercase tracking-widest mt-0.5" style="color: var(--gold-light);">Admin Dashboard</p>
            </div>
            <nav class="flex-1 px-3 space-y-1">
                @php($route = request()->route()->getName())
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $route === 'admin.dashboard' ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.appointments.index') }}" class="nav-link {{ str_starts_with($route, 'admin.appointments') ? 'active' : '' }}">Appointments</a>
                <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ str_starts_with($route, 'admin.testimonials') ? 'active' : '' }}">Reviews</a>
                <a href="{{ route('admin.gallery.index') }}" class="nav-link {{ str_starts_with($route, 'admin.gallery') ? 'active' : '' }}">Gallery</a>
                <a href="{{ route('admin.services.index') }}" class="nav-link {{ str_starts_with($route, 'admin.services') ? 'active' : '' }}">Services</a>
                <p class="text-[0.65rem] uppercase tracking-widest px-3 pt-5 pb-1" style="color: rgba(251,246,241,0.4);">Shop</p>
                <a href="{{ route('admin.products.index') }}" class="nav-link {{ str_starts_with($route, 'admin.products') ? 'active' : '' }}">Products</a>
                <a href="{{ route('admin.orders.index') }}" class="nav-link {{ str_starts_with($route, 'admin.orders') ? 'active' : '' }}">Orders</a>
                <p class="text-[0.65rem] uppercase tracking-widest px-3 pt-5 pb-1" style="color: rgba(251,246,241,0.4);">Site</p>
                <a href="{{ route('admin.contact-messages.index') }}" class="nav-link {{ str_starts_with($route, 'admin.contact-messages') ? 'active' : '' }}">Messages</a>
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" class="p-3">
                @csrf
                <button class="nav-link w-full text-left">Log Out</button>
            </form>
        </aside>

        <div class="flex-1 min-w-0">
            <header class="md:hidden flex items-center justify-between px-4 py-3" style="background: var(--green);">
                <p class="font-display italic text-xl text-white">Salon Belinda Admin</p>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="text-xs text-white/80">Log Out</button>
                </form>
            </header>

            <main class="p-5 md:p-8 max-w-6xl">
                @if (session('success'))
                    <div class="mb-6 px-4 py-3 text-sm" style="background: var(--blush-light); color: var(--maroon);">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 px-4 py-3 text-sm" style="background: var(--blush-light); color: var(--maroon);">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h1 class="font-display text-3xl mb-8">@yield('title', 'Dashboard')</h1>

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
