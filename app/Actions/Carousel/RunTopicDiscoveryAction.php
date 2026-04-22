<?php

namespace App\Actions\Carousel;

use App\Models\CarouselTopicSuggestion;
use App\Services\TopicDiscoveryAgentService;
use Illuminate\Support\Str;

class RunTopicDiscoveryAction
{
    public function __construct(
        private readonly TopicDiscoveryAgentService $discovery
    ) {}

    /**
     * Discover topics from the given sources and persist them as CarouselTopicSuggestion records.
     *
     * @param  array  $sources   e.g. ['web', 'blog', 'manual']
     * @param  string $freeText  Optional user-provided seed topic
     * @param  int    $userId
     * @return array{session_id: string, count: int}
     */
    public function execute(array $sources, string $freeText = '', int $userId = 0): array
    {
        $sessionId = (string) Str::uuid();

        $topics = $this->discovery->discover($sources, $freeText);

        foreach (array_values($topics) as $i => $topic) {
            CarouselTopicSuggestion::create([
                'session_id'          => $sessionId,
                'source'              => $topic['source'],
                'title'               => $topic['title'],
                'description'         => $topic['description'],
                'reasoning'           => $topic['reasoning'],
                'suggested_type'      => $topic['type'],
                'suggested_keywords'  => $topic['keywords'],
                'relevance_score'     => $topic['relevance_score'],
                'priority'            => $i + 1,
                'status'              => 'pending',
                'created_by'          => $userId ?: null,
            ]);
        }

        return ['session_id' => $sessionId, 'count' => count($topics)];
    }
}
