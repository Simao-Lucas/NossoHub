<?php

namespace App\Http\Controllers;

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

    public function create(): View
    {
        return view('plans.create');
    }

    public function show(PlanItem $planItem): View
    {
        return view('plans.show', [
            'item' => $this->plans->find($planItem->id),
        ]);
    }

    public function edit(PlanItem $planItem): View
    {
        return view('plans.edit', [
            'item' => $this->plans->find($planItem->id),
        ]);
    }

    public function destroy(PlanItem $planItem): RedirectResponse
    {
        $this->plans->delete($planItem);

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plano removido.');
    }
}
