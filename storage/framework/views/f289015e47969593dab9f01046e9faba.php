<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Wali Kelas</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Wali Kelas</h1>
<a href="<?php echo e(route('admin.homeroom-assignments.create')); ?>" class="bg-green-600 text-white px-3 py-2 rounded">Tambah</a>
<form method="GET" class="mt-4 flex gap-2">
<select name="class_id" class="border p-2"><option value="">Semua Kelas</option><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php echo e(request('class_id')==$c->id?'selected':''); ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
<button class="bg-blue-600 text-white px-3 py-2 rounded">Filter</button>
</form>
<table class="w-full mt-4 border"><thead><tr><th class="border p-2">Kelas</th><th class="border p-2">Wali Kelas</th><th class="border p-2">Tahun Ajaran</th><th class="border p-2">Semester</th><th class="border p-2">Aksi</th></tr></thead><tbody>
<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td class="border p-2"><?php echo e($a->schoolClass->name); ?></td><td class="border p-2"><?php echo e($a->teacher->name); ?></td><td class="border p-2"><?php echo e($a->academicYear->name); ?></td><td class="border p-2"><?php echo e($a->semester->name); ?></td><td class="border p-2">
<a href="<?php echo e(route('admin.homeroom-assignments.edit',$a)); ?>">Edit</a>
<form method="POST" action="<?php echo e(route('admin.homeroom-assignments.destroy',$a)); ?>" style="display:inline"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button>Hapus</button></form>
</td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody></table>
<?php echo e($items->links()); ?>

</body></html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/homeroom_assignments/index.blade.php ENDPATH**/ ?>