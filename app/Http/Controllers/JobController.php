<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function index(): Response
    {
        $jobs = Job::query()
            ->where('is_active', true)
            ->orderBy('department')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'slug', 'department', 'location', 'created_at']);

        $groups = $jobs
            ->groupBy(fn (Job $j) => $j->department ?: 'Khác')
            ->map(fn ($items, $dep) => [
                'department' => $dep,
                'jobs' => $items->values(),
            ])
            ->values();

        return Inertia::render('home', [
            'groups' => $groups,
            'totalJobs' => $jobs->count(),
        ]);
    }

    public function show(string $slug): Response
    {
        $job = Job::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['formFields' => fn ($q) => $q->orderBy('order')])
            ->firstOrFail();

        return Inertia::render('jobs/show', [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'department' => $job->department,
                'location' => $job->location,
                'description' => $job->description,
                'requirements' => $job->requirements,
                'fields' => $job->formFields->map(fn ($f) => [
                    'id' => $f->id,
                    'label' => $f->label,
                    'type' => $f->type,
                    'options' => $f->options ?? [],
                    'is_required' => (bool) $f->is_required,
                    'order' => $f->order,
                ]),
            ],
        ]);
    }

    public function apply(Request $request, string $slug): RedirectResponse
    {
        $job = Job::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['formFields' => fn ($q) => $q->orderBy('order')])
            ->firstOrFail();

        $rules = [
            'full_name' => ['required', 'string', 'max:120'],
            'phone' => [
                'required', 'string', 'max:32',
                Rule::unique('candidates', 'phone')
                    ->where(fn ($q) => $q->where('job_id', $job->id)->whereNull('deleted_at')),
            ],
            'email' => ['nullable', 'email', 'max:120'],
            'cv_link' => ['required', 'url', 'max:1024', function ($attr, $value, $fail) {
                $host = parse_url($value, PHP_URL_HOST) ?: '';
                if (! preg_match('/(^|\.)(drive|docs)\.google\.com$/i', $host)) {
                    $fail('Vui lòng dán link Google Drive (drive.google.com hoặc docs.google.com).');
                }
            }],
            'answers' => ['array'],
        ];

        $messages = [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại này đã ứng tuyển vị trí này rồi.',
            'email.email' => 'Email không hợp lệ.',
            'cv_link.required' => 'Vui lòng dán link CV.',
            'cv_link.url' => 'Link CV không hợp lệ.',
        ];

        foreach ($job->formFields as $field) {
            $key = "answers.{$field->id}";
            $rules[$key] = $field->is_required ? ['required'] : ['nullable'];
            $rules[$key][] = 'string';
            $rules[$key][] = 'max:2000';

            if (in_array($field->type, ['select', 'radio'], true) && $field->options) {
                $rules[$key][] = Rule::in($field->options);
            }

            $messages["{$key}.required"] = "Vui lòng trả lời câu hỏi: {$field->label}";
            $messages["{$key}.in"] = "Đáp án không hợp lệ cho: {$field->label}";
        }

        $validated = $request->validate($rules, $messages);

        DB::transaction(function () use ($job, $validated) {
            $candidate = Candidate::create([
                'job_id' => $job->id,
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'cv_link' => $validated['cv_link'],
                'current_stage' => 1,
            ]);

            $answers = $validated['answers'] ?? [];
            $rows = [];
            foreach ($job->formFields as $field) {
                $value = $answers[$field->id] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }
                $rows[] = [
                    'candidate_id' => $candidate->id,
                    'field_id' => $field->id,
                    'answer' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($rows) {
                DB::table('candidate_form_answers')->insert($rows);
            }
        });

        return redirect()
            ->route('jobs.show', $job->slug)
            ->with('success', 'Hồ sơ đã được gửi thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.');
    }
}
