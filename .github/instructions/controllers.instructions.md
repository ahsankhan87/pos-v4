---
description: "Use when writing or editing PHP controllers in app/Controllers/. Enforces forStore() scope on model queries, logAction() after mutations, and permission filter patterns on routes."
applyTo: "app/Controllers/**/*.php"
---

# Controller Conventions

## Multi-Store Scope (`forStore()`)

Every model query that retrieves store-specific data **must** chain `forStore()`. Omitting it leaks data across stores silently.

```php
// ✅ correct
$products = $this->productModel->forStore()->findAll();
$customer = $this->customerModel->forStore()->find($id);

// ✅ correct — explicit store ID when building custom queries
$builder = $this->targetModel
    ->where('pos_employee_sales_targets.store_id', $storeId)
    ->findAll();

// ❌ wrong — no store scope, returns all stores' data
$products = $this->productModel->findAll();
```

**Exceptions**: cross-store admin endpoints that intentionally aggregate all stores. Document the reason explicitly with a comment.

## Audit Logging (`logAction()`)

Call `logAction()` immediately after every successful **create**, **update**, or **delete**. Include the resource name, its ID, and any key identifying field.

```php
// create
$id = $this->model->insert($data, true);
logAction('product_created', 'Product ID: ' . (int) $id . ', Name: ' . $data['name']);

// update
$this->model->update($id, $data);
logAction('product_updated', 'Product ID: ' . (int) $id);

// delete
$this->model->delete($id);
logAction('product_deleted', 'Product ID: ' . (int) $id);
```

- Load the helper in the constructor if not already: `helper(['audit'])`.
- Do **not** log on validation failures or early-return guard clauses — only on committed DB changes.

## Permission Filters on Routes

Every route that reads or mutates data must carry a `permission:` filter. Always apply the most specific permission available.

```php
// read — use module.view
$routes->get('products', 'Products::index', ['filter' => 'permission:products.view']);

// write — use module.create / module.edit / module.delete
$routes->post('products/store', 'Products::store', ['filter' => 'permission:products.create']);
$routes->post('products/update/(:num)', 'Products::update/$1', ['filter' => 'permission:products.edit']);
$routes->post('products/delete/(:num)', 'Products::delete/$1', ['filter' => 'permission:products.delete']);
```

Group routes under `auth` first, then add per-route `permission:` filters for granular control:

```php
$routes->group('products', ['filter' => 'auth'], function ($routes) {
    $routes->get('/',                  'Products::index',     ['filter' => 'permission:products.view']);
    $routes->get('new',                'Products::new',       ['filter' => 'permission:products.create']);
    $routes->post('store',             'Products::store',     ['filter' => 'permission:products.create']);
    $routes->get('edit/(:num)',        'Products::edit/$1',   ['filter' => 'permission:products.edit']);
    $routes->post('update/(:num)',     'Products::update/$1', ['filter' => 'permission:products.edit']);
    $routes->post('delete/(:num)',     'Products::delete/$1', ['filter' => 'permission:products.delete']);
});
```

**In-controller checks** (secondary guard, e.g. inside AJAX handlers):

```php
if (! can('products.edit')) {
    return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
}
```

Role ID `1` or role name `admin` bypasses all permission checks automatically — no special-casing needed.

## Redirect + Flash Pattern

After a mutation always redirect with a flash message, never render a view directly:

```php
return redirect()->to(site_url('products'))->with('success', lang('Products.success_create'));
return redirect()->back()->withInput()->with('error', lang('Products.error_create'));
```
