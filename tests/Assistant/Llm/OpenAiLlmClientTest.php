<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Llm;

use OpenAI\Resources\Responses;
use OpenAI\Responses\Responses\CreateResponse;
use OpenAI\Testing\ClientFake;
use OpenAI\Testing\Enums\OverrideStrategy;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ZxArt\Assistant\Llm\LlmException;
use ZxArt\Assistant\Llm\LlmMessageDto;
use ZxArt\Assistant\Llm\LlmReplyDto;
use ZxArt\Assistant\Llm\LlmRole;
use ZxArt\Assistant\Llm\LlmToolCallDto;
use ZxArt\Assistant\Llm\LlmToolDto;
use ZxArt\Assistant\Llm\OpenAiLlmClient;
use ZxArt\Logs\Log;
use Monolog\Logger;
use ZxArt\Shared\Images\ImageDto;

/**
 * The conversation as the Responses API takes it, and its answer read back.
 */
final class OpenAiLlmClientTest extends TestCase
{
    private string $logDirectory;

    #[\Override]
    protected function setUp(): void
    {
        $this->logDirectory = sys_get_temp_dir() . '/assistant-log-test-' . uniqid('', true) . '/';
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach (glob($this->logDirectory . '*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->logDirectory)) {
            rmdir($this->logDirectory);
        }
    }

    public function testEachExchangeIsLoggedUnderItsRunWithImagesLeftOut(): void
    {
        $client = new ClientFake([CreateResponse::fake()]);

        $this->client($client)->complete([
            new LlmMessageDto(LlmRole::User, 'Look', images: [new ImageDto('image/png', 'AAAA')]),
        ], [], 42);

        $logged = $this->logged();
        self::assertStringContainsString('[42] : request', $logged);
        self::assertStringContainsString('"model": "gpt-6-luna"', $logged);
        self::assertStringContainsString('"image_url": "<image/png, 3 bytes>"', $logged);
        self::assertStringNotContainsString('AAAA', $logged);
        self::assertStringContainsString('[42] : response', $logged);
        self::assertStringContainsString('"id": "resp_67ccf18ef5fc8190b16dbee19bc54e5f087bb177ab789d5c"', $logged);
    }

    public function testAFailedExchangeIsLoggedWithItsError(): void
    {
        $client = new ClientFake([new RuntimeException('timeout')]);

        try {
            $this->client($client)->complete([new LlmMessageDto(LlmRole::User, 'hi')], [], 42);
        } catch (LlmException) {
        }

        self::assertStringContainsString('[42] : failed', $this->logged());
        self::assertStringContainsString('timeout', $this->logged());
    }

    public function testTheMessagesAndToolsAreSentAsInputItems(): void
    {
        $client = new ClientFake([CreateResponse::fake()]);

        $this->client($client)->complete([
            new LlmMessageDto(LlmRole::System, 'Be brief'),
            new LlmMessageDto(LlmRole::Assistant, 'Searching', [new LlmToolCallDto('call_1', 'search', '{"query":"Elite"}')]),
            new LlmMessageDto(LlmRole::Tool, '{"found":[]}', toolCallId: 'call_1'),
            new LlmMessageDto(LlmRole::User, 'Look', images: [new ImageDto('image/png', 'AAAA')]),
        ], [new LlmToolDto('search', 'Finds', ['type' => 'object'])], 1);

        $client->assertSent(Responses::class, static fn(string $method, array $parameters): bool => $method === 'create' && $parameters === [
            'model' => 'gpt-6-luna',
            'store' => false,
            'input' => [
                ['role' => 'system', 'content' => 'Be brief'],
                ['role' => 'assistant', 'content' => 'Searching'],
                ['type' => 'function_call', 'call_id' => 'call_1', 'name' => 'search', 'arguments' => '{"query":"Elite"}'],
                ['type' => 'function_call_output', 'call_id' => 'call_1', 'output' => '{"found":[]}'],
                ['role' => 'user', 'content' => [
                    ['type' => 'input_text', 'text' => 'Look'],
                    ['type' => 'input_image', 'image_url' => 'data:image/png;base64,AAAA'],
                ]],
            ],
            'tools' => [['type' => 'function', 'name' => 'search', 'description' => 'Finds', 'parameters' => ['type' => 'object']]],
        ]);
    }

    public function testTheAnswerIsReadWithItsToolCallsAndTokens(): void
    {
        $client = new ClientFake([CreateResponse::fake([
            'output' => [
                ['type' => 'reasoning', 'id' => 'rs_1', 'summary' => []],
                ['type' => 'function_call', 'id' => 'fc_1', 'call_id' => 'call_9', 'name' => 'search', 'arguments' => '{}', 'status' => 'completed'],
            ],
            'usage' => [
                'input_tokens' => 120,
                'input_tokens_details' => ['cached_tokens' => 0],
                'output_tokens' => 7,
                'output_tokens_details' => ['reasoning_tokens' => 0],
                'total_tokens' => 127,
            ],
        ], strategy: OverrideStrategy::Replace)]);

        self::assertEquals(
            new LlmReplyDto('', [new LlmToolCallDto('call_9', 'search', '{}')], 120, 7),
            $this->client($client)->complete([new LlmMessageDto(LlmRole::User, 'hi')], [], 1),
        );
    }

    public function testAFailedRequestIsAModelFailure(): void
    {
        $client = new ClientFake([new RuntimeException('timeout')]);

        $this->expectException(LlmException::class);
        $this->expectExceptionMessage('The model could not be asked: timeout');

        $this->client($client)->complete([new LlmMessageDto(LlmRole::User, 'hi')], [], 1);
    }

    private function client(ClientFake $client): OpenAiLlmClient
    {
        return new OpenAiLlmClient($client, 'gpt-6-luna', new Log(new Logger('assistant'), $this->logDirectory));
    }

    private function logged(): string
    {
        return implode('', array_map('file_get_contents', glob($this->logDirectory . '*.log') ?: []));
    }
}
