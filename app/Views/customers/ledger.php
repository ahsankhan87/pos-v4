<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg mt-8 p-8">
    <h2 class="text-2xl font-bold mb-6 text-blue-700">Customer Ledger</h2>
    <table class="min-w-full mb-4">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ledger as $entry): ?>
                <tr>
                    <td><?= $entry['date'] ?></td>
                    <td><?= esc($entry['description']) ?></td>
                    <td><?= number_format($entry['debit'], 2) ?></td>
                    <td><?= number_format($entry['credit'], 2) ?></td>
                    <td><?= number_format($entry['balance'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>