<?php

namespace App\Livewire\Wishlist;

use App\Enums\WishlistCategory;
use App\Enums\WishlistPriority;
use App\Enums\WishlistStatus;
use App\Services\WishlistService;
use Livewire\Component;
use Livewire\Attributes\Url;

class WishlistIndex extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $priority = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $formCategory = 'experience';

    public string $formPriority = 'medium';

    public string $formStatus = 'pending';

    public string $link = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->resetFormFields();
    }

    public function openCreate(): void
    {
        $this->resetFormFields();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function openEdit(int $id, WishlistService $wishlist): void
    {
        $item = $wishlist->find($id);

        $this->editingId = $item->id;
        $this->title = $item->title;
        $this->description = (string) $item->description;
        $this->formCategory = $item->category->value;
        $this->formPriority = $item->priority->value;
        $this->formStatus = $item->status->value;
        $this->link = (string) $item->link;
        $this->notes = (string) $item->notes;
        $this->showForm = true;
    }

    public function save(WishlistService $wishlist): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'formCategory' => ['required', 'in:'.implode(',', array_column(WishlistCategory::cases(), 'value'))],
            'formPriority' => ['required', 'in:'.implode(',', array_column(WishlistPriority::cases(), 'value'))],
            'formStatus' => ['required', 'in:'.implode(',', array_column(WishlistStatus::cases(), 'value'))],
            'link' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'category' => $validated['formCategory'],
            'priority' => $validated['formPriority'],
            'status' => $validated['formStatus'],
            'link' => $validated['link'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->editingId) {
            $wishlist->update($wishlist->find($this->editingId), $payload);
            session()->flash('success', 'Item atualizado.');
        } else {
            $wishlist->create($payload);
            session()->flash('success', 'Item adicionado à wishlist.');
        }

        $this->showForm = false;
        $this->resetFormFields();
    }

    public function delete(int $id, WishlistService $wishlist): void
    {
        $wishlist->delete($wishlist->find($id));
        session()->flash('success', 'Item removido.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetFormFields();
    }

    public function render(WishlistService $wishlist)
    {
        return view('livewire.wishlist.index', [
            'items' => $wishlist->list([
                'search' => $this->search ?: null,
                'category' => $this->category ?: null,
                'status' => $this->status ?: null,
                'priority' => $this->priority ?: null,
            ]),
            'categories' => WishlistCategory::options(),
            'priorities' => WishlistPriority::options(),
            'statuses' => WishlistStatus::options(),
        ]);
    }

    private function resetFormFields(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->formCategory = WishlistCategory::Experience->value;
        $this->formPriority = WishlistPriority::Medium->value;
        $this->formStatus = WishlistStatus::Pending->value;
        $this->link = '';
        $this->notes = '';
        $this->resetValidation();
    }
}
