<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-6xl mx-auto px-4 py-6">
    <style>
        /* Print only the iframe content */
        @media print {

            /* Hide everything on the parent page */
            body * {
                visibility: hidden !important;
            }

            /* Show only iframe and its contents */
            #preview,
            #preview * {
                visibility: visible !important;
            }

            /* Position iframe to fill page without padding */
            #preview {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }

            /* Remove all margins and padding from body and html */
            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: 100% !important;
            }

            /* Hide page wrapper padding */
            .max-w-4xl {
                margin: 0 !important;
                padding: 0 !important;
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
            <button type="button" accesskey="p" title="Print Receipt (Ctrl+P)" onclick="printReceiptOnly()" class="btn btn-primary btn-sm"><i class="fas fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <!-- Keyboard shortcuts hint banner (always visible) -->
    <div id="receipt-shortcuts-hint" class="mb-4 bg-blue-50 border border-blue-200 text-blue-900 px-3 py-2 rounded text-xs flex items-center">
        <i class="fas fa-keyboard mr-2"></i>
        <div>
            <span class="font-semibold">Shortcuts:</span>
            <span class="ml-1">
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+P</kbd> Print
                <span class="mx-1">·</span>
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Shift+P</kbd> Browser Print
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
        <!-- Receipt preview in iframe to isolate CSS -->
        <iframe id="preview" title="Receipt Preview" style="width: 100%; height: 600px; border: 1px solid #ddd; background: white; border-radius: 4px;"></iframe>
    </div>

    <div class="mt-4 text-center">
        <a id="back-pos-link" href="<?= site_url('sales/new') ?>" accesskey="b" title="Back (Ctrl+Alt+B)" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i>Back to POS</a>
    </div>
</div>

<script>
    // Inject receipt HTML into iframe to isolate styles
    (function injectReceiptIntoFrame() {
        const frame = document.getElementById('preview');
        if (!frame) return;
        const doc = frame.contentDocument || frame.contentWindow?.document;
        if (!doc) return;

        const rawHTML = <?= json_encode($receiptHtml ?? '') ?>;

        // If a full document is present, extract <head> styles and <body> content; else use as-is
        let content = rawHTML || '';
        let headExtra = '';
        const fullDocRe = new RegExp('(<\\s*!doctype)|(</\\s*html>)|(</\\s*body>)|(</\\s*head>)', 'i');
        if (fullDocRe.test(content)) {
            try {
                const TMP = document.implementation.createHTMLDocument('tmp');
                TMP.documentElement.innerHTML = content;
                // Collect styles and external stylesheets from head (if any)
                if (TMP.head) {
                    headExtra = Array.from(TMP.head.querySelectorAll('style,link[rel="stylesheet"]'))
                        .map(n => n.outerHTML)
                        .join('');
                }
                content = TMP.body ? TMP.body.innerHTML : content;
            } catch (_) {
                /* keep content as-is */
            }
        }

        doc.open();
        doc.write('<!doctype html><html><head>');
        doc.write('<meta charset="utf-8">');
        doc.write('<meta name="viewport" content="width=device-width, initial-scale=1">');
        doc.write('<style>');
        doc.write('html,body{margin:0;padding:8px;font-family:Arial,Helvetica,sans-serif;color:#111;}');
        doc.write('table{width:100%;border-collapse:collapse;}');
        doc.write('td,th{font-size:11px;padding:2px;vertical-align:top;}');
        doc.write('.text-right{text-align:right}.text-center{text-align:center}');
        doc.write('@media print { @page { margin: 0;!important } html,body{padding:0!important;margin:0!important;overflow:hidden!important;} #invoice-POS{ width:80mm !important; max-width:80mm !important; margin:0 auto !important; } }');
        doc.write('</style>');
        if (headExtra) doc.write(headExtra);
        doc.write('</head><body>');
        if (content) doc.write(content);
        doc.write('</body></html>');
        doc.close();

        // Mark iframe as ready after content is loaded
        frame.dataset.ready = 'true';

        // Autosize iframe height to content
        function resize() {
            try {
                const h = Math.max(
                    doc.body?.scrollHeight || 0,
                    doc.documentElement?.scrollHeight || 0
                );
                if (h) frame.style.height = (h + 16) + 'px';
            } catch (_) {}
        }
        frame.addEventListener('load', resize);
        setTimeout(resize, 80);
    })();

    // Print the iframe (isolated)
    function printReceiptOnly() {
        const frame = document.getElementById('preview');
        if (!frame) {
            console.error('Receipt frame not found');
            window.print();
            return;
        }

        // Wait for iframe to be ready if needed
        if (!frame.dataset.ready) {
            console.log('Waiting for iframe to load...');
            setTimeout(printReceiptOnly, 100);
            return;
        }

        if (frame.contentWindow) {
            try {
                // Focus and print the iframe
                frame.contentWindow.focus();
                setTimeout(function() {
                    frame.contentWindow.print();
                }, 50);
                return;
            } catch (err) {
                console.error('Iframe print failed:', err);
            }
        }
        // Fallback to window print if iframe fails
        console.warn('Falling back to window.print()');
        window.print();
    }

    // Keyboard shortcuts (avoid triggering while typing)
    // Use capture phase (true) to intercept before browser default
    document.addEventListener('keydown', function(e) {
        const tag = (document.activeElement && document.activeElement.tagName) || '';
        const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag);
        if (isTyping) return;

        // Ctrl+P -> Print iframe only (prevents parent page print with scrollbars)
        if (e.ctrlKey && !e.altKey && !e.shiftKey && (e.key === 'p' || e.key === 'P')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const frame = document.getElementById('preview');
            if (frame && frame.contentWindow && frame.dataset.ready) {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } else {
                printReceiptOnly();
            }
            return false;
        }
        // Ctrl+Shift+P -> Browser print preview (parent page with @media print rules)
        if (e.ctrlKey && e.shiftKey && !e.altKey && (e.key === 'p' || e.key === 'P')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            window.print();
            return false;
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