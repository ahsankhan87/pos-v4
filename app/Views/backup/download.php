<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Database Backup</h1>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-blue-800 text-sm">
                <i class="fas fa-info-circle mr-2"></i>
                Download a complete backup of the database. The backup will be in SQL format.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-6">
            <a href="<?= base_url('backup/download?method=php') ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-center">
                <i class="fas fa-download mr-2"></i>
                Download Backup (PHP Method)
            </a>
            <!-- 
            <a href="<?= base_url('backup/download?method=mysqldump') ?>"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-center">
                <i class="fas fa-database mr-2"></i>
                Download Backup (MySQLDump Method)
            </a> -->
        </div>

        <?php if (session()->has('error')): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-800 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= session()->get('error') ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="text-center text-sm text-gray-600">
            <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
            <p>File format: SQL</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>