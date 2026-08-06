<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plan\StorePlanItemRequest;
use App\Http\Requests\Plan\UpdatePlanItemRequest;
use App\Models\PlanItem;
use App\Services\PlanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PlanController extends Controller
{
    public function __construct(
        private readonly PlanService $plans,
    ) {}

    public function index(): View
    {
        return view('plans.index');
    }

    public function store(StorePlanItemRequest $request): RedirectResponse
    {
        $this->plans->create($request->validated());

        return redirect()
            ->route('plans.index')
            ->with('success', 'Item adicionado aos planos.');
    }

    public function update(UpdatePlanItemRequest $request, PlanItem $planItem): RedirectResponse
    {
        $this->plans->update($planItem, $request->validated());

        return redirect()
            ->route('plans.index')
            ->with('success', 'Item atualizado.');
    }

    public function destroy(PlanItem $planItem): RedirectResponse
    {
        $this->plans->delete($planItem);

        return redirect()
            ->route('plans.index')
            ->with('success', 'Item removido.');
    }
}
