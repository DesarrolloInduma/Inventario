<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | Inventario INDUMA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f5f7] font-sans text-slate-800 antialiased">

{{-- Indicador de carga --}}
<div id="app-progress" aria-hidden="true"><span></span></div>
<div id="app-loader" role="status" aria-live="polite">
    <div class="app-loader__card">
        <span class="app-loader__ring"><i id="app-loader-icon" class="bi bi-shield-lock"></i></span>
        <p class="app-loader__title" id="app-loader-title">Verificando acceso…</p>
        <p class="app-loader__sub" id="app-loader-sub">Un momento por favor</p>
        <span class="app-loader__bar"><span></span></span>
    </div>
</div>

<main class="min-h-screen p-0 sm:p-5 lg:p-8">
<div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-[1440px] overflow-hidden bg-white shadow-[0_24px_90px_-35px_rgb(15_23_42/.35)] sm:rounded-[2rem] lg:grid-cols-[1.02fr_.98fr]">
    <section class="relative flex min-h-[390px] items-center justify-center overflow-hidden bg-[#b3072d] p-8 sm:p-12 lg:min-h-full lg:p-16">
        <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(135deg, transparent 0 47%, white 47.2% 47.5%, transparent 47.7%), linear-gradient(45deg, transparent 0 58%, white 58.2% 58.5%, transparent 58.7%); background-size: 180px 180px;"></div>
        <div class="absolute -bottom-40 -left-20 size-[32rem] rounded-full border-[70px] border-white/10"></div>
        <div class="absolute -right-28 -top-28 size-[28rem] rounded-full border-[45px] border-black/10"></div>
        <div class="relative w-full max-w-xl text-center">
            <div class="mx-auto flex min-h-[220px] items-center justify-center rounded-[2rem] bg-white px-8 py-10 shadow-2xl shadow-black/20 sm:min-h-[300px] sm:px-14"><img src="{{ asset('images/logo-induma.png') }}" alt="INDUMA S.A.S." class="h-auto w-full max-w-[460px]"></div>
            <p class="mt-8 text-xs font-bold uppercase tracking-[.3em] text-white/75">Sistema de inventario tecnologico</p>
            <h1 class="mt-3 font-display text-4xl font-bold uppercase tracking-wide text-white sm:text-5xl">Gestion que mantiene todo en orden</h1>
            <div class="mx-auto mt-6 h-1 w-16 rounded-full bg-white/70"></div>
        </div>
    </section>

    <section class="relative flex min-h-[620px] items-center justify-center overflow-hidden bg-[#fbfcfd] px-6 py-12 sm:px-12 lg:px-20">
        <div class="pointer-events-none absolute -right-28 -top-28 size-80 rounded-full border border-slate-200"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-32 size-96 rounded-full border border-orange-200"></div>
        <div class="relative w-full max-w-[390px] animate-fade-up">
            <div class="mb-8 flex items-center gap-3 lg:hidden"><span class="grid size-11 place-items-center rounded-xl bg-[#b3072d] text-2xl font-black text-white">I</span><div><p class="font-display text-2xl font-bold tracking-wide text-slate-900">INDUMA</p><p class="text-[10px] font-bold uppercase tracking-[.2em] text-cyan-700">Inventario tecnologico</p></div></div>
            <div class="mb-7 flex items-end justify-between gap-4"><div><p class="font-display text-xs font-bold uppercase tracking-[.24em] text-[#b3072d]">Acceso interno</p><h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">Hola de nuevo</h2><p class="mt-2 text-sm leading-6 text-slate-500">Ingresa a tu espacio de trabajo en INDUMA.</p></div><span class="hidden rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-cyan-700 sm:inline-flex"><i class="bi bi-shield-check mr-1"></i> Entorno seguro</span></div>
            <form method="POST" action="{{ route('login.attempt') }}" data-loading="Verificando acceso…" data-loading-sub="Validando tus credenciales…" x-data="{ show: false }" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_24px_70px_-32px_rgb(15_23_42/.4)] sm:p-8">
                @csrf
                @if($errors->any())<div class="mb-5 flex gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><i class="bi bi-exclamation-circle-fill"></i><p>{{ $errors->first() }}</p></div>@endif
                <label for="email" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Correo electronico</label>
                <div class="mt-2 flex items-center rounded-xl border border-slate-200 bg-slate-50 transition focus-within:border-[#b3072d] focus-within:bg-white focus-within:ring-4 focus-within:ring-red-700/10"><i class="bi bi-envelope ml-4 text-slate-400"></i><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="usuario@induma.com" class="w-full bg-transparent px-3 py-3.5 text-sm outline-none placeholder:text-slate-400"></div>
                <label for="password" class="mt-5 block text-xs font-bold uppercase tracking-wide text-slate-500">Contrasena</label>
                <div class="mt-2 flex items-center rounded-xl border border-slate-200 bg-slate-50 transition focus-within:border-[#b3072d] focus-within:bg-white focus-within:ring-4 focus-within:ring-red-700/10"><i class="bi bi-lock ml-4 text-slate-400"></i><input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="Ingresa tu contrasena" class="w-full bg-transparent px-3 py-3.5 text-sm outline-none placeholder:text-slate-400"><button type="button" @click="show = !show" class="mr-4 text-slate-400 transition hover:text-[#b3072d]" :aria-label="show ? 'Ocultar contrasena' : 'Mostrar contrasena'"><i class="bi text-sm" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i></button></div>
                <label class="mt-5 flex cursor-pointer items-center gap-2 text-sm text-slate-500"><input type="checkbox" name="remember" class="size-4 rounded border-slate-300 text-red-700 focus:ring-red-700/30">Mantener sesion abierta</label>
                <button type="submit" class="group mt-7 flex w-full items-center justify-center gap-2 rounded-xl bg-[#b3072d] py-3.5 text-sm font-bold text-white shadow-lg shadow-red-900/20 transition hover:bg-[#8e0626] active:scale-[.98]">Ingresar al sistema <i class="bi bi-arrow-right transition group-hover:translate-x-1"></i></button>
            </form>
            <div class="mt-7 flex items-center justify-center gap-2 text-xs text-slate-400"><i class="bi bi-shield-check text-cyan-600"></i><span>Acceso protegido | INDUMA S.A.S.</span></div>
        </div>
    </section>
</div>
</main>
</body>
</html>
