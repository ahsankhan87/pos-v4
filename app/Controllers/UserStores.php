<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StoreModel;

class UserStores extends BaseController
{
    protected $userModel;
    protected $storeModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->storeModel = new StoreModel();
    }

    public function manage($userId)
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $currentStores = $this->userModel->getUserStores($userId);
        $allStores = $this->storeModel->where('is_active', 1)->findAll();

        $data = [
            'title' => 'Manage User Stores',
            'user' => $user,
            'currentStores' => array_column($currentStores, 'id'),
            'stores' => $allStores
        ];

        return  view('user_stores/manage', $data);
    }

    public function update($userId)
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $storeIds = $this->request->getPost('stores') ?? [];

        // Get current stores
        $currentStores = $this->userModel->getUserStores($userId);
        $currentStoreIds = array_column($currentStores, 'id');

        // Stores to add
        $toAdd = array_diff($storeIds, $currentStoreIds);
        foreach ($toAdd as $storeId) {
            $this->userModel->addStoreToUser($userId, $storeId);
        }

        // Stores to remove
        $toRemove = array_diff($currentStoreIds, $storeIds);
        foreach ($toRemove as $storeId) {
            $this->userModel->removeStoreFromUser($userId, $storeId);
        }

        return redirect()->to("/users")->with('message', 'User stores updated successfully');
    }
}
