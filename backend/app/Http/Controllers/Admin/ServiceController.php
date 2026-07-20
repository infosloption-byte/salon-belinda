<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $categories = ServiceCategory::with('services')->orderBy('sort_order')->get();

        return view('admin.services.index', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'intro' => ['nullable', 'string', 'max:500'],
        ]);

        $category = ServiceCategory::create([
            ...$data,
            'slug' => Str::slug($data['title']),
            'sort_order' => ServiceCategory::max('sort_order') + 1,
        ]);
        ActivityLogger::log('service_category.created', "Added service category \"{$category->title}\"", $category);

        return back()->with('success', 'Category added.');
    }

    public function destroyCategory(ServiceCategory $category): RedirectResponse
    {
        ActivityLogger::log('service_category.deleted', "Deleted service category \"{$category->title}\" and its services", $category);
        $category->delete();

        return back()->with('success', 'Category and its services removed.');
    }

    public function storeService(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:60'],
            'price' => ['required', 'integer', 'min:0'],
            'price_prefix' => ['nullable', 'string', 'max:20'],
        ]);

        $service = Service::create($data + ['sort_order' => 0]);
        ActivityLogger::log('service.created', "Added service \"{$service->name}\"", $service);

        return back()->with('success', 'Service added.');
    }

    public function updateService(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:60'],
            'price' => ['required', 'integer', 'min:0'],
            'price_prefix' => ['nullable', 'string', 'max:20'],
        ]);

        $service->update($data);
        ActivityLogger::log('service.updated', "Updated service \"{$service->name}\"", $service);

        return back()->with('success', 'Service updated.');
    }

    public function destroyService(Service $service): RedirectResponse
    {
        ActivityLogger::log('service.deleted', "Deleted service \"{$service->name}\"", $service);
        $service->delete();

        return back()->with('success', 'Service removed.');
    }
}
