<?php

namespace App\Controllers;

use App\Models\M_products;

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
        return view('products/new', ['title' => 'Add New Product']);
    }

    public function create()
    {
        helper('form');
        // $post1 = $this->request->getPost();
        // var_dump($post1);

        // Validate the form data   
        $validation = \Config\Services::validation();
        if (!$this->validate([
            'name' => 'required',
            'price' => 'required',
            'cost_price' => 'required',
            'barcode' => 'required|is_unique[pos_products.barcode]',
            'code' => 'permit_empty',
            'stock_alert' => 'permit_empty',
            'description' => 'permit_empty',

            'store_id' => 'permit_empty',
            'created_at' => 'permit_empty',
            'updated_at' => 'permit_empty',
            //'quantity' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        // Gets the validated data.
        $post = $this->validator->getValidated();

        // Insert the data into the database.
        $data = [
            'name' => $post['name'],
            'price' => $post['price'],
            'cost_price' => $post['cost_price'],
            //'quantity' => $post['quantity'],
            'description' => $post['description'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'barcode' => $post['barcode'] ?? '',
            'store_id' => session('store_id') ?? '',
            'code' => $post['code'] ?? '',
            'stock_alert' => $post['stock_alert'] ?? 10,
        ];
        // Create a new instance of the model and insert the data.
        $model = new M_products();
        $model->insert($data);

        // Log the action
        logAction('product_created', 'Product Name: ' . $data['name'] . ', ID: ' . $model->insertID());
        // Redirect to the products index page.
        return redirect()->to(site_url('products'))->with('success', 'Product created successfully');
    }

    public function edit($id = null)
    {
        helper('form');

        $model = new M_products();
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
            'barcode' => 'permit_empty', //'barcode' => 'required|is_unique[pos_products.barcode]',
            'updated_at' => 'permit_empty',
        ])) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        // Gets the validated data.
        $post = $this->validator->getValidated();
        // Update the data in the database.
        $model = new M_products();
        $model->update($id, $post);
        // Log the action
        logAction('product_updated', 'Product ID: ' . $id . ', Name: ' . $post['name']);
        // Redirect to the products index page.
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
            $products = $model->select('id, name, code, barcode, cost_price, price, quantity')
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
                ->select('id, name, code, barcode, cost_price, price, quantity')
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
                'text' => $p['name'] . ' (Stock: ' . $p['quantity'] . ')',
                'price' => $p['price'],
                'cost_price' => $p['cost_price'],
                'quantity' => $p['quantity'],
            ];
        }
        return $this->response->setJSON($results);
    }
    // Endpoint to get product by barcode
    public function barcode()
    {
        $barcode = $this->request->getGet('barcode');
        $model = new \App\Models\M_products();
        $product = $model->where('barcode', $barcode)->select('id, name, code, cost_price, price, quantity')->forStore()->first();
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

        $filteredBuilder->select('id, name, cost_price, price, quantity, IFNULL(description, "") as description, code, barcode');

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

        if ($this->request->getMethod() === 'get') {
            return view('products/import', [
                'title' => 'Import Products',
            ]);
        }

        $validation = \Config\Services::validation();
        $rules = [
            'csv_file' => [
                'label' => 'CSV File',
                'rules' => 'uploaded[csv_file]|max_size[csv_file,5120]|ext_in[csv_file,csv,txt]',
            ],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', implode('\n', $validation->getErrors()))->withInput();
        }

        $file = $this->request->getFile('csv_file');
        if (! $file->isValid()) {
            return redirect()->back()->with('error', 'Invalid upload.')->withInput();
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
            $key = strtolower(trim($h));
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
        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        // Allowed optional synonyms for price
        $priceKeys = ['price', 'unit_price', 'selling_price'];

        $rowNum = 1; // start after header
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, function ($v) {
                return trim((string) $v) !== '';
            })) === 0) {
                // skip completely empty rows
                continue;
            }

            $get = function ($key, $default = null) use ($map, $row) {
                if (isset($map[$key])) {
                    return trim((string) ($row[$map[$key]] ?? ''));
                }
                return $default;
            };

            $name = $get('name');
            if ($name === '' || $name === null) {
                $skipped++;
                $errors[] = "Row {$rowNum}: missing name";
                continue;
            }

            // Determine price (accept price or unit_price)
            $price = null;
            foreach ($priceKeys as $pk) {
                $val = $get($pk);
                if ($val !== null && $val !== '') {
                    $price = $val;
                    break;
                }
            }

            // cost_price is optional but supported
            $costPrice = $get('cost_price');

            // Optional columns
            $code = $get('code');
            $barcode = $get('barcode');
            $quantity = $get('quantity');
            $stockAlert = $get('stock_alert');
            $description = $get('description');

            // Sanitize numeric fields
            $price = $price !== null && $price !== '' ? (float) $price : null;
            $costPrice = $costPrice !== null && $costPrice !== '' ? (float) $costPrice : null;
            $quantity = $quantity !== null && $quantity !== '' ? (float) $quantity : null;
            $stockAlert = $stockAlert !== null && $stockAlert !== '' ? (float) $stockAlert : null;

            // Build data payload
            $data = [
                'name' => $name,
                'price' => $price ?? 0,
                'cost_price' => $costPrice ?? 0,
                'quantity' => $quantity ?? 0,
                'stock_alert' => $stockAlert ?? 10,
                'description' => $description ?? null,
                'code' => $code ?? null,
                'barcode' => $barcode ?? null,
                'store_id' => $storeId,
            ];

            try {
                // Upsert by barcode (preferred) or code within store
                $db->transStart();

                $existing = null;
                if (! empty($barcode)) {
                    $existing = $productsModel->where('store_id', $storeId)->where('barcode', $barcode)->first();
                }
                if ($existing === null && ! empty($code)) {
                    $existing = $productsModel->where('store_id', $storeId)->where('code', $code)->first();
                }

                if ($existing) {
                    $productsModel->update($existing['id'], $data);
                    $updated++;
                } else {
                    $productsModel->insert($data);
                    $inserted++;
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
}
