<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
helper('permission');
$canEditLinePrice    = can('sales.edit_price');
$canEditLineDiscount = can('sales.edit_discount');
$currency            = session()->get('currency_symbol') ?? '$';
?>

<style>
    /* ==========================================================================
   BLINK/FOODINN-STYLE POS — PREMIUM TOUCH DESIGN
   ========================================================================== */
    :root {
        --pos-bg: #0f172a;
        --pos-panel: #1e293b;
        --pos-panel-2: #273449;
        --pos-border: #334155;
        --pos-text: #f1f5f9;
        --pos-muted: #94a3b8;
        --pos-accent: #22c55e;
        --pos-accent-2: #16a34a;
        --pos-blue: #3b82f6;
        --pos-amber: #f59e0b;
        --pos-red: #ef4444;
        --pos-radius: 16px;
    }

    * {
        -webkit-tap-highlight-color: transparent;
    }

    .blink-app {
        height: calc(100vh - 64px);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--pos-text);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: var(--pos-bg);
    }

    /* ================= TOP BAR ================= */
    .blink-top {
        height: 64px;
        flex-shrink: 0;
        background: var(--pos-panel);
        border-bottom: 1px solid var(--pos-border);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 16px;
    }

    .blink-brand {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .blink-brand-logo {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--pos-accent), var(--pos-accent-2));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
        box-shadow: 0 4px 12px rgba(34, 197, 94, .35);
    }

    .blink-brand-title {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: -.2px;
    }

    .blink-brand-sub {
        font-size: 11px;
        color: var(--pos-muted);
    }

    .blink-top-search {
        flex: 1;
        max-width: 520px;
        height: 44px;
        border-radius: 12px;
        background: var(--pos-panel-2);
        border: 1px solid var(--pos-border);
        display: flex;
        align-items: center;
        padding: 0 14px;
        gap: 10px;
        transition: .2s;
    }

    .blink-top-search:focus-within {
        border-color: var(--pos-blue);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
    }

    .blink-top-search i {
        color: var(--pos-muted);
        font-size: 15px;
    }

    .blink-top-search input {
        flex: 1;
        background: transparent;
        border: none;
        outline: none;
        color: var(--pos-text);
        font-size: 15px;
        font-weight: 500;
    }

    .blink-top-search input::placeholder {
        color: var(--pos-muted);
    }

    .blink-top-btn {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--pos-panel-2);
        border: 1px solid var(--pos-border);
        color: var(--pos-text);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        transition: .15s;
    }

    .blink-top-btn:hover {
        background: var(--pos-border);
    }

    .blink-top-btn:active {
        transform: scale(.94);
    }

    .blink-user {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 12px 6px 6px;
        background: var(--pos-panel-2);
        border-radius: 12px;
        border: 1px solid var(--pos-border);
    }

    .blink-user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
    }

    .blink-user-name {
        font-size: 13px;
        font-weight: 700;
    }

    .blink-user-role {
        font-size: 10px;
        color: var(--pos-muted);
    }

    /* ================= MAIN LAYOUT ================= */
    .blink-main {
        flex: 1;
        display: flex;
        overflow: hidden;
    }

    /* ---- LEFT: Categories rail ---- */
    .blink-cats {
        width: 132px;
        flex-shrink: 0;
        background: var(--pos-panel);
        border-right: 1px solid var(--pos-border);
        overflow-y: auto;
        padding: 12px 8px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .blink-cat {
        padding: 14px 8px;
        border-radius: 14px;
        background: var(--pos-panel-2);
        border: 1px solid transparent;
        color: var(--pos-muted);
        cursor: pointer;
        transition: .15s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        text-align: center;
        user-select: none;
    }

    .blink-cat i {
        font-size: 22px;
    }

    .blink-cat span {
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.2;
    }

    .blink-cat:hover {
        background: var(--pos-border);
        color: var(--pos-text);
    }

    .blink-cat.active {
        background: linear-gradient(135deg, var(--pos-accent), var(--pos-accent-2));
        color: #fff;
        border-color: transparent;
        box-shadow: 0 6px 16px rgba(34, 197, 94, .35);
    }

    .blink-cat.active i {
        transform: scale(1.1);
    }

    /* ---- CENTER: Product grid ---- */
    .blink-products-wrap {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        background:
            radial-gradient(1200px 600px at 100% 0%, rgba(34, 197, 94, .05), transparent 60%),
            radial-gradient(800px 400px at 0% 100%, rgba(59, 130, 246, .05), transparent 60%),
            var(--pos-bg);
    }

    .blink-products-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .blink-products-title {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.3px;
    }

    .blink-products-count {
        font-size: 12px;
        color: var(--pos-muted);
        font-weight: 600;
    }

    .blink-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
        gap: 10px;
    }

    .blink-card {
        background: linear-gradient(145deg, #334155, #29394f);
        border: 1px solid #475569;
        border-radius: var(--pos-radius);
        padding: 12px;
        cursor: pointer;
        transition: .18s;
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 128px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .28), inset 0 1px 0 rgba(255, 255, 255, .06);
    }

    .blink-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(34, 197, 94, .0), rgba(34, 197, 94, .08));
        opacity: 0;
        transition: .2s;
    }

    .blink-card:hover {
        transform: translateY(-3px);
        border-color: var(--pos-accent);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .35);
    }

    .blink-card:hover::before {
        opacity: 1;
    }

    .blink-card:active {
        transform: scale(.97);
    }

    .blink-card.out {
        opacity: .55;
    }

    .blink-card.out:hover {
        transform: none;
    }

    .blink-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(59, 130, 246, .35), rgba(139, 92, 246, .35));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #bfdbfe;
    }

    .blink-card-name {
        font-size: 14px;
        font-weight: 700;
        color: #f8fafc;
        line-height: 1.3;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blink-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .blink-card-price {
        font-size: 16px;
        font-weight: 900;
        color: var(--pos-accent);
        letter-spacing: -.3px;
    }

    .blink-card-stock {
        font-size: 10.5px;
        color: var(--pos-muted);
        font-weight: 600;
    }

    .blink-card-add {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: var(--pos-accent);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        opacity: 0;
        transform: scale(.7);
        transition: .2s;
        box-shadow: 0 4px 12px rgba(34, 197, 94, .4);
    }

    .blink-card:hover .blink-card-add {
        opacity: 1;
        transform: scale(1);
    }

    .blink-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 80px 20px;
        color: var(--pos-muted);
        text-align: center;
    }

    .blink-empty i {
        font-size: 56px;
        margin-bottom: 16px;
        opacity: .35;
    }

    .blink-empty h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--pos-text);
        margin: 0 0 6px;
    }

    .blink-empty p {
        font-size: 13px;
        margin: 0;
    }

    /* ================= RIGHT: CART SIDEBAR ================= */
    .blink-side {
        width: 470px;
        flex-shrink: 0;
        background: var(--pos-panel);
        border-left: 1px solid var(--pos-border);
        display: flex;
        flex-direction: column;
        overflow-x: hidden;
        overflow-y: auto;
        min-height: 0;
    }

    .blink-side-head {
        padding: 10px 16px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-bottom: 1px solid rgba(255, 255, 255, .2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }

    .blink-side-title {
        font-size: 15px;
        font-weight: 800;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .blink-side-badge {
        background: #fff;
        color: #4f46e5;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 20px;
    }

    .blink-side-clear {
        background: rgba(255, 255, 255, .18);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .4);
        height: 36px;
        padding: 0 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: .15s;
    }

    .blink-side-clear:hover {
        background: rgba(239, 68, 68, .75);
    }

    .blink-side-clear:active {
        transform: scale(.95);
    }

    /* Cart items list */
    .blink-cart {
        flex: 1 1 0;
        min-height: 200px;
        overflow-y: auto;
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        background: #e8edf5;
    }

    .blink-cart-item {
        flex-shrink: 0;
        background: #fff;
        color: #0f172a;
        border: 1px solid #d3dcea;
        border-radius: 10px;
        padding: 6px 8px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
        transition: background .4s, border-color .4s;
    }

    .blink-cart-item:nth-child(even) {
        background: #f6f8fc;
    }

    .blink-cart-item.flash {
        background: #bbf7d0;
        border-color: var(--pos-accent-2);
    }

    .blink-cart-item.is-gift {
        background: #fffbeb;
        border-color: #fcd34d;
    }

    .blink-ci-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .blink-ci-idx {
        width: 18px;
        flex-shrink: 0;
        text-align: center;
        font-size: 11px;
        font-weight: 800;
        color: #94a3b8;
    }

    .blink-ci-main {
        flex: 1;
        min-width: 0;
    }

    .blink-ci-main[data-pad] {
        cursor: pointer;
    }

    .blink-ci-name {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .blink-ci-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        line-height: 1.3;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
    }

    .blink-ci-meta .unit {
        color: #4338ca;
        font-weight: 800;
    }

    .blink-ci-meta .unit.editable {
        background: #eef2ff;
        border: 1px solid #c7d2fe;
        border-radius: 6px;
        padding: 0 6px;
    }

    .blink-ci-meta .disc {
        color: #b45309;
        font-weight: 700;
    }

    .blink-ci-meta .imei {
        color: #b45309;
        background: transparent;
        border: 1px dashed #d97706;
        border-radius: 6px;
        padding: 0 6px;
        font-size: 10.5px;
        cursor: pointer;
    }

    .blink-ci-remove {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        flex-shrink: 0;
        background: #fee2e2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
        transition: .15s;
        border: none;
    }

    .blink-ci-remove:hover {
        background: #fecaca;
    }

    .blink-ci-remove:disabled {
        opacity: .4;
        cursor: not-allowed;
    }

    /* Compact quantity stepper */
    .blink-qty {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        background: #f1f5f9;
        border-radius: 9px;
        padding: 2px;
        border: 1px solid #cbd5e1;
    }

    .blink-qty button {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: none;
        background: #e0e7ff;
        color: #3730a3;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: .15s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .blink-qty button:hover {
        background: #c7d2fe;
    }

    .blink-qty button:active {
        transform: scale(.9);
    }

    .blink-qty button:disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    .blink-qty input {
        width: 42px;
        height: 30px;
        text-align: center;
        background: transparent;
        border: none;
        outline: none;
        color: #0f172a;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        -moz-appearance: textfield;
    }

    .blink-qty input::-webkit-outer-spin-button,
    .blink-qty input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .blink-ci-price {
        width: 78px;
        flex-shrink: 0;
        text-align: right;
        font-size: 14px;
        font-weight: 900;
        color: #15803d;
        letter-spacing: -.3px;
        cursor: pointer;
    }

    /* Empty cart */
    .blink-cart-empty {
        flex: 1 1 0;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: #e8edf5;
        color: #64748b;
        text-align: center;
    }

    .blink-cart-empty i {
        font-size: 52px;
        margin-bottom: 14px;
        opacity: .35;
    }

    .blink-cart-empty h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px;
    }

    .blink-cart-empty p {
        font-size: 12px;
        margin: 0;
    }

    /* Totals block */
    .blink-totals {
        flex-shrink: 0;
        padding: 8px 16px;
        background: var(--pos-panel);
        border-top: 1px solid var(--pos-border);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2px 10px;
    }

    .blink-trow {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        font-size: 11px;
        color: var(--pos-muted);
        font-weight: 600;
    }

    .blink-trow span:last-child {
        font-size: 13px;
    }

    .blink-trow span:last-child {
        color: var(--pos-text);
        font-weight: 700;
    }

    .blink-trow.discount span:last-child {
        color: var(--pos-amber);
    }

    .blink-trow.tax span:last-child {
        color: #86efac;
    }

    .blink-trow.total {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding-top: 4px;
        margin-top: 2px;
        border-top: 1px dashed var(--pos-border);
        font-size: 14px;
        color: var(--pos-text);
        font-weight: 800;
    }

    .blink-trow.total span:last-child {
        font-size: 22px;
        font-weight: 900;
        color: var(--pos-accent);
        letter-spacing: -.6px;
    }

    /* Tendered + quick cash */
    .blink-tender {
        padding: 8px 16px;
        background: var(--pos-panel);
        border-top: 1px solid var(--pos-border);
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        flex-shrink: 0;
    }

    .blink-tender-input {
        grid-column: 1;
        grid-row: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--pos-panel-2);
        border: 1px solid var(--pos-border);
        border-radius: 12px;
        padding: 0 12px;
        height: 44px;
        transition: .15s;
    }

    .blink-tender-input:focus-within {
        border-color: var(--pos-accent);
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .15);
    }

    .blink-tender-input .sym {
        font-size: 18px;
        font-weight: 800;
        color: var(--pos-accent);
    }

    .blink-tender-input input {
        flex: 1;
        min-width: 0;
        background: transparent;
        border: none;
        outline: none;
        color: var(--pos-text);
        font-size: 18px;
        font-weight: 900;
        letter-spacing: -.5px;
        -moz-appearance: textfield;
    }

    .blink-tender-input input::-webkit-outer-spin-button,
    .blink-tender-input input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .blink-quickcash {
        grid-column: 1 / -1;
        grid-row: 2;
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(58px, 1fr);
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .blink-quickcash button {
        height: 38px;
        border-radius: 10px;
        background: var(--pos-panel-2);
        border: 1px solid var(--pos-border);
        color: var(--pos-text);
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: .15s;
    }

    .blink-quickcash button:hover {
        background: var(--pos-accent);
        color: #fff;
        border-color: var(--pos-accent);
    }

    .blink-quickcash button:active {
        transform: scale(.94);
    }

    .blink-change {
        grid-column: 2;
        grid-row: 1;
        min-width: 0;
        height: 44px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
        padding: 0 12px;
        border-radius: 12px;
        background: rgba(34, 197, 94, .12);
        border: 1px solid rgba(34, 197, 94, .3);
        font-size: 12px;
        font-weight: 700;
    }

    .blink-change.due {
        background: rgba(239, 68, 68, .12);
        border-color: rgba(239, 68, 68, .3);
    }

    .blink-change .amt {
        font-size: 16px;
        font-weight: 900;
        color: #86efac;
    }

    .blink-change.due .amt {
        color: #fca5a5;
    }

    /* Action buttons */
    .blink-actions {
        padding: 8px 16px 12px;
        background: var(--pos-panel);
        border-top: 1px solid var(--pos-border);
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-shrink: 0;
    }

    .blink-actions-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .blink-actions-row .blink-btn {
        height: 42px;
    }

    .blink-btn {
        height: 50px;
        border-radius: 14px;
        border: none;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: .15s;
        letter-spacing: -.2px;
    }

    .blink-btn:active {
        transform: scale(.97);
    }

    .blink-btn.draft {
        background: var(--pos-amber);
        color: #1e293b;
        box-shadow: 0 4px 12px rgba(245, 158, 11, .3);
    }

    .blink-btn.draft:hover {
        background: #fbbf24;
    }

    .blink-btn.clear {
        background: var(--pos-panel-2);
        color: var(--pos-muted);
        border: 1px solid var(--pos-border);
    }

    .blink-btn.clear:hover {
        background: var(--pos-border);
        color: var(--pos-text);
    }

    .blink-btn.pay {
        height: 64px;
        font-size: 17px;
        font-weight: 900;
        background: linear-gradient(135deg, var(--pos-accent), var(--pos-accent-2));
        color: #fff;
        box-shadow: 0 8px 24px rgba(34, 197, 94, .4);
    }

    .blink-btn.pay:hover {
        background: linear-gradient(135deg, #16a34a, #15803d);
    }

    .blink-btn.pay:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .blink-btn.pay i {
        font-size: 20px;
    }

    /* ================= MODALS ================= */
    .blink-modal {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--pos-text);
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .7);
        backdrop-filter: blur(4px);
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .blink-modal.open {
        display: flex;
    }

    .blink-modal-box {
        background: var(--pos-panel);
        border-radius: 20px;
        border: 1px solid var(--pos-border);
        max-width: 520px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 30px 60px rgba(0, 0, 0, .5);
        animation: blinkPop .22s ease-out;
    }

    @keyframes blinkPop {
        from {
            transform: scale(.94);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .blink-modal-head {
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--pos-border);
    }

    .blink-modal-head h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .blink-modal-close {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        background: var(--pos-panel-2);
        color: var(--pos-muted);
        cursor: pointer;
        font-size: 14px;
    }

    .blink-modal-body {
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .blink-field label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--pos-muted);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .blink-field input,
    .blink-field select,
    .blink-field textarea {
        width: 100%;
        background: var(--pos-panel-2);
        border: 1px solid var(--pos-border);
        border-radius: 12px;
        color: var(--pos-text);
        font-size: 14px;
        font-weight: 600;
        padding: 12px 14px;
        outline: none;
        transition: .15s;
        font-family: inherit;
    }

    .blink-field input:focus,
    .blink-field select:focus,
    .blink-field textarea:focus {
        border-color: var(--pos-blue);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
    }

    .blink-modal-foot {
        padding: 14px 20px;
        display: flex;
        gap: 10px;
        border-top: 1px solid var(--pos-border);
    }

    .blink-modal-foot .blink-btn {
        flex: 1;
        height: 48px;
    }

    /* Calculator-style keypad */
    .pad-summary {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 10px;
    }

    .pad-name {
        font-size: 14px;
        font-weight: 800;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pad-line {
        font-size: 15px;
        font-weight: 900;
        color: var(--pos-accent);
        white-space: nowrap;
    }

    .pad-tabs {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: 1fr;
        gap: 6px;
    }

    .pad-tab {
        height: 40px;
        border-radius: 10px;
        border: 1px solid var(--pos-border);
        background: var(--pos-panel-2);
        color: var(--pos-muted);
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .pad-tab.active {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-color: transparent;
        color: #fff;
    }

    .pad-display {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        height: 64px;
        padding: 0 16px;
        border-radius: 14px;
        background: #0b1220;
        border: 1px solid var(--pos-border);
    }

    .pad-display .lbl {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--pos-muted);
    }

    .pad-display .val {
        font-size: 32px;
        font-weight: 900;
        letter-spacing: -1px;
        color: #fff;
        font-variant-numeric: tabular-nums;
    }

    .pad-types {
        display: none;
        gap: 6px;
    }

    .pad-types.show {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .pad-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .pad-key {
        height: 58px;
        border-radius: 14px;
        border: 1px solid var(--pos-border);
        background: var(--pos-panel-2);
        color: var(--pos-text);
        font-size: 22px;
        font-weight: 800;
        cursor: pointer;
        transition: .1s;
    }

    .pad-key:active {
        transform: scale(.94);
        background: var(--pos-border);
    }

    .pad-key.fn {
        background: rgba(239, 68, 68, .15);
        color: #fca5a5;
    }

    .blink-kbd {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        background: var(--pos-panel-2);
        border: 1px solid var(--pos-border);
        font-family: monospace;
        font-size: 11px;
        font-weight: 700;
        color: var(--pos-text);
    }

    /* Toast */
    .blink-toast {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 120;
        background: var(--pos-panel);
        border: 1px solid var(--pos-border);
        border-radius: 14px;
        padding: 14px 18px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        max-width: 380px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, .4);
        animation: blinkSlide .25s ease-out;
    }

    @keyframes blinkSlide {
        from {
            transform: translateX(30px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .blink-toast.err {
        border-left: 4px solid var(--pos-red);
    }

    .blink-toast.ok {
        border-left: 4px solid var(--pos-accent);
    }

    .blink-toast i {
        font-size: 18px;
        margin-top: 1px;
    }

    .blink-toast.err i {
        color: var(--pos-red);
    }

    .blink-toast.ok i {
        color: var(--pos-accent);
    }

    .blink-toast p {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: var(--pos-text);
    }

    .blink-toast ul {
        margin: 4px 0 0;
        padding-left: 18px;
        color: #fca5a5;
        font-size: 12.5px;
    }

    /* Loader */
    .blink-loader {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .85);
        backdrop-filter: blur(4px);
        z-index: 200;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .blink-loader.open {
        display: flex;
    }

    .blink-loader-box {
        background: var(--pos-panel);
        border-radius: 20px;
        padding: 36px 40px;
        text-align: center;
        border: 1px solid var(--pos-border);
        box-shadow: 0 30px 60px rgba(0, 0, 0, .5);
        max-width: 360px;
    }

    .blink-spinner {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: 4px solid rgba(34, 197, 94, .2);
        border-top-color: var(--pos-accent);
        margin: 0 auto 20px;
        animation: blinkSpin .8s linear infinite;
    }

    @keyframes blinkSpin {
        to {
            transform: rotate(360deg);
        }
    }

    .blink-loader-box h3 {
        margin: 0 0 6px;
        font-size: 16px;
        font-weight: 800;
    }

    .blink-loader-box p {
        margin: 0;
        font-size: 13px;
        color: var(--pos-muted);
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--pos-border);
        border-radius: 8px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }

    /* Responsive */
    @media (max-width:1100px) {
        .blink-side {
            width: 400px;
        }

        .blink-cats {
            width: 110px;
        }
    }

    @media (max-width:820px) {
        .blink-app {
            height: auto;
            min-height: calc(100vh - 64px);
        }

        .blink-main {
            flex-direction: column;
        }

        .blink-cats {
            width: 100%;
            flex-direction: row;
            padding: 8px;
            overflow-x: auto;
        }

        .blink-cat {
            min-width: 88px;
            padding: 10px 6px;
        }

        .blink-cat i {
            font-size: 18px;
        }

        .blink-cat span {
            font-size: 10.5px;
        }

        .blink-side {
            width: 100%;
            overflow-y: visible;
        }

        .blink-cart {
            flex: 0 0 auto;
            min-height: 120px;
            max-height: 50vh;
        }
    }
</style>

<div class="blink-app">

    <!-- ===================== TOP BAR ===================== -->
    <div class="blink-top">
        <div class="blink-brand">
            <div class="blink-brand-logo"><i class="fas fa-bolt"></i></div>
            <div>
                <div class="blink-brand-title">QuickSale</div>
                <div class="blink-brand-sub">#<?= esc($invoiceNo) ?></div>
            </div>
        </div>

        <div class="blink-top-search">
            <i class="fas fa-search"></i>
            <input type="text" id="barcode-input" placeholder="Scan barcode or search product…" autofocus autocomplete="off">
        </div>

        <button type="button" class="blink-top-btn" id="openHelp" title="Help"><i class="fas fa-question"></i></button>
        <button type="button" class="blink-top-btn" id="openMore" title="More"><i class="fas fa-ellipsis-h"></i></button>

        <div class="blink-user">
            <div class="blink-user-avatar"><i class="fas fa-user"></i></div>
            <div>
                <div class="blink-user-name"><?= esc(session()->get('username') ?? lang('Sales.cashier')) ?></div>
                <div class="blink-user-role"><?= esc($userRole ?? 'Cashier') ?></div>
            </div>
        </div>
    </div>

    <!-- ===================== MAIN ===================== -->
    <div class="blink-main">

        <!-- Category rail -->
        <div class="blink-cats" id="categoryRail">
            <button type="button" class="blink-cat active" data-cat="all">
                <i class="fas fa-th"></i><span>All Items</span>
            </button>
            <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                    <button type="button" class="blink-cat" data-cat="<?= (int)$cat['id'] ?>">
                        <i class="fas fa-tag"></i><span><?= esc($cat['name']) ?></span>
                    </button>
            <?php endforeach;
            endif; ?>
        </div>

        <!-- Product grid -->
        <div class="blink-products-wrap" id="productsWrap">
            <div class="blink-products-head">
                <div>
                    <div class="blink-products-title" id="gridTitle">All Items</div>
                    <div class="blink-products-count" id="gridCount">Tap a product to add</div>
                </div>
            </div>
            <div class="blink-grid" id="productGrid"></div>
        </div>

        <!-- Cart sidebar -->
        <div class="blink-side">
            <div class="blink-side-head">
                <div class="blink-side-title">
                    <i class="fas fa-shopping-bag" style="color:#fde68a"></i>
                    Current Order
                    <span class="blink-side-badge" id="cartCount">0</span>
                </div>
                <button type="button" class="blink-side-clear" onclick="clearCart()">
                    <i class="fas fa-trash-alt"></i> Clear
                </button>
            </div>

            <div class="blink-cart" id="cartList"></div>

            <div class="blink-cart-empty" id="cartEmpty">
                <i class="fas fa-shopping-bag"></i>
                <h3>No items yet</h3>
                <p>Tap a product to start</p>
            </div>

            <!-- Totals -->
            <div class="blink-totals">
                <div class="blink-trow"><span>Subtotal</span><span id="subDisplay"><?= $currency ?>0.00</span></div>
                <div class="blink-trow discount"><span>Discount</span><span id="discDisplay">-<?= $currency ?>0.00</span></div>
                <div class="blink-trow tax"><span>Tax (<span id="taxRateLabel"><?= $taxRate ?></span>%)</span><span id="taxDisplay"><?= $currency ?>0.00</span></div>
                <div class="blink-trow total"><span>Total</span><span id="totalDisplay"><?= $currency ?>0.00</span></div>
            </div>

            <!-- Tender -->
            <div class="blink-tender">
                <div class="blink-tender-input">
                    <span class="sym"><?= $currency ?></span>
                    <input type="number" id="tenderedAmountInput" placeholder="Cash tendered" step="0.01" min="0">
                </div>
                <div class="blink-quickcash" id="quickCash">
                    <?php foreach ([5, 10, 20, 50, 100, 200, 500, 1000] as $amt): ?>
                        <button type="button" data-amt="<?= $amt ?>">+<?= $amt ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="blink-change" id="changeBox">
                    <span>Change</span>
                    <span class="amt" id="changeDisplay"><?= $currency ?>0.00</span>
                </div>
                <div class="blink-change due" id="dueBox" style="display:none">
                    <span>Due</span>
                    <span class="amt" id="dueDisplay"><?= $currency ?>0.00</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="blink-actions">
                <div class="blink-actions-row">
                    <button type="button" class="blink-btn clear" id="btnMore">
                        <i class="fas fa-cog"></i> Options
                    </button>
                    <button type="button" class="blink-btn draft" id="saveDraftBtn">
                        <i class="fas fa-bookmark"></i> Draft
                    </button>
                </div>
                <button type="button" class="blink-btn pay" id="completeSubmitBtn">
                    <i class="fas fa-check-circle"></i> Charge <span id="chargeAmount"><?= $currency ?>0.00</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================== HELP MODAL ===================== -->
<div class="blink-modal" id="helpModal">
    <div class="blink-modal-box">
        <div class="blink-modal-head">
            <h3><i class="fas fa-keyboard" style="color:var(--pos-blue)"></i> Keyboard Shortcuts</h3>
            <button type="button" class="blink-modal-close" data-close="helpModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="blink-modal-body">
            <div class="blink-field">
                <label>Navigation</label>
                <div style="display:grid; gap:8px; font-size:13px">
                    <div style="display:flex; justify-content:space-between"><span>Focus search</span><span class="blink-kbd">F1</span></div>
                    <div style="display:flex; justify-content:space-between"><span>Open customer</span><span class="blink-kbd">F3</span></div>
                    <div style="display:flex; justify-content:space-between"><span>Open tendered</span><span class="blink-kbd">F6</span></div>
                    <div style="display:flex; justify-content:space-between"><span>Tax rate</span><span class="blink-kbd">F7</span></div>
                </div>
            </div>
            <div class="blink-field">
                <label>Cart</label>
                <div style="display:grid; gap:8px; font-size:13px">
                    <div style="display:flex; justify-content:space-between"><span>Increase last item</span><span class="blink-kbd">+</span></div>
                    <div style="display:flex; justify-content:space-between"><span>Decrease last item</span><span class="blink-kbd">-</span></div>
                    <div style="display:flex; justify-content:space-between"><span>Remove last item</span><span class="blink-kbd">Del</span></div>
                    <div style="display:flex; justify-content:space-between"><span>Clear cart</span><span class="blink-kbd">F12</span></div>
                </div>
            </div>
            <div class="blink-field">
                <label>Checkout</label>
                <div style="display:grid; gap:8px; font-size:13px">
                    <div style="display:flex; justify-content:space-between"><span>Save draft</span><span class="blink-kbd">F5</span></div>
                    <div style="display:flex; justify-content:space-between"><span>Complete sale</span><span class="blink-kbd">F9</span></div>
                </div>
            </div>
        </div>
        <div class="blink-modal-foot">
            <button type="button" class="blink-btn clear" data-close="helpModal">Close</button>
        </div>
    </div>
</div>

<!-- ===================== OPTIONS MODAL ===================== -->
<div class="blink-modal" id="optionsModal">
    <div class="blink-modal-box" style="max-width:640px">
        <div class="blink-modal-head">
            <h3><i class="fas fa-cog" style="color:var(--pos-blue)"></i> Order Options</h3>
            <button type="button" class="blink-modal-close" data-close="optionsModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="blink-modal-body" id="optionsForm"></div>
        <div class="blink-modal-foot">
            <button type="button" class="blink-btn clear" data-close="optionsModal">Cancel</button>
            <button type="button" class="blink-btn draft" id="optionsApply"><i class="fas fa-check"></i> Apply</button>
        </div>
    </div>
</div>

<!-- ===================== KEYPAD MODAL ===================== -->
<div class="blink-modal" id="padModal">
    <div class="blink-modal-box" style="max-width:380px">
        <div class="blink-modal-head">
            <h3><i class="fas fa-calculator" style="color:#a78bfa"></i> Edit Item</h3>
            <button type="button" class="blink-modal-close" data-close="padModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="blink-modal-body" style="gap:10px">
            <div class="pad-summary">
                <div class="pad-name" id="padName"></div>
                <div class="pad-line" id="padLine"></div>
            </div>
            <div class="pad-tabs" id="padTabs"></div>
            <div class="pad-display">
                <span class="lbl" id="padLabel"></span>
                <span class="val" id="padValue">0</span>
            </div>
            <div class="pad-types" id="padTypes">
                <button type="button" class="pad-tab" data-ptype="fixed"><?= esc($currency) ?></button>
                <button type="button" class="pad-tab" data-ptype="percentage">%</button>
            </div>
            <div class="pad-grid" id="padGrid">
                <?php foreach (['7', '8', '9', '4', '5', '6', '1', '2', '3', '.', '0'] as $k): ?>
                    <button type="button" class="pad-key" data-key="<?= $k ?>"><?= $k ?></button>
                <?php endforeach; ?>
                <button type="button" class="pad-key fn" data-key="back"><i class="fas fa-backspace"></i></button>
            </div>
        </div>
        <div class="blink-modal-foot">
            <button type="button" class="blink-btn clear" data-key="clear">Clear</button>
            <button type="button" class="blink-btn pay" id="padApply"><i class="fas fa-check"></i> Apply</button>
        </div>
    </div>
</div>

<!-- ===================== IMEI MODAL ===================== -->
<div class="blink-modal" id="imeiModal">
    <div class="blink-modal-box" style="max-width:520px">
        <div class="blink-modal-head">
            <h3><i class="fas fa-mobile-alt" style="color:var(--pos-amber)"></i> <span id="imeiTitle">Select IMEI</span></h3>
            <button type="button" class="blink-modal-close" data-close="imeiModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="blink-modal-body">
            <div class="blink-field">
                <input type="text" id="imeiSearch" placeholder="Filter IMEI…" autocomplete="off">
            </div>
            <div id="imeiHint" style="font-size:12px; color:var(--pos-muted)"></div>
            <div id="imeiList" style="display:flex; flex-direction:column; gap:6px; max-height:320px; overflow-y:auto"></div>
        </div>
        <div class="blink-modal-foot">
            <button type="button" class="blink-btn clear" data-close="imeiModal">Cancel</button>
            <button type="button" class="blink-btn draft" id="imeiApply"><i class="fas fa-check"></i> Apply</button>
        </div>
    </div>
</div>

<!-- ===================== CUSTOMER MODAL ===================== -->
<div class="blink-modal" id="customerModal">
    <div class="blink-modal-box" style="max-width:600px">
        <div class="blink-modal-head">
            <h3><i class="fas fa-user" style="color:var(--pos-accent)"></i> Select Customer</h3>
            <button type="button" class="blink-modal-close" data-close="customerModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="blink-modal-body">
            <div class="blink-field">
                <label>Search</label>
                <input type="text" id="customerSearch" placeholder="Type customer name…">
            </div>
            <div id="customerList" style="display:flex; flex-direction:column; gap:6px; max-height:340px; overflow-y:auto"></div>
        </div>
    </div>
</div>

<!-- ===================== LOADER ===================== -->
<div class="blink-loader" id="saleLoader">
    <div class="blink-loader-box">
        <div class="blink-spinner"></div>
        <h3>Processing Sale</h3>
        <p><?= !empty($zatcaEnabled) ? 'Submitting to ZATCA, please wait…' : 'Please wait…' ?></p>
    </div>
</div>

<!-- ===================== HIDDEN FORM ===================== -->
<form method="post" action="<?= site_url('sales/create') ?>" id="pos-form" style="display:none">
    <?= csrf_field() ?>
    <input type="hidden" name="invoice_no" value="<?= esc($invoiceNo) ?>">
    <?php if (isset($resumeDraftId)): ?>
        <input type="hidden" name="draft_id" value="<?= (int)$resumeDraftId ?>">
    <?php endif; ?>
    <input type="hidden" name="sale_date" id="f_sale_date" value="<?= date('Y-m-d\TH:i') ?>">
    <input type="hidden" name="customer_id" id="f_customer_id" value="">
    <input type="hidden" name="employee_id" id="f_employee_id" value="">
    <input type="hidden" name="description" id="f_description" value="">
    <input type="hidden" name="payment_type" id="f_payment_type" value="cash">
    <input type="hidden" name="payment_method" id="f_payment_method" value="cash">
    <input type="hidden" name="discount" id="f_discount" value="0">
    <input type="hidden" name="discount_type" id="f_discount_type" value="fixed">
    <input type="hidden" name="total_discount" id="f_total_discount" value="0">
    <input type="hidden" name="tax_rate" id="f_tax_rate" value="<?= $taxRate ?>">
    <input type="hidden" name="total_tax" id="f_total_tax" value="0">
    <input type="hidden" name="subtotal" id="f_subtotal" value="0">
    <input type="hidden" name="grand_total" id="f_grand_total" value="0">
    <input type="hidden" name="tendered_amount" id="f_tendered" value="0">
    <input type="hidden" name="change_amount" id="f_change" value="0">
    <input type="hidden" name="cart_data" id="f_cart_data" value="[]">
    <input type="hidden" name="draft" id="f_draft" value="0">
    <input type="hidden" name="misc_service" id="f_misc" value="0">
    <input type="hidden" name="misc_service_amount" id="f_misc_amt" value="0">
    <?php if (!empty($zatcaEnabled)): ?>
        <input type="hidden" name="zatca_invoice_type" id="f_zatca_type" value="<?= esc($zatcaDefaultInvoiceType ?? 'simplified') ?>">
    <?php endif; ?>
</form>

<script>
    /* ==========================================================================
   BLINK-STYLE POS — JAVASCRIPT
   ========================================================================== */
    (function() {
        'use strict';

        const CURRENCY = '<?= esc($currency, 'js') ?>';
        const TAX_RATE = <?= (float)$taxRate ?>;
        const CAN_EDIT_PRICE = <?= $canEditLinePrice ? 'true' : 'false' ?>;
        const CAN_EDIT_DISCOUNT = <?= $canEditLineDiscount ? 'true' : 'false' ?>;
        const IS_ADMIN = <?= strtolower((string)($userRole ?? '')) === 'admin' ? 'true' : 'false' ?>;
        const SHOW_DISC_TYPE = <?= !empty($salesShowDiscountType) ? 'true' : 'false' ?>;
        const ZATCA_ENABLED = <?= !empty($zatcaEnabled) ? 'true' : 'false' ?>;
        const ZATCA_DEFAULT = '<?= esc($zatcaDefaultInvoiceType ?? 'simplified') ?>';

        /* ---------- State ---------- */
        const state = {
            cart: [],
            products: [],
            categories: [],
            activeCat: 'all',
            customerId: 0,
            employeeId: <?= (int) ($preselectedEmployeeId ?? 0) ?>,
            paymentType: 'cash',
            paymentMethod: 'cash',
            description: '',
            zatcaType: ZATCA_DEFAULT,
            taxRate: TAX_RATE,
            taxInclusive: false
        };

        /* ---------- Money helpers ---------- */
        const fmt = (n) => CURRENCY + (parseFloat(n) || 0).toFixed(2);
        const esc = (s) => String(s ?? '').replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        } [m]));
        const isGift = (it) => Number(it?.is_gift || 0) === 1;
        const needImei = (v) => v === 1 || v === '1' || v === true || String(v || '').toLowerCase() === 'true' || String(v || '').toLowerCase() === 'yes';
        let productRequestId = 0;
        const normalizeProduct = (p) => ({
            id: p.id,
            name: p.name,
            code: p.code || '',
            price: parseFloat(p.price) || 0,
            stock: parseFloat(p.quantity) || 0,
            carton_size: parseFloat(p.carton_size) || 0,
            cost_price: parseFloat(p.cost_price) || 0,
            max_discount_value: parseFloat(p.max_discount_value) || 0,
            max_discount_type: p.max_discount_type || 'fixed',
            requires_imei: needImei(p.requires_imei),
            category_id: p.category_id || null
        });

        /* ---------- Toasts ---------- */
        function toast(msg, type = 'ok', extra = []) {
            const el = document.createElement('div');
            el.className = 'blink-toast ' + (type === 'err' ? 'err' : 'ok');
            const ico = type === 'err' ? 'fa-exclamation-triangle' : 'fa-check-circle';
            const list = extra.length ? `<ul>${extra.map(e=>`<li>${esc(e)}</li>`).join('')}</ul>` : '';
            el.innerHTML = `<i class="fas ${ico}"></i><div><p>${esc(msg)}</p>${list}</div>`;
            document.body.appendChild(el);
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transition = '.3s';
                setTimeout(() => el.remove(), 300);
            }, 3200);
        }

        function errorToast(list) {
            if (!list || !list.length) return;
            toast(list.length === 1 ? list[0] : `${list.length} issues found`, 'err', list);
        }

        /* ---------- Modal helpers ---------- */
        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }
        document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => closeModal(b.dataset.close)));
        document.querySelectorAll('.blink-modal').forEach(m => m.addEventListener('click', e => {
            if (e.target === m) m.classList.remove('open');
        }));

        /* ==========================================================================
           PRODUCT GRID
           ========================================================================== */
        async function loadProducts(cat = 'all', search = '') {
            const grid = document.getElementById('productGrid');
            const requestId = ++productRequestId;
            grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--pos-muted)"><div class="blink-spinner" style="margin:0 auto 12px"></div>Loading…</div>`;

            try {
                const params = new URLSearchParams();
                if (search) params.set('q', search);
                if (cat !== 'all') params.set('category_id', cat);
                params.set('context', 'sale');
                params.set('limit', '80');

                const res = await fetch('<?= site_url('api/products/search') ?>?' + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('http ' + res.status);
                const data = await res.json();
                if (requestId !== productRequestId) return;
                const list = Array.isArray(data) ? data : (data.results || data.data || []);

                state.products = list.map(normalizeProduct);
                renderProducts();
            } catch (err) {
                if (requestId !== productRequestId) return;
                grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--pos-muted)">
            <i class="fas fa-exclamation-triangle" style="font-size:32px;opacity:.4"></i>
            <p style="margin-top:12px">Failed to load products</p>
        </div>`;
            }
        }

        function renderProducts() {
            const grid = document.getElementById('productGrid');
            const count = state.products.length;
            document.getElementById('gridCount').textContent = count === 0 ? 'No products found' : `${count} item${count!==1?'s':''} • Tap to add`;

            if (count === 0) {
                grid.innerHTML = `<div class="blink-empty" style="grid-column:1/-1">
            <i class="fas fa-box-open"></i>
            <h3>No products here</h3>
            <p>Try another category or search term</p>
        </div>`;
                return;
            }

            grid.innerHTML = state.products.map(p => {
                const out = p.stock <= 0;
                return `<button type="button" class="blink-card${out?' out':''}" data-pid="${esc(p.id)}">
            <div class="blink-card-add"><i class="fas fa-plus"></i></div>
            <div class="blink-card-icon"><i class="fas fa-cube"></i></div>
            <div class="blink-card-name">${esc(p.name)}</div>
            <div class="blink-card-meta">
                <div class="blink-card-price">${fmt(p.price)}</div>
                <div class="blink-card-stock">${out?'Out':p.stock.toFixed(0)+' left'}</div>
            </div>
        </button>`;
            }).join('');
        }

        document.getElementById('productGrid').addEventListener('click', (e) => {
            const card = e.target.closest('.blink-card');
            if (!card) return;
            const product = state.products.find(x => String(x.id) === card.dataset.pid);
            if (product) addToCart(product);
        });

        /* Category rail */
        document.getElementById('categoryRail').addEventListener('click', (e) => {
            const btn = e.target.closest('.blink-cat');
            if (!btn) return;
            document.querySelectorAll('.blink-cat').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.activeCat = btn.dataset.cat;
            document.getElementById('gridTitle').textContent = btn.querySelector('span').textContent;
            loadProducts(state.activeCat);
        });

        /* ==========================================================================
           CART
           ========================================================================== */
        let flashKey = null;
        const rowKey = (it) => String(it.id) + (isGift(it) ? ':gift' : '');

        function addToCart(product) {
            let addedNew = false;
            flashKey = String(product.id);
            const existing = state.cart.find(it => !isGift(it) && String(it.id) === String(product.id));
            if (existing) {
                if (existing.quantity < existing.stock) {
                    existing.quantity += 1;
                } else {
                    toast(`Only ${existing.stock} available in stock`, 'err');
                    return;
                }
            } else {
                state.cart.unshift({
                    id: product.id,
                    name: product.name,
                    code: product.code,
                    price: product.price,
                    cost_price: product.cost_price,
                    stock: product.stock,
                    carton_size: product.carton_size,
                    max_discount_value: product.max_discount_value,
                    max_discount_type: product.max_discount_type,
                    requires_imei: product.requires_imei,
                    quantity: 1,
                    unit: 'pieces',
                    discount: 0,
                    discount_type: 'fixed',
                    selected_imeis: [],
                    is_gift: 0
                });
                addedNew = true;
            }
            renderCart();
            if (addedNew && product.requires_imei) {
                openImeiPicker(0);
            } else if (!('ontouchstart' in window)) {
                document.getElementById('barcode-input').focus();
            }
        }

        function renderCart() {
            const list = document.getElementById('cartList');
            const empty = document.getElementById('cartEmpty');
            const count = document.getElementById('cartCount');

            count.textContent = state.cart.length;

            if (state.cart.length === 0) {
                list.innerHTML = '';
                list.style.display = 'none';
                empty.style.display = 'flex';
                recalcTotals();
                return;
            }

            list.style.display = 'flex';
            empty.style.display = 'none';

            list.innerHTML = state.cart.map((item, idx) => {
                const gift = isGift(item);
                const lineBase = item.price * item.quantity;
                let lineDisc = 0;
                if (item.discount > 0) {
                    lineDisc = (SHOW_DISC_TYPE && item.discount_type === 'percentage') ?
                        lineBase * (item.discount / 100) :
                        item.discount;
                    if (lineDisc > lineBase) lineDisc = lineBase;
                }
                const lineTotal = lineBase - lineDisc;
                const key = rowKey(item);
                const canPrice = !gift && CAN_EDIT_PRICE;
                const canDisc = !gift && CAN_EDIT_DISCOUNT;
                const padMain = gift ? '' : (canPrice ? 'price' : 'qty');
                const padTotal = gift ? '' : (canDisc ? 'disc' : (canPrice ? 'price' : 'qty'));
                const discLabel = lineDisc > 0 ? `<span class="disc"><i class="fas fa-tag"></i> -${fmt(lineDisc)}</span>` : '';
                const imeiBtn = (!gift && item.requires_imei) ?
                    `<button type="button" class="imei" data-imei="${idx}"><i class="fas fa-mobile-alt"></i> ${(item.selected_imeis||[]).length}/${Math.round(item.quantity)}</button>` : '';

                return `<div class="blink-cart-item${gift?' is-gift':''}${flashKey===key?' flash':''}" data-idx="${idx}">
            <div class="blink-ci-row">
                <div class="blink-ci-idx">${state.cart.length - idx}</div>
                <div class="blink-ci-main" ${padMain?`data-pad="${padMain}"`:''}>
                    <div class="blink-ci-name" title="${esc(item.name)}">${esc(item.name)}${gift?' <span style="color:#b45309;font-size:10px">GIFT</span>':''}</div>
                    <div class="blink-ci-meta">
                        <span class="unit${canPrice?' editable':''}">${canPrice?'<i class="fas fa-pen" style="font-size:9px"></i> ':''}${fmt(item.price)}</span>${item.code ? `<span>· ${esc(item.code)}</span>` : ''}${discLabel}${imeiBtn}
                    </div>
                </div>
                <div class="blink-qty">
                    <button type="button" data-dec="${idx}" ${gift?'disabled':''}><i class="fas fa-minus"></i></button>
                    <input type="text" readonly inputmode="none" value="${item.quantity.toFixed(item.quantity%1?2:0)}" ${gift?'':`data-pad="qty"`}>
                    <button type="button" data-inc="${idx}" ${gift?'disabled':''}><i class="fas fa-plus"></i></button>
                </div>
                <div class="blink-ci-price" ${padTotal?`data-pad="${padTotal}"`:''}>${fmt(lineTotal)}</div>
                <button type="button" class="blink-ci-remove" data-remove="${idx}" ${gift?'disabled':''}>
                    <i class="fas ${gift?'fa-lock':'fa-times'}"></i>
                </button>
            </div>
        </div>`;
            }).join('');

            if (flashKey !== null) {
                list.scrollTop = 0;
                flashKey = null;
                setTimeout(() => list.querySelectorAll('.flash').forEach(el => el.classList.remove('flash')), 700);
            }

            recalcTotals();
        }

        function recalcTotals() {
            let subtotal = 0,
                totalDisc = 0;
            state.cart.forEach(item => {
                const base = item.price * item.quantity;
                subtotal += base;
                let d = 0;
                if (item.discount > 0) {
                    d = (SHOW_DISC_TYPE && item.discount_type === 'percentage') ?
                        base * (item.discount / 100) :
                        item.discount;
                    if (d > base) d = base;
                }
                totalDisc += d;
            });

            const taxable = subtotal - totalDisc;
            const taxAmt = taxable * (state.taxRate / 100);
            const grand = taxable + taxAmt;

            document.getElementById('subDisplay').textContent = fmt(subtotal);
            document.getElementById('discDisplay').textContent = '-' + fmt(totalDisc);
            document.getElementById('taxDisplay').textContent = fmt(taxAmt);
            document.getElementById('taxRateLabel').textContent = state.taxRate;
            document.getElementById('totalDisplay').textContent = fmt(grand);
            document.getElementById('chargeAmount').textContent = fmt(grand);

            state._subtotal = subtotal;
            state._discount = totalDisc;
            state._tax = taxAmt;
            state._grand = grand;

            updateChange();
        }

        function updateChange() {
            const tendered = parseFloat(document.getElementById('tenderedAmountInput').value) || 0;
            const grand = state._grand || 0;
            const diff = tendered - grand;
            const changeBox = document.getElementById('changeBox');
            const dueBox = document.getElementById('dueBox');

            if (diff >= 0) {
                document.getElementById('changeDisplay').textContent = fmt(diff);
                changeBox.style.display = 'flex';
                dueBox.style.display = 'none';
            } else {
                document.getElementById('dueDisplay').textContent = fmt(Math.abs(diff));
                changeBox.style.display = 'none';
                dueBox.style.display = 'flex';
            }
        }

        /* ==========================================================================
           CART EVENT DELEGATION
           ========================================================================== */
        document.getElementById('cartList').addEventListener('click', (e) => {
            const incBtn = e.target.closest('[data-inc]');
            const decBtn = e.target.closest('[data-dec]');
            const remBtn = e.target.closest('[data-remove]');
            const imeiBtn = e.target.closest('[data-imei]');

            if (imeiBtn) {
                openImeiPicker(+imeiBtn.dataset.imei);
                return;
            }
            const tog = e.target.closest('[data-pad]');
            if (tog && !incBtn && !decBtn && !remBtn) {
                openPad(+tog.closest('.blink-cart-item').dataset.idx, tog.dataset.pad);
                return;
            }
            if (incBtn) {
                const i = +incBtn.dataset.inc;
                if (state.cart[i]) {
                    adjustQty(i, 1);
                }
                return;
            }
            if (decBtn) {
                const i = +decBtn.dataset.dec;
                if (state.cart[i]) {
                    adjustQty(i, -1);
                }
                return;
            }
            if (remBtn) {
                const i = +remBtn.dataset.remove;
                removeItem(i);
                return;
            }
        });

        /* ==========================================================================
           KEYPAD (quantity / price / discount)
           ========================================================================== */
        const pad = {
            idx: null,
            field: 'qty',
            vals: {},
            type: 'fixed',
            fresh: true
        };
        const PAD_LABELS = {
            qty: 'Quantity',
            price: 'Unit price',
            disc: 'Discount'
        };

        function padNum(f) {
            return parseFloat(pad.vals[f]) || 0;
        }

        function padRefresh() {
            const item = state.cart[pad.idx];
            if (!item) return;
            document.getElementById('padValue').textContent = pad.vals[pad.field] === '' ? '0' : pad.vals[pad.field];
            document.getElementById('padLabel').textContent = PAD_LABELS[pad.field] + (pad.field === 'disc' && SHOW_DISC_TYPE ? (pad.type === 'percentage' ? ' (%)' : ` (${CURRENCY})`) : '');
            const base = padNum('price') * padNum('qty');
            let d = pad.type === 'percentage' && SHOW_DISC_TYPE ? base * padNum('disc') / 100 : padNum('disc');
            if (d > base) d = base;
            document.getElementById('padLine').textContent = fmt(base - d);
            document.querySelectorAll('#padTabs .pad-tab').forEach(b => b.classList.toggle('active', b.dataset.ptab === pad.field));
            const types = document.getElementById('padTypes');
            types.classList.toggle('show', pad.field === 'disc' && SHOW_DISC_TYPE);
            types.querySelectorAll('[data-ptype]').forEach(b => b.classList.toggle('active', b.dataset.ptype === pad.type));
        }

        function padSetField(f) {
            pad.field = f;
            pad.fresh = true;
            padRefresh();
        }

        function openPad(idx, field) {
            const item = state.cart[idx];
            if (!item || isGift(item)) return;
            if (document.activeElement) document.activeElement.blur();
            pad.idx = idx;
            pad.vals = {
                qty: String(+item.quantity.toFixed(2)),
                price: String(+item.price.toFixed(2)),
                disc: String(+(item.discount || 0).toFixed(2))
            };
            pad.type = SHOW_DISC_TYPE && item.discount_type === 'percentage' ? 'percentage' : 'fixed';

            const fields = ['qty'];
            if (CAN_EDIT_PRICE) fields.push('price');
            if (CAN_EDIT_DISCOUNT) fields.push('disc');
            document.getElementById('padTabs').innerHTML = fields.map(f =>
                `<button type="button" class="pad-tab" data-ptab="${f}">${PAD_LABELS[f]}</button>`).join('');
            document.getElementById('padName').textContent = item.name;
            pad.field = fields.includes(field) ? field : 'qty';
            pad.fresh = true;
            padRefresh();
            openModal('padModal');
        }

        function padKey(k) {
            const f = pad.field;
            let v = pad.vals[f];
            if (k === 'clear') {
                v = '';
            } else if (k === 'back') {
                v = pad.fresh ? '' : v.slice(0, -1);
            } else if (k === '.') {
                v = pad.fresh ? '0.' : (v.includes('.') ? v : (v === '' ? '0.' : v + '.'));
            } else {
                if (pad.fresh || v === '0') v = k;
                else if (v.length < 9 && !/\.\d\d$/.test(v)) v += k;
            }
            pad.fresh = false;
            pad.vals[f] = v;
            padRefresh();
        }

        function padApply() {
            const item = state.cart[pad.idx];
            if (!item) return closeModal('padModal');

            const qty = padNum('qty');
            if (qty < 0.01) {
                toast('Quantity must be greater than zero', 'err');
                padSetField('qty');
                return;
            }
            if (qty > item.stock) {
                toast(`Only ${item.stock} available`, 'err');
                padSetField('qty');
                return;
            }
            if (SHOW_DISC_TYPE && pad.type === 'percentage' && padNum('disc') > 100) {
                toast('Discount cannot exceed 100%', 'err');
                padSetField('disc');
                return;
            }

            item.quantity = qty;
            if (CAN_EDIT_PRICE) item.price = padNum('price');
            if (CAN_EDIT_DISCOUNT) {
                item.discount = padNum('disc');
                item.discount_type = SHOW_DISC_TYPE ? pad.type : 'fixed';
            }
            closeModal('padModal');
            renderCart();
        }

        document.getElementById('padModal').addEventListener('click', (e) => {
            const key = e.target.closest('[data-key]');
            const tab = e.target.closest('[data-ptab]');
            const type = e.target.closest('[data-ptype]');
            if (key) padKey(key.dataset.key);
            else if (tab) padSetField(tab.dataset.ptab);
            else if (type) {
                pad.type = type.dataset.ptype;
                padRefresh();
            }
        });
        document.getElementById('padApply').addEventListener('click', padApply);
        document.addEventListener('keydown', (e) => {
            if (!document.getElementById('padModal').classList.contains('open')) return;
            if (/^[0-9]$/.test(e.key) || e.key === '.') {
                e.preventDefault();
                padKey(e.key);
            } else if (e.key === 'Backspace') {
                e.preventDefault();
                padKey('back');
            } else if (e.key === 'Enter') {
                e.preventDefault();
                padApply();
            }
        });

        function adjustQty(idx, delta) {
            const item = state.cart[idx];
            if (isGift(item)) return;
            const cs = parseFloat(item.carton_size) || 1;
            let step = 1;
            if (item.unit === 'cartons' && cs > 1) step = cs;
            let newQty = item.quantity + (delta * step);
            if (newQty < 0.01) newQty = 0.01;
            if (newQty > item.stock) {
                toast(`Only ${item.stock} available`, 'err');
                newQty = item.stock;
            }
            item.quantity = newQty;
            renderCart();
        }

        function removeItem(idx) {
            if (!state.cart[idx]) return;
            if (isGift(state.cart[idx])) {
                toast('Gift items are managed by promotions', 'err');
                return;
            }
            const name = state.cart[idx].name;
            state.cart.splice(idx, 1);
            renderCart();
            toast(`${name} removed`);
        }

        window.clearCart = function() {
            if (state.cart.length === 0) return;
            if (confirm('Clear the entire cart?')) {
                state.cart = [];
                renderCart();
                toast('Cart cleared');
            }
        };

        /* ==========================================================================
           IMEI PICKER
           ========================================================================== */
        let imeiCtx = null;

        async function openImeiPicker(idx) {
            const item = state.cart[idx];
            if (!item || isGift(item) || !item.requires_imei) return;

            const qty = Math.round(item.quantity);
            imeiCtx = {
                idx,
                selected: new Set((item.selected_imeis || []).map(String)),
                all: []
            };
            document.getElementById('imeiTitle').textContent = item.name;
            document.getElementById('imeiHint').textContent = `Select exactly ${qty} IMEI(s)`;
            document.getElementById('imeiSearch').value = '';
            document.getElementById('imeiList').innerHTML = '<div style="text-align:center;padding:20px;color:var(--pos-muted)">Loading…</div>';
            openModal('imeiModal');

            try {
                const res = await fetch('<?= site_url('api/products/available-imeis') ?>/' + encodeURIComponent(item.id), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                const rows = (data && Array.isArray(data.results)) ? data.results : [];
                const known = new Set(rows.map(r => String(r.id || r.text || '').trim()));
                imeiCtx.all = rows.map(r => String(r.id || r.text || '').trim()).filter(Boolean);
                imeiCtx.selected.forEach(v => {
                    if (!known.has(v)) imeiCtx.all.push(v);
                });
            } catch (err) {
                imeiCtx.all = [...imeiCtx.selected];
            }
            renderImeiList();
        }

        function renderImeiList() {
            if (!imeiCtx) return;
            const term = document.getElementById('imeiSearch').value.trim().toLowerCase();
            const list = imeiCtx.all.filter(v => !term || v.toLowerCase().includes(term));
            document.getElementById('imeiList').innerHTML = list.length ? list.map(v => `
                <label style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:var(--pos-panel-2); border-radius:10px; cursor:pointer">
                    <input type="checkbox" data-imei-val="${esc(v)}" ${imeiCtx.selected.has(v) ? 'checked' : ''} style="width:18px;height:18px">
                    <span>${esc(v)}</span>
                </label>`).join('') : '<div style="text-align:center;padding:20px;color:var(--pos-muted)">No IMEI available</div>';
        }

        document.getElementById('imeiSearch').addEventListener('input', renderImeiList);
        document.getElementById('imeiList').addEventListener('change', (e) => {
            const cb = e.target.closest('[data-imei-val]');
            if (!cb || !imeiCtx) return;
            if (cb.checked) imeiCtx.selected.add(cb.dataset.imeiVal);
            else imeiCtx.selected.delete(cb.dataset.imeiVal);
        });
        document.getElementById('imeiApply').addEventListener('click', () => {
            if (!imeiCtx || !state.cart[imeiCtx.idx]) return;
            const qty = Math.round(state.cart[imeiCtx.idx].quantity);
            if (imeiCtx.selected.size !== qty) {
                toast(`Select exactly ${qty} IMEI(s)`, 'err');
                return;
            }
            state.cart[imeiCtx.idx].selected_imeis = [...imeiCtx.selected];
            imeiCtx = null;
            closeModal('imeiModal');
            renderCart();
        });

        /* ==========================================================================
           TENDER INPUT & QUICK CASH
           ========================================================================== */
        document.getElementById('tenderedAmountInput').addEventListener('input', updateChange);

        document.getElementById('quickCash').addEventListener('click', (e) => {
            const btn = e.target.closest('[data-amt]');
            if (!btn) return;
            const cur = parseFloat(document.getElementById('tenderedAmountInput').value) || 0;
            document.getElementById('tenderedAmountInput').value = (cur + parseInt(btn.dataset.amt, 10)).toFixed(2);
            updateChange();
        });

        /* ==========================================================================
           BARCODE / SEARCH INPUT
           ========================================================================== */
        const barcodeInput = document.getElementById('barcode-input');
        let searchTimer = null;

        barcodeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimer);
                const v = barcodeInput.value.trim();
                if (!v) return;
                lookupBarcode(v);
            }
        });
        barcodeInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const v = barcodeInput.value.trim();
            if (v.length < 2) {
                loadProducts(state.activeCat);
                return;
            }
            searchTimer = setTimeout(() => loadProducts(state.activeCat, v), 280);
        });

        async function lookupBarcode(code) {
            try {
                const res = await fetch('<?= site_url('api/products/barcode') ?>?barcode=' + encodeURIComponent(code), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('lookup failed');
                const p = await res.json();
                if (p && p.id) {
                    addToCart(normalizeProduct(p));
                    barcodeInput.value = '';
                    return;
                }
            } catch (err) {
                toast('Error searching product', 'err');
                return;
            }

            // Not a barcode: if the typed text narrowed the grid to one product, add it
            clearTimeout(searchTimer);
            await loadProducts(state.activeCat, code);
            if (state.products.length === 1) {
                addToCart(state.products[0]);
                barcodeInput.value = '';
                loadProducts(state.activeCat);
            } else {
                toast(`Product "${code}" not found`, 'err');
            }
        }

        /* ==========================================================================
           OPTIONS MODAL (customer, employee, payment, tax, description)
           ========================================================================== */
        const customersList = <?= json_encode(array_map(fn($c) => ['id' => (int)$c['id'], 'name' => $c['name']], $customers ?? [])) ?>;
        const employeesList = <?= json_encode(array_map(fn($e) => ['id' => (int)$e['id'], 'name' => $e['name']], $employees ?? [])) ?>;

        document.getElementById('btnMore').addEventListener('click', openOptions);
        document.getElementById('openMore').addEventListener('click', openOptions);

        function openOptions() {
            const body = document.getElementById('optionsForm');
            const paymentMethods = ['cash', 'card', 'upi', 'wallet'];

            body.innerHTML = `
        <div class="blink-field">
            <label>Customer</label>
            <select id="opt_customer">
                <option value="0">Walk-in Customer</option>
                ${customersList.map(c => `<option value="${c.id}" ${state.customerId===c.id?'selected':''}>${esc(c.name)}</option>`).join('')}
            </select>
        </div>
        <div class="blink-field">
            <label>Employee / Server</label>
            <select id="opt_employee">
                <option value="0">None</option>
                ${employeesList.map(e => `<option value="${e.id}" ${state.employeeId===e.id?'selected':''}>${esc(e.name)}</option>`).join('')}
            </select>
        </div>
        <div class="blink-field">
            <label>Payment Type</label>
            <select id="opt_paytype">
                <option value="cash" ${state.paymentType==='cash'?'selected':''}>Cash</option>
                <option value="credit" ${state.paymentType==='credit'?'selected':''}>Credit</option>
            </select>
        </div>
        <div class="blink-field">
            <label>Payment Method</label>
            <select id="opt_paymethod">
                ${paymentMethods.map(m => `<option value="${m}" ${state.paymentMethod===m?'selected':''}>${m.charAt(0).toUpperCase()+m.slice(1)}</option>`).join('')}
            </select>
        </div>
        <div class="blink-field">
            <label>Tax Rate (%)</label>
            <input type="number" id="opt_tax" value="${state.taxRate}" min="0" max="100" step="0.01">
        </div>
        ${ZATCA_ENABLED ? `
        <div class="blink-field">
            <label>ZATCA Invoice Type</label>
            <select id="opt_zatca">
                <option value="simplified" ${state.zatcaType==='simplified'?'selected':''}>Simplified</option>
                <option value="standard" ${state.zatcaType==='standard'?'selected':''}>Standard</option>
            </select>
        </div>` : ''}
        <div class="blink-field">
            <label>Notes</label>
            <textarea id="opt_desc" rows="2" placeholder="Optional">${esc(state.description)}</textarea>
        </div>
    `;
            openModal('optionsModal');
        }

        document.getElementById('optionsApply').addEventListener('click', () => {
            state.customerId = parseInt(document.getElementById('opt_customer').value, 10) || 0;
            state.employeeId = parseInt(document.getElementById('opt_employee').value, 10) || 0;
            state.paymentType = document.getElementById('opt_paytype').value;
            state.paymentMethod = document.getElementById('opt_paymethod').value;
            state.taxRate = parseFloat(document.getElementById('opt_tax').value) || 0;
            state.description = document.getElementById('opt_desc').value || '';
            if (ZATCA_ENABLED) {
                const z = document.getElementById('opt_zatca');
                if (z) state.zatcaType = z.value;
            }
            recalcTotals();
            closeModal('optionsModal');
            toast('Options updated');
        });

        /* ==========================================================================
           HELP MODAL
           ========================================================================== */
        document.getElementById('openHelp').addEventListener('click', () => openModal('helpModal'));

        /* ==========================================================================
           SUBMIT HANDLERS
           ========================================================================== */
        function syncForm() {
            document.getElementById('f_customer_id').value = state.customerId;
            document.getElementById('f_employee_id').value = state.employeeId;
            document.getElementById('f_description').value = state.description;
            document.getElementById('f_payment_type').value = state.paymentType;
            document.getElementById('f_payment_method').value = state.paymentMethod;
            document.getElementById('f_tax_rate').value = state.taxRate;
            document.getElementById('f_total_tax').value = (state._tax || 0).toFixed(2);
            document.getElementById('f_total_discount').value = (state._discount || 0).toFixed(2);
            document.getElementById('f_subtotal').value = (state._subtotal || 0).toFixed(2);
            document.getElementById('f_grand_total').value = (state._grand || 0).toFixed(2);
            document.getElementById('f_tendered').value = (parseFloat(document.getElementById('tenderedAmountInput').value) || 0).toFixed(2);
            document.getElementById('f_change').value = Math.max(0, (parseFloat(document.getElementById('tenderedAmountInput').value) || 0) - (state._grand || 0)).toFixed(2);
            if (!SHOW_DISC_TYPE) state.cart.forEach(it => it.discount_type = 'fixed');
            document.getElementById('f_cart_data').value = JSON.stringify(state.cart);
            if (ZATCA_ENABLED) {
                const z = document.getElementById('f_zatca_type');
                if (z) z.value = state.zatcaType;
            }
        }

        function validate() {
            const errors = [];
            if (state.cart.length === 0 && document.getElementById('f_misc').value !== '1') errors.push('Cart is empty. Please add products to continue.');
            state.cart.forEach(item => {
                if (!isGift(item) && item.requires_imei) {
                    const qty = Math.round(item.quantity);
                    const imeis = (item.selected_imeis || []).map(v => String(v).trim()).filter(Boolean);
                    if (Math.abs(item.quantity - qty) > 0.0001 || qty <= 0) {
                        errors.push(`IMEI product ${item.name} must have whole quantity.`);
                    } else if (new Set(imeis.map(v => v.toLowerCase())).size !== imeis.length) {
                        errors.push(`Duplicate IMEI selected for ${item.name}.`);
                    } else if (imeis.length !== qty) {
                        errors.push(`Select exactly ${qty} IMEI(s) for ${item.name}.`);
                    }
                }
                if (CAN_EDIT_DISCOUNT && !IS_ADMIN && !isGift(item)) {
                    const base = item.price * item.quantity;
                    let d = (SHOW_DISC_TYPE && item.discount_type === 'percentage') ?
                        base * (item.discount / 100) :
                        item.discount;
                    if (d > base) d = base;
                    const lim = (item.max_discount_type === 'percentage') ?
                        base * (item.max_discount_value / 100) :
                        item.max_discount_value * item.quantity;
                    if (d - lim > 0.0001) errors.push(`Discount for ${item.name} exceeds product limit.`);
                }
            });
            return errors;
        }

        // Empty cart + tendered amount: invoice it as a "Misc Service" line, as on the new sale page
        function setupMiscService() {
            const miscFlag = document.getElementById('f_misc');
            miscFlag.value = '0';
            if (state.cart.length > 0) return {
                ok: true
            };

            const amount = parseFloat(document.getElementById('tenderedAmountInput').value) || 0;
            if (amount <= 0) return {
                ok: false
            };

            const message = <?= json_encode(lang('Sales.misc_service_confirm')) ?>.replace('{amount}', CURRENCY + amount.toFixed(2));
            if (!confirm(message)) return {
                ok: false,
                cancelled: true
            };

            miscFlag.value = '1';
            document.getElementById('f_misc_amt').value = amount.toFixed(2);
            return {
                ok: true,
                misc: true,
                amount
            };
        }

        let isSubmitting = false;

        document.getElementById('completeSubmitBtn').addEventListener('click', () => {
            if (isSubmitting) return;

            const misc = setupMiscService();
            if (!misc.ok) {
                if (!misc.cancelled) toast(<?= json_encode(lang('Sales.cart_empty_add_products')) ?>, 'err');
                return;
            }

            const errors = validate();
            if (errors.length) {
                errorToast(errors);
                return;
            }

            syncForm();
            if (misc.misc) {
                document.getElementById('f_subtotal').value = misc.amount.toFixed(2);
                document.getElementById('f_grand_total').value = misc.amount.toFixed(2);
                document.getElementById('f_tendered').value = misc.amount.toFixed(2);
                document.getElementById('f_total_tax').value = '0';
                document.getElementById('f_total_discount').value = '0';
                document.getElementById('f_change').value = '0';
            }
            isSubmitting = true;
            document.getElementById('f_draft').value = '0';
            document.getElementById('pos-form').action = '<?= site_url('sales/create') ?>';
            document.getElementById('saleLoader').classList.add('open');
            setTimeout(() => document.getElementById('pos-form').submit(), 60);
        });

        document.getElementById('saveDraftBtn').addEventListener('click', () => {
            if (isSubmitting) return;
            if (state.cart.length === 0) {
                toast(<?= json_encode(lang('Sales.cannot_save_empty_cart')) ?>, 'err');
                return;
            }
            if (!confirm(<?= json_encode(lang('Sales.confirm_save_draft')) ?>)) return;
            isSubmitting = true;
            syncForm();
            document.getElementById('f_draft').value = '1';
            document.getElementById('pos-form').action = '<?= site_url('sales/save-draft') ?>';
            document.getElementById('pos-form').submit();
        });

        /* ==========================================================================
           KEYBOARD SHORTCUTS
           ========================================================================== */
        document.addEventListener('keydown', (e) => {
            const inInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);
            const modalOpen = document.querySelector('.blink-modal.open');

            if (e.key === 'Escape') {
                if (modalOpen) {
                    modalOpen.classList.remove('open');
                    return;
                }
                return;
            }
            if (modalOpen) return;

            if (e.key === 'F1') {
                e.preventDefault();
                barcodeInput.focus();
                barcodeInput.select();
            } else if (e.key === 'F3') {
                e.preventDefault();
                openOptions();
            } else if (e.key === 'F6') {
                e.preventDefault();
                document.getElementById('tenderedAmountInput').focus();
            } else if (e.key === 'F7') {
                e.preventDefault();
                openOptions();
            } else if (e.key === 'F5') {
                e.preventDefault();
                document.getElementById('saveDraftBtn').click();
            } else if (e.key === 'F9' || (e.ctrlKey && e.key === 's')) {
                e.preventDefault();
                document.getElementById('completeSubmitBtn').click();
            } else if (e.key === 'F12' && !inInput) {
                e.preventDefault();
                clearCart();
            } else if ((e.key === '+' || e.key === '=') && !inInput && state.cart.length) {
                e.preventDefault();
                adjustQty(state.cart.length - 1, 1);
            } else if (e.key === '-' && !inInput && state.cart.length) {
                e.preventDefault();
                adjustQty(state.cart.length - 1, -1);
            } else if (e.key === 'Delete' && !inInput && state.cart.length) {
                e.preventDefault();
                removeItem(state.cart.length - 1);
            }
        });

        /* ==========================================================================
           DRAFT PREFILL
           ========================================================================== */
        if (window.__DRAFT_PREFILL__) {
            try {
                const dp = window.__DRAFT_PREFILL__;
                if (Array.isArray(dp.cart) && dp.cart.length) {
                    state.cart = dp.cart.map(it => ({
                        id: it.id,
                        name: it.name,
                        code: it.code || '',
                        price: parseFloat(it.price) || 0,
                        cost_price: parseFloat(it.cost_price) || 0,
                        stock: parseFloat(it.stock) || 0,
                        carton_size: parseFloat(it.carton_size) || 0,
                        max_discount_value: parseFloat(it.max_discount_value) || 0,
                        max_discount_type: it.max_discount_type || 'fixed',
                        requires_imei: needImei(it.requires_imei),
                        quantity: parseFloat(it.quantity) || 0,
                        unit: it.unit || 'pieces',
                        discount: parseFloat(it.discount) || 0,
                        discount_type: it.discount_type || 'fixed',
                        selected_imeis: Array.isArray(it.selected_imeis) ? it.selected_imeis : [],
                        is_gift: Number(it.is_gift) || 0
                    }));
                }
                if (dp.customerId) state.customerId = parseInt(dp.customerId, 10) || 0;
                if (dp.employeeId) state.employeeId = parseInt(dp.employeeId, 10) || 0;
                if (dp.paymentMethod) state.paymentMethod = dp.paymentMethod;
                if (typeof dp.description === 'string') state.description = dp.description;
                if (dp.zatcaInvoiceType) state.zatcaType = dp.zatcaInvoiceType;
            } catch (e) {
                console.warn('Prefill failed', e);
            }
        }

        /* ==========================================================================
           INIT
           ========================================================================== */
        const CSRF_NAME = '<?= csrf_token() ?>';

        function refreshCsrfToken() {
            fetch('<?= site_url('api/csrf-refresh') ?>', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.token) {
                        document.querySelectorAll(`input[name="${CSRF_NAME}"]`).forEach(i => i.value = data.token);
                    }
                })
                .catch(() => {});
        }
        setInterval(refreshCsrfToken, 600000);

        loadProducts('all');
        renderCart();
        barcodeInput.focus();

    })();
</script>

<?= $this->endSection() ?>