<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Kelas</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Data Kelas</h1>
<a href="<?php echo e(route('admin.classes.create')); ?>" class="bg-green-600 text-white px-3 py-2 rounded">Tambah</a>
<table class="w-full mt-4 border"><thead><tr><th>Nama</th><th>Level</th><th>Aksi</th></tr></thead><tbody>
<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td><?php echo e($c->name); ?></td><td><?php echo e($c->level); ?></td><td>
<a href="<?php echo e(route('admin.classes.edit',$c)); ?>">Edit</a>
<form method="POST" action="<?php echo e(route('admin.classes.destroy',$c)); ?>" style="display:inline"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button>Hapus</button></form>
</td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody></table>
<?php echo e($items->links()); ?>

</body></html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/classes/index.blade.php ENDPATH**/ ?>