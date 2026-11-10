<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LaundryAssistantConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['laundry-assistant.openai_enabled' => true]);
    }

    public function test_free_mode_check_succeeds_without_calling_openai_even_with_a_saved_key(): void
    {
        config(['laundry-assistant.openai_enabled' => false, 'laundry-assistant.openai_key' => 'saved-key-must-not-be-used']);
        Http::preventStrayRequests();
        $this->artisan('laundry:assistant-check')
            ->expectsOutputToContain('Free FAQ mode is active. No OpenAI requests are made.')
            ->doesntExpectOutputToContain('saved-key-must-not-be-used')->assertSuccessful();
        Http::assertNothingSent();
    }

    public function test_a_missing_key_explains_setup_without_making_a_request(): void
    {
        config(['laundry-assistant.openai_key' => '']);
        Http::preventStrayRequests();
        $this->artisan('laundry:assistant-check')
            ->expectsOutputToContain('OPENAI_API_KEY is missing')
            ->assertFailed();
        Http::assertNothingSent();
    }

    public function test_a_successful_check_uses_the_server_key_and_does_not_print_provider_content(): void
    {
        config(['laundry-assistant.openai_key' => 'private-test-key']);
        Http::fake(['api.openai.com/v1/responses' => Http::response([
            'status' => 'completed',
            'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'OK']]]],
        ])]);
        $this->artisan('laundry:assistant-check')
            ->expectsOutputToContain('OpenAI connection verified.')
            ->doesntExpectOutputToContain('private-test-key')
            ->assertSuccessful();
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer private-test-key')
            && $request['store'] === false && $request['max_output_tokens'] === 64);
        Http::assertSentCount(1);
    }

    public function test_rejected_keys_model_access_and_quota_errors_have_actionable_messages(): void
    {
        config(['laundry-assistant.openai_key' => 'private-test-key']);
        $responses = Http::sequence();
        foreach ([401, 403, 404, 429, 500] as $status) {
            $responses->push(['error' => ['message' => 'private-provider-details']], $status);
        }
        Http::fake(['api.openai.com/v1/responses' => $responses]);
        foreach ([401 => 'rejected the API key', 403 => 'denied access', 404 => 'model is unavailable', 429 => 'quota or rate limit', 500 => 'HTTP 500'] as $status => $message) {
            $this->artisan('laundry:assistant-check')
                ->expectsOutputToContain($message)
                ->doesntExpectOutputToContain('private-provider-details')
                ->doesntExpectOutputToContain('private-test-key')
                ->assertFailed();
        }
    }

    public function test_connection_failure_reports_a_problem_without_exposing_internal_details(): void
    {
        config(['laundry-assistant.openai_key' => 'private-test-key']);
        Http::fake(fn () => throw new ConnectionException('private-transport-details'));
        $this->artisan('laundry:assistant-check')
            ->expectsOutputToContain('Cannot connect to OpenAI')
            ->doesntExpectOutputToContain('private-transport-details')
            ->assertFailed();
    }

    public function test_billing_errors_are_distinguished_from_temporary_rate_limits(): void
    {
        config(['laundry-assistant.openai_key' => 'private-test-key']);
        $cases = [
            'credit_balance_exhausted' => 'API credits are exhausted',
            'organization_spend_limit_exceeded' => 'organization spend limit',
            'project_spend_limit_exceeded' => 'project spend limit',
            'organization_usage_limit_exceeded' => 'organization usage limit',
            'insufficient_quota' => 'OpenAI returned insufficient_quota',
        ];
        $responses = Http::sequence();
        foreach (array_keys($cases) as $code) {
            $responses->push(['error' => ['code' => $code, 'type' => 'insufficient_quota', 'message' => 'private-provider-details']], 429);
        }
        Http::fake(['api.openai.com/v1/responses' => $responses]);
        foreach ($cases as $expected) {
            $this->artisan('laundry:assistant-check')
                ->expectsOutputToContain($expected)
                ->doesntExpectOutputToContain('private-provider-details')
                ->assertFailed();
        }
    }

    public function test_an_incomplete_response_is_not_reported_as_a_working_connection(): void
    {
        config(['laundry-assistant.openai_key' => 'private-test-key']);
        Http::fake(['api.openai.com/v1/responses' => Http::response(['status' => 'incomplete', 'output' => []])]);
        $this->artisan('laundry:assistant-check')
            ->expectsOutputToContain('did not return a complete text response')
            ->assertFailed();
    }
}
