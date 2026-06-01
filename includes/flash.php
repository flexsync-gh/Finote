<?php $flashes = get_flashes(); ?>
<?php foreach ($flashes as $type => $messages) { ?>
    <?php foreach ($messages as $message) { ?>
        <div class="alert alert-<?php echo $type === 'error' ? 'danger' : e($type); ?> alert-dismissible fade show" role="alert">
            <?php echo e($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>
<?php } ?>
