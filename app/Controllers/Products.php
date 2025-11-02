<?php

namespace App\Controllers;

use App\Models\M_inventory;
use App\Models\M_products;
use App\Models\UnitModel;

class Products extends BaseController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        helper('audit');
    }

    /**
     * Display a list of products.
     *
     * @return string
     */
    public function index()
    {
        $data = [
            'title' => 'Products List',
        ];

        return view('products/index', $data);
    }

    public function show($id = null)
    {
        $model = new M_products();
        $data['product'] = $model->find($id);
        $data['title'] = 'Product Details';
        // Check if the product exists before rendering the view.
        if (!$data['product']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Product not found');
        }
        return view('products/show', $data);
    }

    public function new()
    {
        helper('form');
        $unitModel = new UnitModel();

        $units = $unitModel->forStore()->orderBy('name', 'ASC')->findAll();

        return view('products/new', [
            'title' => 'Add New Product',
            'units' => $units,
        ]);
    }

    public function create()
    {
        helper('form');

        $validation = \Config\Services::validation();
        if (!$this->validate([
            'name' => 'required',
            'price' => 'required',
            'cost_price' => 'required',
            'barcode' => 'permit_empty|is_unique[pos_products.barcode]',
            'code' => 'permit_empty',
            'stock_alert' => 'permit_empty',
            'description' => 'permit_empty',
            'unit_id' => 'permit_empty|integer',
            'carton_size' => 'permit_empty|decimal', // Added carton_size validation
            'store_id' => 'permit_empty',
            'created_at' => 'permit_empty',
            'updated_at' => 'permit_empty',
        ])) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $post = $this->validator->getValidated();

        $storeId = session('store_id') ?? '';
        $barcode = trim((string) ($post['barcode'] ?? ''));
        if ($barcode === '') {
            $barcode = $this->generateUniqueBarcode(is_numeric($storeId) ? (int) $storeId : null);
        }

        $data = [
            'name' => $post['name'],
            'price' => $post['price'],
            'cost_price' => $post['cost_price'],
            'description' => $post['description'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'barcode' => $barcode,
            'store_id' => $storeId,
            'code' => $post['code'] ?? '',
            'stock_alert' => $post['stock_alert'] ?? 10,
            'unit_id' => $post['unit_id'] ?? null,
            'carton_size' => !empty($post['carton_size']) ? (float)$post['carton_size'] : null, // Added carton_size
        ];

        $model = new M_products();
        $model->insert($data);

        logAction('product_created', 'Product Name: ' . $data['name'] . ', ID: ' . $model->insertID());
        return redirect()->to(site_url('products'))->with('success', 'Product created successfully');
    }

    public function edit($id = null)
    {
        helper('form');

        $model = new M_products();
        $unitModel = new UnitModel();

        $units = $unitModel->forStore()->orderBy('name', 'ASC')->findAll();

        $data['units'] = $units;
        $data['product'] = $model->find($id);
        $data['title'] = 'Edit Product';
        // Check if the product exists before rendering the view.
        if (!$data['product']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Product not found');
        }
        return view('products/edit', $data);
    }

    public function update($id = null)
    {
        helper('form');
        $validation = \Config\Services::validation();
        if (!$this->validate([
            'name' => 'required',
            'price' => 'required',
            'cost_price' => 'required',
            'code' => 'permit_empty',
            'stock_alert' => 'permit_empty',
            'description' => 'permit_empty',
            'barcode' => 'permit_empty',
            'unit_id' => 'permit_empty|integer',
            'carton_size' => 'permit_empty|decimal', // Added carton_size validation
            'updated_at' => 'permit_empty',
        ])) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $post = $this->validator->getValidated();
        $model = new M_products();
        $model->update($id, $post);

        logAction('product_updated', 'Product ID: ' . $id . ', Name: ' . $post['name']);
        return redirect()->to(site_url('products'))->with('success', 'Product updated successfully');
    }


    public function delete($id = null)
    {
        $model = new M_products();

        // Check for related sales
        $saleItemsModel = new \App\Models\M_sale_items();
        $salesCount = $saleItemsModel->where('product_id', $id)->countAllResults();

        // Check for related purchases (if you have a purchases table)
        $purchaseItemsModel = new \App\Models\PurchaseItemModel();
        $purchasesCount = $purchaseItemsModel->where('product_id', $id)->countAllResults();

        if ($salesCount > 0 || $purchasesCount > 0) {
            return redirect()->to(site_url('products'))->with('error', 'Cannot delete product: it has related sales or purchases.');
        }

        $model->forStore()->delete($id);

        // Delete Inventory logs related to the product
        $inventoryModel = new M_inventory();
        $inventoryModel->deleteByProductId($id);

        // Log the action
        logAction('product_deleted', 'Product ID: ' . $id);
        return redirect()->to(site_url('products'))->with('success', 'Product deleted successfully');
    }
    public function stockMovementHistory($productId)
    {
        $inventoryModel = new \App\Models\M_inventory();
        $history = $inventoryModel->getProductHistory($productId);

        $data = [
            'title' => 'Stock Movement History',
            'history' => $history,
            'productId' => $productId,
        ];

        return view('products/stock_movement_history', $data);
    }

    // AJAX endpoint for product search
    public function search($keyword = '')
    {
        // Get search term from query parameter 'q' or URL segment
        $q = $this->request->getGet('q') ?? $keyword;

        $model = new \App\Models\M_products();

        // Require minimum 1 character to search (performance optimization for large datasets)
        if (empty($q) || strlen(trim($q)) < 1) {
            // Return empty array or top 20 products
            $products = $model->select('id, name, code, barcode, cost_price, price, quantity, carton_size')
                ->forStore()
                ->orderBy('name', 'ASC')
                ->limit(20)
                ->findAll();
        } else {
            // Search by name, code, or barcode with optimized query
            $products = $model->groupStart()
                ->like('name', $q)
                ->orLike('code', $q)
                ->orLike('barcode', $q)
                ->groupEnd()
                ->select('id, name, code, barcode, cost_price, price, quantity, carton_size')
                ->forStore()
                ->orderBy('name', 'ASC')
                ->limit(50)
                ->findAll();
        }

        $results = [];
        foreach ($products as $p) {
            $results[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'code' => $p['code'] ?? '',
                'barcode' => $p['barcode'] ?? '',
                'text' => $p['name'] . ' (Stock: ' . $this->formatQuantityForDisplay($p['quantity'], $p['carton_size']) . ')',
                'price' => $p['price'],
                'cost_price' => $p['cost_price'],
                'quantity' => $p['quantity'],
                'carton_size' => $p['carton_size'] ?? null,
            ];
        }
        return $this->response->setJSON($results);
    }
    // Endpoint to get product by barcode
    public function barcode()
    {
        $barcode = $this->request->getGet('barcode');
        $model = new \App\Models\M_products();
        $product = $model->where('barcode', $barcode)
            ->select('id, name, code, cost_price, price, quantity, carton_size')
            ->forStore()
            ->first();
        return $this->response->setJSON($product ?? []);
    }

    public function datatable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Invalid request',
            ]);
        }

        $draw = (int) $this->request->getVar('draw');
        $start = (int) $this->request->getVar('start');
        $length = (int) $this->request->getVar('length');
        $length = $length > 0 ? min($length, 200) : 25; // guard against huge page sizes

        $search = $this->request->getVar('search')['value'] ?? '';
        $orderRequest = $this->request->getVar('order')[0] ?? null;

        $columns = ['id', 'name', 'cost_price', 'price', 'quantity', 'description'];

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $baseBuilder = $db->table('pos_products')->where('store_id', $storeId);

        $totalRecords = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = clone $baseBuilder;

        if (!empty($search)) {
            $filteredBuilder->groupStart()
                ->like('name', $search)
                ->orLike('code', $search)
                ->orLike('barcode', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $filteredBuilder)->countAllResults();

        $filteredBuilder->select('id, name, cost_price, price, quantity, carton_size, IFNULL(description, "") as description, code, barcode');

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $filteredBuilder->orderBy($orderColumn, $orderDir);
        } else {
            $filteredBuilder->orderBy('id', 'DESC');
        }

        $filteredBuilder->limit($length, $start);

        $products = $filteredBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $products,
        ]);
    }

    public function barcode1($code, $action = 'image')
    {
        $payload = generate_barcode($code);
        $image = is_array($payload) ? $payload['data'] : $payload;
        $mime = is_array($payload) ? $payload['mime'] : 'image/png';

        if ($action === 'preview') {
            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setBody($image);
        }

        // For download
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="barcode_' . $code . ($mime === 'image/svg+xml' ? '.svg' : '.png') . '"')
            ->setBody($image);
    }

    public function printBarcodes($productId = null)
    {
        helper('barcode');

        $model = new M_products();

        $model->select('id, name, code, barcode, price, quantity')->forStore();

        $idsFromQuery = $this->request->getGet('ids');
        if ($idsFromQuery) {
            $ids = array_filter(array_map('intval', explode(',', $idsFromQuery)));
            if (! empty($ids)) {
                $model->whereIn('id', $ids);
            }
        } elseif ($productId !== null) {
            $model->where('id', (int) $productId);
        }

        $products = $model
            ->where('barcode IS NOT NULL AND barcode != ""', null, false)
            ->orderBy('name', 'ASC')
            ->findAll();

        if (empty($products)) {
            return redirect()->to(site_url('products'))
                ->with('error', 'No products with barcodes were found for printing.');
        }

        return view('products/barcode_labels', [
            'title' => 'Print Barcode Labels',
            'products' => $products,
            'currencySymbol' => session('currency_symbol') ?? '',
        ]);
    }

    // Endpoint to generate barcode image
    public function barcode_image($barcode)
    {
        try {
            $payload = generate_barcode($barcode);
            $image = is_array($payload) ? $payload['data'] : $payload;
            $mime = is_array($payload) ? $payload['mime'] : 'image/png';

            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Cache-Control', 'public, max-age=3600')
                ->setBody($image);
        } catch (\Exception $e) {
            // Fallback: return a simple text response
            return $this->response
                ->setHeader('Content-Type', 'text/plain')
                ->setBody('Error generating barcode: ' . $e->getMessage());
        }
    }

    /**
     * Import products from a CSV file.
     * Accepts headers: name, code, barcode, price or unit_price, cost_price, quantity, stock_alert, description
     * Rows will be upserted based on barcode (preferred) or code within the current store.
     */
    public function import()
    {
        helper(['form']);

        if ($this->request->getMethod() === 'GET') {
            return view('products/import', [
                'title' => 'Import Products',
            ]);
        }

        // Validate basic file presence and type
        $rules = [
            'csv_file' => [
                'label' => 'CSV File',
                'rules' => 'uploaded[csv_file]|max_size[csv_file,5120]|ext_in[csv_file,csv,txt]',
            ],
        ];
        if (! $this->validate($rules)) {
            $errors = $this->validator ? $this->validator->getErrors() : ['csv_file' => 'Please upload a valid CSV file.'];
            return redirect()->back()->with('error', implode("\n", $errors))->withInput();
        }

        $file = $this->request->getFile('csv_file');
        if (! $file || ! $file->isValid()) {
            $err = method_exists($file, 'getErrorString') ? ($file->getErrorString() . ' (Error code: ' . $file->getError() . ')') : 'Invalid upload.';
            return redirect()->back()->with('error', $err)->withInput();
        }

        $handle = fopen($file->getTempName(), 'r');
        if ($handle === false) {
            return redirect()->back()->with('error', 'Could not read the uploaded file.')->withInput();
        }

        // Read header row
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV appears to be empty.')->withInput();
        }

        // Normalize headers to lowercase keys
        $map = [];
        foreach ($headers as $idx => $h) {
            if ($idx === 0) {
                // Strip UTF-8 BOM if present
                $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
            }
            $key = strtolower(trim($h));
            if ($key === '') {
                continue;
            }
            $map[$key] = $idx;
        }

        $required = ['name'];
        foreach ($required as $req) {
            if (! array_key_exists($req, $map)) {
                fclose($handle);
                return redirect()->back()->with('error', 'Missing required column: ' . $req)->withInput();
            }
        }

        $productsModel = new M_products();
        $inventoryModel = new M_inventory();
        $db = \Config\Database::connect();
        $storeId = session('store_id');
        $userId = session('user_id') ?? 0;

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        // Allowed optional synonyms for price
        $priceKeys = ['price', 'unit_price', 'selling_price'];

        $rowNum = 1; // start after header
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, static function ($v) {
                return trim((string) $v) !== '';
            })) === 0) {
                // skip completely empty rows
                continue;
            }

            $get = static function ($key) use ($map, $row) {
                if (! array_key_exists($key, $map)) {
                    return null;
                }
                return trim((string) ($row[$map[$key]] ?? ''));
            };

            $name = $get('name');
            if ($name === '' || $name === null) {
                $skipped++;
                $errors[] = "Row {$rowNum}: missing name";
                continue;
            }

            // Determine price (accept price or synonyms)
            $price = null;
            $priceColumnPresent = false;
            $priceProvided = false;
            foreach ($priceKeys as $pk) {
                if (! array_key_exists($pk, $map)) {
                    continue;
                }
                $priceColumnPresent = true;
                $val = $get($pk);
                if ($val !== null && $val !== '') {
                    $price = (float) $val;
                    $priceProvided = true;
                    break;
                }
            }

            $costPriceColumnPresent = array_key_exists('cost_price', $map);
            $costPriceProvided = false;
            $costPrice = null;
            if ($costPriceColumnPresent) {
                $costPriceRaw = $get('cost_price');
                if ($costPriceRaw !== null && $costPriceRaw !== '') {
                    $costPrice = (float) $costPriceRaw;
                    $costPriceProvided = true;
                }
            }

            $hasQuantityColumn = array_key_exists('quantity', $map);
            $quantityProvided = false;
            $quantity = null;
            if ($hasQuantityColumn) {
                $quantityValue = $get('quantity');
                if ($quantityValue !== null && $quantityValue !== '') {
                    $quantity = (float) $quantityValue;
                    $quantityProvided = true;
                }
            }

            $hasStockAlertColumn = array_key_exists('stock_alert', $map);
            $stockAlertProvided = false;
            $stockAlert = null;
            if ($hasStockAlertColumn) {
                $stockAlertRaw = $get('stock_alert');
                if ($stockAlertRaw !== null && $stockAlertRaw !== '') {
                    $stockAlert = (float) $stockAlertRaw;
                    $stockAlertProvided = true;
                }
            }

            $hasDescriptionColumn = array_key_exists('description', $map);
            $descriptionRaw = $get('description');
            $description = $hasDescriptionColumn ? ($descriptionRaw !== '' ? $descriptionRaw : null) : null;

            $hasCodeColumn = array_key_exists('code', $map);
            $codeRaw = $get('code');
            $code = $hasCodeColumn ? ($codeRaw !== '' ? $codeRaw : null) : null;

            $hasBarcodeColumn = array_key_exists('barcode', $map);
            $barcodeRaw = $get('barcode');
            $barcode = $hasBarcodeColumn ? ($barcodeRaw !== '' ? $barcodeRaw : null) : null;

            try {
                $db->transStart();

                $existing = null;
                if (! empty($barcode)) {
                    $existing = $productsModel->where('store_id', $storeId)->where('barcode', $barcode)->first();
                }
                if ($existing === null && ! empty($code)) {
                    $existing = $productsModel->where('store_id', $storeId)->where('code', $code)->first();
                }

                $data = [
                    'name' => $name,
                    'store_id' => $storeId,
                ];

                if ($priceProvided) {
                    $data['price'] = $price;
                } elseif (! $existing) {
                    $data['price'] = 0;
                } elseif ($priceColumnPresent) {
                    // header present but empty -> keep existing value
                }

                if ($costPriceProvided) {
                    $data['cost_price'] = $costPrice;
                } elseif (! $existing) {
                    $data['cost_price'] = 0;
                }

                if ($quantityProvided) {
                    $data['quantity'] = $quantity;
                } elseif (! $existing) {
                    $data['quantity'] = 0;
                }

                if ($stockAlertProvided) {
                    $data['stock_alert'] = $stockAlert;
                } elseif (! $existing) {
                    $data['stock_alert'] = 10;
                }

                if ($hasDescriptionColumn) {
                    $data['description'] = $description;
                } elseif (! $existing) {
                    $data['description'] = null;
                }

                if ($hasCodeColumn) {
                    $data['code'] = $code;
                } elseif (! $existing) {
                    $data['code'] = null;
                }

                if ($hasBarcodeColumn) {
                    $data['barcode'] = $barcode;
                } elseif (! $existing) {
                    $data['barcode'] = null;
                }

                $productId = null;
                $quantityDelta = 0.0;

                if ($existing) {
                    $productId = (int) $existing['id'];
                    $previousQuantity = (float) ($existing['quantity'] ?? 0);
                    $productsModel->update($productId, $data);
                    $updated++;
                    if ($quantityProvided) {
                        $newQuantity = isset($data['quantity']) ? (float) $data['quantity'] : $previousQuantity;
                        $quantityDelta = $newQuantity - $previousQuantity;
                    }
                } else {
                    $productsModel->insert($data);
                    $productId = (int) $productsModel->insertID();
                    $inserted++;
                    $quantityDelta = (float) ($data['quantity'] ?? 0);
                }

                if ($productId && abs($quantityDelta) > 0.00001) {
                    $inventoryModel->logStockChange(
                        $productId,
                        $userId,
                        $quantityDelta,
                        $quantityDelta >= 0 ? 'in' : 'out',
                        $storeId,
                        'CSV import row ' . $rowNum,
                        $data['cost_price'] ?? 0,
                        $data['price'] ?? 0
                    );
                }

                $db->transComplete();
                if ($db->transStatus() === false) {
                    throw new \RuntimeException('Transaction failed');
                }
            } catch (\Throwable $t) {
                $db->transRollback();
                $skipped++;
                $errors[] = "Row {$rowNum}: " . $t->getMessage();
            }
        }

        fclose($handle);

        $summary = "Imported successfully. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}.";
        if (! empty($errors)) {
            $summary .= "\nIssues:\n" . implode("\n", array_slice($errors, 0, 50));
        }

        // Log the action
        logAction('products_import', $summary);

        return redirect()->to(site_url('products'))
            ->with('success', $summary);
    }

    /**
     * Export products to an Excel-compatible file (HTML table opened by Excel).
     */
    public function export()
    {
        $productsModel = new M_products();
        $storeId = session('store_id');

        $products = $productsModel
            ->select('id, name, code, barcode, price, cost_price, quantity, stock_alert, description, created_at, updated_at')
            ->forStore($storeId)
            ->orderBy('name', 'ASC')
            ->findAll();

        $filename = 'products_' . date('Ymd_His') . '.xls';

        $response = service('response');
        $response->setHeader('Content-Type', 'application/vnd.ms-excel');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');

        $headings = ['ID', 'Name', 'Code', 'Barcode', 'Price', 'Cost Price', 'Quantity', 'Stock Alert', 'Description', 'Created At', 'Updated At'];

        $escape = static function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        $output = '<meta charset="UTF-8">';
        $output .= '<table border="1"><thead><tr>';
        foreach ($headings as $heading) {
            $output .= '<th>' . $escape($heading) . '</th>';
        }
        $output .= '</tr></thead><tbody>';

        foreach ($products as $product) {
            $output .= '<tr>';
            $output .= '<td>' . $escape($product['id'] ?? '') . '</td>';
            $output .= '<td>' . $escape($product['name'] ?? '') . '</td>';
            $output .= '<td>' . $escape($product['code'] ?? '') . '</td>';
            $output .= '<td>' . $escape($product['barcode'] ?? '') . '</td>';
            $output .= '<td>' . $escape(number_format((float) ($product['price'] ?? 0), 2, '.', '')) . '</td>';
            $output .= '<td>' . $escape(number_format((float) ($product['cost_price'] ?? 0), 2, '.', '')) . '</td>';
            $output .= '<td>' . $escape(number_format((float) ($product['quantity'] ?? 0), 2, '.', '')) . '</td>';
            $output .= '<td>' . $escape(number_format((float) ($product['stock_alert'] ?? 0), 2, '.', '')) . '</td>';
            $output .= '<td>' . $escape($product['description'] ?? '') . '</td>';
            $output .= '<td>' . $escape($product['created_at'] ?? '') . '</td>';
            $output .= '<td>' . $escape($product['updated_at'] ?? '') . '</td>';
            $output .= '</tr>';
        }

        if (empty($products)) {
            $output .= '<tr><td colspan="11">No products found</td></tr>';
        }

        $output .= '</tbody></table>';

        logAction('products_export', 'Exported ' . count($products) . ' products');

        return $response->setBody($output);
    }

    public function generateBarcode()
    {
        $storeId = session('store_id');
        $barcode = $this->generateUniqueBarcode(is_numeric($storeId) ? (int) $storeId : null);

        return $this->response->setJSON([
            'barcode' => $barcode,
        ]);
    }

    private function generateUniqueBarcode($storeId = null)
    {
        $db = \Config\Database::connect();
        $storeSegment = str_pad((string) ($storeId ?? 0), 3, '0', STR_PAD_LEFT);

        do {
            $random = str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $base = substr($storeSegment, -3) . $random;
            $checkDigit = $this->calculateEan13Checksum($base);
            $barcode = $base . $checkDigit;

            $exists = $db->table('pos_products')
                ->select('id')
                ->where('barcode', $barcode)
                ->limit(1)
                ->get()
                ->getFirstRow();
        } while ($exists !== null);

        return $barcode;
    }

    private function calculateEan13Checksum($base12Digits)
    {
        $sum = 0;
        $base = (string) $base12Digits;
        $length = strlen($base);
        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $base[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        return (10 - ($sum % 10)) % 10;
    }

    // Add helper method to format quantity
    private function formatQuantityForDisplay($pieces, $cartonSize)
    {
        if (!$cartonSize || $cartonSize <= 1) {
            return number_format($pieces, 2) . ' pcs';
        }

        $cartons = floor($pieces / $cartonSize);
        $remaining = $pieces - ($cartons * $cartonSize);

        if ($remaining > 0) {
            return $cartons . ' ctns + ' . number_format($remaining, 2) . ' pcs';
        }
        return $cartons . ' ctns';
    }
}
