<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CheckLaundryAssistant extends Command
{
    protected $signature = 'laundry:assistant-check';

    protected $description = 'Check free FAQ mode, or verify OpenAI when paid AI is explicitly enabled';

    public function handle(): int
    {
        if (! config('laundry-assistant.openai_enabled', false)) {
            $this->info('Free FAQ mode is active. No OpenAI requests are made.');
            $this->line('Open Ask Waku on the main page for booking, prices, tracking, and laundry-care answers.');

            return self::SUCCESS;
        }
        $key = config('laundry-assistant.openai_key');
        if (! is_string($key) || trim($key) === '') {
            $this->error('OPENAI_API_KEY is missing. The website is using FAQ answers.');
            $this->line('Save your key in .env, run php artisan config:clear, then run this check again.');

            return self::FAILURE;
        }

        try {
            // Check authentication, model access and quota without sending visitor or account data.
            $response = Http::withToken($key)->acceptJson()->asJson()
                ->connectTimeout(5)->timeout(config('laundry-assistant.timeout', 20))
                ->post('https://api.openai.com/v1/responses', [
                    'model' => config('laundry-assistant.model'),
                    'input' => 'Reply only with OK.',
                    'max_output_tokens' => 64,
                    'store' => false,
                ]);
        } catch (ConnectionException) {
            $this->error('Cannot connect to OpenAI. Check the internet connection, proxy, and PHP HTTPS certificates.');

            return self::FAILURE;
        }

        if (! $response->successful()) {
            // Never print the provider error body: it can contain sensitive request information.
            $this->error(match ($response->status()) {
                401 => 'OpenAI rejected the API key. Check that it is correct and has not been revoked.',
                403 => 'OpenAI denied access. Check the key and project permissions for Responses requests.',
                404 => 'The configured model is unavailable to this project. Check OPENAI_MODEL and model access.',
                429 => match ($response->json('error.code')) {
                    'credit_balance_exhausted' => 'OpenAI API credits are exhausted. Add credits in API Billing before trying again.',
                    'organization_spend_limit_exceeded' => 'The OpenAI organization spend limit has been reached. Review the organization billing limit.',
                    'project_spend_limit_exceeded' => 'The OpenAI project spend limit has been reached. Review this project\'s billing limit.',
                    'organization_usage_limit_exceeded' => 'The OpenAI organization usage limit has been reached. Request a higher approved usage limit.',
                    'insufficient_quota' => 'OpenAI returned insufficient_quota. Check available API credits and account billing limits before trying again.',
                    default => $response->json('error.type') === 'insufficient_quota'
                        ? 'OpenAI reported insufficient API quota. Check API Billing for available credits and spending limits.'
                        : 'OpenAI reported a quota or rate limit. Check API billing and usage, or retry after the rate limit resets.',
                },
                default => 'OpenAI could not complete the check (HTTP '.$response->status().'). Please retry later.',
            });

            return self::FAILURE;
        }

        foreach ((array) $response->json('output', []) as $item) {
            if (! is_array($item) || ($item['type'] ?? null) !== 'message') {
                continue;
            }
            foreach ((array) ($item['content'] ?? []) as $part) {
                if (is_array($part) && ($part['type'] ?? null) === 'output_text'
                    && is_string($part['text'] ?? null) && trim($part['text']) !== ''
                    && $response->json('status') === 'completed') {
                    $this->info('OpenAI connection verified.');
                    $this->line('Refresh the main page, open Ask Waku, and send a laundry question.');
                    $this->line('Generated replies are labeled "AI answer".');

                    return self::SUCCESS;
                }
            }
        }

        $this->error('OpenAI did not return a complete text response. Check the configured model and try again.');

        return self::FAILURE;
    }
}
