<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->trim()->value();

        $query = Category::query()
            ->withCount('equipments')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $categories = $query->paginate(10)->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'search' => $search,
            'activePage' => 'categories',
        ]);
    }

    public function create()
    {
        return view('admin.categories.create', [
            'activePage' => 'categories',
        ]);
    }

    public function store(CategoryStoreRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?? null, $data['name']);

        $category = Category::create($data);
        admin_audit('category.store', 'categories', $category->id, [
            'name' => $category->name,
            'slug' => $category->slug,
        ], auth('admin')->id());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', __('Category berhasil ditambahkan.'));
    }

    public function edit(string $slug)
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        return view('admin.categories.edit', [
            'category' => $category,
            'activePage' => 'categories',
        ]);
    }

    public function update(CategoryUpdateRequest $request, string $slug)
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?? null, $data['name'], $category->id);

        $category->update($data);
        admin_audit('category.update', 'categories', $category->id, $data, auth('admin')->id());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', __('Category berhasil diperbarui.'));
    }

    public function destroy(string $slug)
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();
        $snapshot = $category->only(['id', 'name', 'slug']);
        $category->delete();
        admin_audit('category.destroy', 'categories', $snapshot['id'], $snapshot, auth('admin')->id());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', __('Category berhasil dihapus.'));
    }

    private function generateUniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name);
        if ($base === '') {
            $base = 'category';
        }

        $candidate = $base;
        $counter = 2;

        while ($this->slugExists($candidate, $ignoreId)) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Category::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }
}
