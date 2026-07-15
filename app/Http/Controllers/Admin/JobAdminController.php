<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JobAdminController extends Controller
{
    public function index(): Response
    {
        $jobs = Job::query()
            ->withCount(['candidates', 'formFields'])
            ->orderByDesc('is_active')
            ->orderBy('department')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'department', 'location', 'commission_amount', 'is_active', 'created_at'])
            ->map(fn (Job $j) => [
                'id' => $j->id,
                'title' => $j->title,
                'slug' => $j->slug,
                'department' => $j->department,
                'location' => $j->location,
                'commission_amount' => $j->commission_amount,
                'is_active' => $j->is_active,
                'candidates_count' => $j->candidates_count,
                'fields_count' => $j->form_fields_count,
                'created_at' => $j->created_at->toIso8601String(),
            ]);

        return Inertia::render('admin/jobs/index', [
            'jobs' => $jobs,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/jobs/create-edit', [
            'job' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['is_active'] = true;

        $job = Job::create($data);

        return redirect()
            ->route('admin.jobs.form', $job->id)
            ->with('success', "Đã tạo vị trí \"{$job->title}\". Cấu hình form sơ vấn ngay nào.");
    }

    public function edit(Job $job): Response
    {
        return Inertia::render('admin/jobs/create-edit', [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'department' => $job->department,
                'location' => $job->location,
                'description' => $job->description,
                'requirements' => $job->requirements,
                'commission_amount' => $job->commission_amount,
                'is_active' => $job->is_active,
            ],
        ]);
    }

    public function update(Request $request, Job $job): RedirectResponse
    {
        $data = $this->validateData($request, $job);

        if ($data['title'] !== $job->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $job->id);
        }

        $job->update($data);

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', "Đã cập nhật \"{$job->title}\".");
    }

    public function toggle(Job $job): RedirectResponse
    {
        $job->update(['is_active' => ! $job->is_active]);

        $state = $job->is_active ? 'mở tuyển lại' : 'tạm dừng';

        return back()->with('success', "Đã {$state} \"{$job->title}\".");
    }

    public function destroy(Job $job): RedirectResponse
    {
        $title = $job->title;
        $job->delete();

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', "Đã xoá \"{$title}\". Có thể khôi phục từ lịch sử.");
    }

    private const ALLOWED_TAGS = '<b><strong><i><em><u><br>';

    private function validateData(Request $request, ?Job $job = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'department' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'commission_amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
        ], [
            'title.required' => 'Vui lòng nhập tên vị trí.',
            'description.required' => 'Vui lòng nhập mô tả công việc.',
            'commission_amount.required' => 'Vui lòng nhập đơn giá hoa hồng.',
            'commission_amount.integer' => 'Đơn giá phải là số nguyên (VND).',
        ]);

        foreach (['title', 'department', 'location', 'description', 'requirements'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = strip_tags($data[$field], self::ALLOWED_TAGS);
            }
        }

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug(strip_tags($title));
        if ($base === '') {
            $base = 'vi-tri-'.now()->timestamp;
        }

        $slug = $base;
        $i = 1;
        while (Job::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists()
        ) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }
}
