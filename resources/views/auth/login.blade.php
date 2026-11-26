<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — e-Rapor SMK IT AL-ABROR</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
globalThis.tailwind = globalThis.tailwind || {};
globalThis.tailwind.config = {
  theme: {
    extend: {
      colors: {
        'primary': '#1d4ed8',
        'on-primary': '#ffffff',
        'primary-hover': '#0039b5',
        'primary-container': '#dce1ff',
        'on-primary-container': '#001551',
        'sidebar': '#0b1530',
        'accent': '#f59e0b',
        'surface': '#ffffff',
        'surface-dim': '#f8fafc',
        'on-surface': '#0b1c30',
        'on-surface-muted': '#505f76',
        'outline-variant': '#e0e1f4',
        'error': '#bf0715',
        'error-container': '#ffedea',
        'on-error-container': '#410002',
      },
      fontFamily: {
        'headline': ['"Red Hat Text"', 'sans-serif'],
        'body': ['"Instrument Sans"', 'sans-serif'],
      },
    },
  },
};
</script>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&amp;family=Red+Hat+Text:wght@500;600;700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=block" rel="stylesheet">
<style>.material-symbols-outlined { display: inline-block; width: 1em; height: 1em; line-height: 1 !important; box-sizing: content-box; overflow: hidden; white-space: nowrap; direction: ltr; vertical-align: middle; }</style>
</head>
<body class="bg-surface-dim text-on-surface font-body antialiased min-h-screen flex items-center justify-center p-6">
<div class="w-full max-w-md flex flex-col gap-6">
  <div class="flex flex-col items-center gap-3 text-center">
    <div class="w-14 h-14 rounded bg-primary flex items-center justify-center">
      <span class="material-symbols-outlined text-on-primary" style="font-size: 32px;" aria-hidden="true">school</span>
    </div>
    <div>
      <h1 class="font-headline text-2xl font-bold text-on-surface">e-Rapor SMK</h1>
      <p class="font-body text-sm text-on-surface-muted">SMK IT AL-ABROR &middot; Kurikulum Merdeka</p>
    </div>
  </div>

  <form method="POST" action="/login" class="bg-surface border border-outline-variant rounded-md p-8 flex flex-col gap-4">
    @csrf
    <h2 class="font-headline text-xl font-semibold text-on-surface">Masuk</h2>
    @if($errors->any())
    <div class="bg-error-container text-on-error-container px-4 py-2 rounded font-body text-sm">{{ $errors->first() }}</div>
    @endif
    <div class="flex flex-col gap-1">
      <label for="email" class="font-body text-sm font-semibold text-on-surface">Email</label>
      <input id="email" name="email" type="email" placeholder="nama@sekolah.sch.id" value="{{ old('email') }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-sm focus:border-primary focus:ring-1 focus:ring-primary" required autofocus>
    </div>
    <div class="flex flex-col gap-1">
      <label for="password" class="font-body text-sm font-semibold text-on-surface">Password</label>
      <input id="password" name="password" type="password" placeholder="Password" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-sm focus:border-primary focus:ring-1 focus:ring-primary" required>
    </div>
    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded bg-primary text-on-primary font-body text-sm font-semibold hover:bg-primary-hover">Masuk</button>
  </form>

  <p class="text-center font-body text-xs text-on-surface-muted">Gunakan akun admin, guru, atau wali kelas yang sudah dibuat.</p>
</div>
</body>
</html>
