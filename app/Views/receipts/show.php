<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-4xl mx-auto px-4 py-6">
    <style>
        /* Ensure Print prints only the receipt container */
        @media print {
            body * {
                visibility: hidden !important;
            }

            #receipt-html,
            #receipt-html * {
                visibility: visible !important;
            }

            #receipt-html {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Receipt</h1>
            <p class="text-xs text-gray-500">Invoice #<?= esc($sale['invoice_no'] ?? '') ?> · <?= esc(date('d M Y h:i A', strtotime($sale['created_at'] ?? date('Y-m-d H:i:s')))) ?></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= site_url('sales/new') ?>" accesskey="n" title="New Sale (Alt+Shift+N)" class="btn btn-muted btn-sm"><i class="fas fa-plus mr-1"></i>New Sale</a>
            <a href="<?= site_url('sales/edit/' . ($sale['id'] ?? 0)) ?>" accesskey="e" title="Edit Sale (Alt+Shift+E)" class="btn btn-warning btn-sm"><i class="fas fa-edit mr-1"></i>Edit Sale</a>
            <a href="<?= site_url('sales') ?>" accesskey="l" title="Sales List (Alt+Shift+L)" class="btn btn-secondary btn-sm"><i class="fas fa-list mr-1"></i>Sales</a>
            <!-- <a href="<?= site_url('receipts/generate/' . ($sale['id'] ?? 0) . '?output=pdf') ?>" target="_blank" accesskey="d" title="Open PDF (Alt+Shift+D)" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf mr-1"></i>PDF</a> -->
            <button type="button" accesskey="p" title="Print Receipt (Alt+Shift+P)" onclick="printReceiptOnly()" class="btn btn-primary btn-sm"><i class="fas fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <!-- Keyboard shortcuts hint banner (always visible) -->
    <div id="receipt-shortcuts-hint" class="mb-4 bg-blue-50 border border-blue-200 text-blue-900 px-3 py-2 rounded text-xs flex items-center">
        <i class="fas fa-keyboard mr-2"></i>
        <div>
            <span class="font-semibold">Shortcuts:</span>
            <span class="ml-1">
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Alt+P</kbd> Print
                <span class="mx-1">·</span>
                <!-- <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Alt+D</kbd> PDF
                <span class="mx-1">·</span> -->
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Alt+E</kbd> Edit Sale
                <span class="mx-1">·</span>
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Alt+N</kbd> New Sale
                <span class="mx-1">·</span>
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Alt+L</kbd> Sales List
                <span class="mx-1">·</span>
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Alt+B</kbd> Back
            </span>
        </div>
    </div>

    <div class="bg-white shadow rounded p-4 print:shadow-none print:p-0">
        <!-- Render the generated receipt HTML -->
        <div id="receipt-html">
            <?= $receiptHtml ?>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a id="back-pos-link" href="<?= site_url('sales/new') ?>" accesskey="b" title="Back (Ctrl+Alt+B)" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i>Back to POS</a>
    </div>
</div>

<script>
    // Print only the receipt HTML using a new window (avoids printing the entire layout)
    function printReceiptOnly() {
        try {
            const html = document.getElementById('receipt-html').innerHTML;
            const w = window.open('', 'PRINT', 'height=800,width=800');
            if (!w) {
                // fallback to page print with @media print rules
                window.print();
                return;
            }
            w.document.write('<html><head><title>Receipt</title>');
            w.document.write('<style>html,body{margin:0;padding:8px;font-family:Arial,Helvetica,sans-serif;} table{width:100%;border-collapse:collapse;} td,th{font-size:11px;padding:2px;} .text-right{text-align:right} .text-center{text-align:center}</style>');
            w.document.write('</head><body>');
            w.document.write('<div id="receipt">' + html + '</div>');
            w.document.write('</body></html>');
            w.document.close();
            w.focus();
            // Some browsers need a slight delay
            setTimeout(function() {
                w.print();
                w.close();
            }, 100);
        } catch (e) {
            window.print();
        }
    }

    // Keyboard shortcuts (avoid triggering while typing)
    document.addEventListener('keydown', function(e) {
        const tag = (document.activeElement && document.activeElement.tagName) || '';
        const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag);
        if (isTyping) return;
        // Ctrl+Alt+P -> Print
        if ((e.ctrlKey && e.altKey && !e.shiftKey && (e.key === 'p' || e.key === 'P'))) {
            e.preventDefault();
            printReceiptOnly();
        }
        // Ctrl+Alt+D -> PDF
        if ((e.ctrlKey && e.altKey && !e.shiftKey && (e.key === 'd' || e.key === 'D'))) {
            e.preventDefault();
            const link = document.querySelector('a[accesskey="d"]');
            if (link) link.click();
        }
        // Ctrl+Alt+N -> New Sale
        if ((e.ctrlKey && e.altKey && !e.shiftKey && (e.key === 'n' || e.key === 'N'))) {
            e.preventDefault();
            const link = document.querySelector('a[accesskey="n"]');
            if (link) window.location.href = link.href;
        }
        // Ctrl+Alt+E -> Edit Sale
        if ((e.ctrlKey && e.altKey && !e.shiftKey && (e.key === 'e' || e.key === 'E'))) {
            e.preventDefault();
            const link = document.querySelector('a[accesskey="e"]');
            if (link) window.location.href = link.href;
        }

        // Ctrl+Alt+L -> Sales List
        if ((e.ctrlKey && e.altKey && !e.shiftKey && (e.key === 'l' || e.key === 'L'))) {
            e.preventDefault();
            const link = document.querySelector('a[accesskey="l"]');
            if (link) window.location.href = link.href;
        }
        // Ctrl+Alt+B -> Back
        if ((e.ctrlKey && e.altKey && !e.shiftKey && (e.key === 'b' || e.key === 'B'))) {
            e.preventDefault();
            const back = document.getElementById('back-pos-link') || document.querySelector('a[accesskey="b"]');
            if (back && back.href) {
                window.location.href = back.href;
            } else {
                window.history.back();
            }
        }
    });
    // Hint banner is always visible; no persistence needed
</script>

<?= $this->endSection() ?>