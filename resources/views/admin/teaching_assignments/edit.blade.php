<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Edit Penugasan Guru</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Penugasan Guru</h1>
<form method="POST" action="{{ route('admin.teaching-assignments.update',$assignment) }}">
@csrf @method('PUT')
<select name="teacher_id" class="border p-2 mb-2 block">@foreach($teachers as $t)<option value="{{ $t->id }}" {{ $assignment->teacher_id==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach</select>
<select name="subject_id" class="border p-2 mb-2 block">@foreach($subjects as $s)<option value="{{ $s->id }}" {{ $assignment->subject_id==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select>
<select name="class_id" class="border p-2 mb-2 block">@foreach($classes as $c)<option value="{{ $c->id }}" {{ $assignment->class_id==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select>
<select name="academic_year_id" class="border p-2 mb-2 block">@foreach($years as $y)<option value="{{ $y->id }}" {{ $assignment->academic_year_id==$y->id?'selected':'' }}>{{ $y->name }}</option>@endforeach</select>
<select name="semester_id" class="border p-2 mb-2 block">@foreach($semesters as $s)<option value="{{ $s->id }}" {{ $assignment->semester_id==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select>
<input name="hours" type="number" min="0" max="40" value="{{ $assignment->hours }}" class="border p-2 mb-2 block">
<button class="bg-blue-600 text-white px-3 py-2 rounded">Simpan</button>
</form>
</body></html>
