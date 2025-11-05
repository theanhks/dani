<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreContractRequest;
use App\Http\Requests\Admin\UpdateContractRequest;
use App\Services\Admin\ContractService;
use App\Services\Admin\ContractExpirationService;

class ContractController extends Controller
{
    protected $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contracts = $this->contractService->getPaginate();
        return view('admin.contracts.index', compact('contracts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.contracts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractRequest $request)
    {
        $contract = $this->contractService->create($request->validated());

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Tạo hợp đồng thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contract = $this->contractService->show($id);
        return view('admin.contracts.show', compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contract = $this->contractService->show($id);
        return view('admin.contracts.edit', compact('contract'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractRequest $request, string $id)
    {
        $this->contractService->update($id, $request->validated());

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Cập nhật hợp đồng thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->contractService->delete($id);

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Xóa hợp đồng thành công!');
    }

    /**
     * Check and send notification for expired contracts
     */
    public function checkExpired()
    {
        try {
            $contractExpirationService = app(ContractExpirationService::class);
            $result = $contractExpirationService->checkAndSendExpiredContracts();
            
            if ($result['success']) {
                $message = $result['message'];
                if (isset($result['count']) && $result['count'] > 0) {
                    $message .= ' (Đã gửi thông báo cho ' . $result['count'] . ' hợp đồng)';
                }
                return redirect()->route('admin.contracts.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('admin.contracts.index')
                    ->with('error', $result['message'] ?? 'Có lỗi xảy ra khi gửi thông báo');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to check expired contracts', [
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('admin.contracts.index')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
