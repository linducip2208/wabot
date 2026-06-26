<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — WABot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <style>body{font-family:'Inter',sans-serif}</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-brand-600 tracking-tight">WABot</h1>
        <p class="text-gray-500 mt-1">WhatsApp Bulk & Auto-Reply SaaS</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Daftar</h2>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/register" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-2.5 border text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-2.5 border text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input type="password" name="password" required
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-2.5 border text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-4 py-2.5 border text-sm">
            </div>
            <button type="submit"
                class="w-full bg-brand-600 text-white rounded-xl py-2.5 font-semibold text-sm hover:bg-brand-700 transition shadow-sm">
                Daftar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Sudah punya akun? <a href="/login" class="text-brand-600 font-semibold hover:underline">Masuk</a>
        </p>
    </div>
</div>
</body>
</html>
