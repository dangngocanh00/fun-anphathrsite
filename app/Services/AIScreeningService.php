<?php

namespace App\Services;

use App\Models\Candidate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AIScreeningService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private string $version;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('services.anthropic.api_key', '');
        $this->model = (string) config('services.anthropic.model', 'claude-sonnet-4-20250514');
        $this->baseUrl = rtrim((string) config('services.anthropic.base_url'), '/');
        $this->version = (string) config('services.anthropic.version', '2023-06-01');
        $this->timeout = (int) config('services.anthropic.timeout', 60);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function analyze(Candidate $candidate): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Chưa cấu hình ANTHROPIC_API_KEY trong .env');
        }

        $candidate->loadMissing(['job', 'answers.field']);
        $job = $candidate->job;
        if (! $job) {
            throw new RuntimeException('Ứng viên không gắn với vị trí tuyển dụng nào.');
        }

        $jdBlock = $this->buildJobBlock($job, $candidate);
        $candidateBlock = $this->buildCandidateBlock($candidate);

        $payload = [
            'model' => $this->model,
            'max_tokens' => 1024,
            'system' => [
                [
                    'type' => 'text',
                    'text' => $this->systemPrompt(),
                    'cache_control' => ['type' => 'ephemeral'],
                ],
            ],
            'messages' => [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $jdBlock,
                        'cache_control' => ['type' => 'ephemeral'],
                    ],
                    [
                        'type' => 'text',
                        'text' => $candidateBlock,
                    ],
                ],
            ]],
        ];

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->version,
            'content-type' => 'application/json',
            'anthropic-beta' => 'prompt-caching-2024-07-31',
        ])
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/messages', $payload);

        if (! $response->successful()) {
            Log::warning('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Anthropic API lỗi: HTTP '.$response->status().' — '.$response->body());
        }

        $text = (string) ($response->json('content.0.text') ?? '');
        $parsed = $this->extractJson($text);

        $score = (int) max(0, min(100, $parsed['score'] ?? 0));
        $flags = array_values(array_filter(array_map('strval', $parsed['flags'] ?? []), fn ($s) => $s !== ''));
        $questions = array_values(array_filter(array_map('strval', $parsed['questions'] ?? []), fn ($s) => $s !== ''));

        $candidate->update([
            'ai_score' => $score,
            'ai_flags' => $flags,
            'ai_questions' => $questions,
            'ai_analyzed_at' => now(),
        ]);

        return [
            'score' => $score,
            'flags' => $flags,
            'questions' => $questions,
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Bạn là chuyên viên tuyển dụng cấp cao của AnPhat, đánh giá hồ sơ ứng viên dựa trên Mô tả công việc (JD).

NHIỆM VỤ
- So sánh ứng viên với JD → cho điểm 0–100, liệt kê red flags, gợi ý câu hỏi phỏng vấn.
- Trả về DUY NHẤT một JSON object hợp lệ, không kèm giải thích, không kèm code fence.

ĐỊNH DẠNG OUTPUT (BẮT BUỘC)
{
  "score": <int 0-100>,
  "flags": ["<red flag ngắn gọn>", "..."],
  "questions": ["<câu hỏi phỏng vấn cụ thể>", "..."]
}

HƯỚNG DẪN CHO ĐIỂM
- 85–100: Phù hợp xuất sắc, đáp ứng đa số yêu cầu cốt lõi.
- 70–84: Phù hợp, có thể bù bằng training ngắn.
- 50–69: Tiềm năng nhưng thiếu vài kỹ năng quan trọng.
- 30–49: Yếu, thiếu nhiều yêu cầu cốt lõi.
- 0–29: Không phù hợp.

LƯU Ý
- Mỗi flag và mỗi câu hỏi là một câu tiếng Việt ngắn (≤ 25 từ).
- Tối đa 5 flags và 5 questions.
- Nếu không thể đọc được CV (link không truy cập), điểm không quá 50 và phải có flag "Không xác minh được CV".
PROMPT;
    }

    private function buildJobBlock($job, Candidate $candidate): string
    {
        return collect([
            "## VỊ TRÍ TUYỂN DỤNG",
            "Tiêu đề: {$job->title}",
            "Khối / phòng ban: ".($job->department ?: '—'),
            "Địa điểm: ".($job->location ?: '—'),
            '',
            '### Mô tả công việc',
            $job->description,
            '',
            '### Yêu cầu ứng viên',
            $job->requirements ?: '(không có yêu cầu cụ thể)',
        ])->implode("\n");
    }

    private function buildCandidateBlock(Candidate $candidate): string
    {
        $answers = $candidate->answers->map(function ($a) {
            $label = $a->field?->label ?? '(câu hỏi đã xoá)';
            return "- {$label}: ".($a->answer ?: '(bỏ trống)');
        })->implode("\n");

        return collect([
            '## HỒ SƠ ỨNG VIÊN',
            "Họ tên: {$candidate->full_name}",
            "SĐT: {$candidate->phone}",
            "Email: ".($candidate->email ?: '—'),
            "Link CV (Google Drive): {$candidate->cv_link}",
            '',
            '### Trả lời câu hỏi sơ vấn',
            $answers ?: '(ứng viên không trả lời câu nào)',
            '',
            'Hãy đánh giá ứng viên này theo JD ở trên và trả về JSON đúng định dạng.',
        ])->implode("\n");
    }

    private function extractJson(string $text): array
    {
        $trimmed = trim($text);
        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $trimmed) ?? $trimmed;

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $trimmed, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        throw new RuntimeException('Không parse được JSON từ phản hồi Anthropic: '.substr($text, 0, 200));
    }
}
