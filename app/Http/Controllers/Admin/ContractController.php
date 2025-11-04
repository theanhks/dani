<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreContractRequest;
use App\Http\Requests\Admin\UpdateContractRequest;
use App\Services\Admin\ContractService;

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
}
