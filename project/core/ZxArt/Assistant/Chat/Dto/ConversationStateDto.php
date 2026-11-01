<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Assistant\Chat\Rest\ConversationStateRestDto;
use ZxArt\Shared\References\MentionedEntityDto;

#[Map(target: ConversationStateRestDto::class)]
final readonly class ConversationStateDto
{
    /**
     * @param list<MessageDto> $messages those after the seq asked for
     * @param list<PlanDto> $plans every plan of the conversation
     * @param array<string, MentionedEntityDto> $refs the entities the messages and plans mention, by `type:id`
     */
    public function __construct(
        public int $conversationId,
        #[Map(transform: new MapCollection())]
        public array $messages,
        #[Map(transform: new MapCollection())]
        public array $plans,
        public ?RunDto $activeRun,
        #[Map(transform: new MapCollection())]
        public array $refs,
    ) {
    }
}
