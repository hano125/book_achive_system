<?php

namespace App\Livewire\Books;

use App\Models\BookCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.Frontend.master')]
#[Title('التصنيفات')]
class CategoryManager extends Component
{
    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public ?string $description = null;

    public bool $isActive = true;

    public function save(): void
    {
        $validated = $this->validate();

        BookCategory::query()->updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $validated['name'],
                'description' => $validated['description'],
                'is_active' => $validated['isActive'],
            ],
        );

        session()->flash('success', $this->editingId ? 'تم تعديل التصنيف.' : 'تمت إضافة التصنيف.');
        $this->resetForm();
    }

    public function edit(int $categoryId): void
    {
        $category = BookCategory::query()->findOrFail($categoryId);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->isActive = $category->is_active;
    }

    public function delete(int $categoryId): void
    {
        BookCategory::query()->findOrFail($categoryId)->delete();
        session()->flash('success', 'تم نقل التصنيف إلى المحذوفات.');
        $this->resetForm();
    }

    public function restore(int $categoryId): void
    {
        BookCategory::onlyTrashed()->findOrFail($categoryId)->restore();
        session()->flash('success', 'تم استرجاع التصنيف.');
    }

    public function forceDelete(int $categoryId): void
    {
        $category = BookCategory::onlyTrashed()->findOrFail($categoryId);

        if ($category->books()->withTrashed()->exists()) {
            $this->addError('categoryDelete', 'لا يمكن حذف تصنيف يحتوي على كتب نهائياً.');

            return;
        }

        $category->forceDelete();
        session()->flash('success', 'تم حذف التصنيف نهائياً.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'description']);
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.books.category-manager', [
            'categories' => BookCategory::withTrashed()
                ->withCount(['books' => fn (Builder $query) => $query->withTrashed()])
                ->orderBy('name')
                ->get(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('book_categories', 'name')->ignore($this->editingId),
            ],
            'description' => ['nullable', 'string'],
            'isActive' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'اسم التصنيف مطلوب.',
            'name.max' => 'يجب ألا يتجاوز اسم التصنيف 255 حرفاً.',
            'name.unique' => 'اسم التصنيف مستخدم مسبقاً، ويمكن استرجاع التصنيف المحذوف.',
            'description.string' => 'يجب أن يكون الوصف نصاً.',
            'isActive.boolean' => 'قيمة حالة التصنيف غير صالحة.',
        ];
    }
}
