<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobFormField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class JobFormController extends Controller
{
    private const TYPES = ['text', 'textarea', 'select', 'radio', 'date'];

    public function index(Job $job): Response
    {
        $job->load(['formFields' => fn ($q) => $q->orderBy('order')]);

        return Inertia::render('admin/jobs/form-editor', [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'department' => $job->department,
            ],
            'fields' => $job->formFields->map(fn (JobFormField $f) => [
                'id' => $f->id,
                'label' => $f->label,
                'type' => $f->type,
                'options' => $f->options ?? [],
                'is_required' => $f->is_required,
                'order' => $f->order,
            ]),
        ]);
    }

    public function store(Request $request, Job $job): RedirectResponse
    {
        $data = $this->validateField($request);
        $this->ensureLabelUnique($job, $data['label']);

        $data['order'] = ((int) $job->formFields()->max('order')) + 1;

        $job->formFields()->create($data);

        return back()->with('success', 'Đã thêm câu hỏi "'.$data['label'].'".');
    }

    public function update(Request $request, Job $job, JobFormField $field): RedirectResponse
    {
        abort_unless($field->job_id === $job->id, 404);

        $data = $this->validateField($request);
        $this->ensureLabelUnique($job, $data['label'], $field->id);

        $field->update($data);

        return back()->with('success', 'Đã cập nhật câu hỏi.');
    }

    public function destroy(Job $job, JobFormField $field): RedirectResponse
    {
        abort_unless($field->job_id === $job->id, 404);

        $label = $field->label;
        $field->delete();

        return back()->with('success', 'Đã xoá câu hỏi "'.$label.'".');
    }

    public function reorder(Request $request, Job $job): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = collect($data['ids'])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $owned = $job->formFields()->whereIn('id', $ids)->pluck('id')->all();
        if (count($owned) !== $ids->count()) {
            return back()->with('error', 'Có câu hỏi không thuộc vị trí này.');
        }

        DB::transaction(function () use ($ids, $job) {
            foreach ($ids as $i => $id) {
                JobFormField::where('id', $id)
                    ->where('job_id', $job->id)
                    ->update(['order' => $i + 1]);
            }
        });

        return back()->with('success', 'Đã cập nhật thứ tự câu hỏi.');
    }

    private function validateField(Request $request): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:'.implode(',', self::TYPES)],
            'is_required' => ['boolean'],
            'options' => ['nullable', 'array'],
            'options.*' => ['required', 'string', 'max:120'],
        ];

        $messages = [
            'label.required' => 'Vui lòng nhập nhãn câu hỏi.',
            'type.in' => 'Loại câu hỏi không hợp lệ.',
            'options.*.required' => 'Mỗi lựa chọn không được để trống.',
        ];

        $data = $request->validate($rules, $messages);

        if (in_array($data['type'], ['select', 'radio'], true)) {
            $opts = array_values(array_filter($data['options'] ?? [], fn ($o) => trim($o) !== ''));
            if (count($opts) < 2) {
                abort(422, 'Câu hỏi loại lựa chọn cần ít nhất 2 phương án.');
            }
            $data['options'] = $opts;
        } else {
            $data['options'] = null;
        }

        $data['is_required'] = (bool) ($data['is_required'] ?? false);

        return $data;
    }

    private function ensureLabelUnique(Job $job, string $label, ?int $ignoreId = null): void
    {
        $exists = $job->formFields()
            ->whereRaw('LOWER(label) = ?', [mb_strtolower(trim($label))])
            ->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'label' => 'Câu hỏi này đã tồn tại trong vị trí.',
            ]);
        }
    }
}
