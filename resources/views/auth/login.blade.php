<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">
<form method="POST" action="/login" class="bg-white p-8 rounded shadow w-96">
@csrf
<h1 class="text-xl font-bold mb-4">Login E-Rapor SMK</h1>
@if($errors->any())
<div class="text-red-600 mb-2">{{ $errors->first() }}</div>
@endif
<input name="email" type="email" placeholder="Email" class="w-full border p-2 mb-3" required>
<input name="password" type="password" placeholder="Password" class="w-full border p-2 mb-3" required>
<button class="w-full bg-blue-600 text-white p-2 rounded">Masuk</button>
</form>
</body>
</html>
