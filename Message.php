<?php
namespace src;

class Message{

    public int $random_id;

    public function __construct(
        public int|string $peer_id,
        public ?string $domain = null,
        public ?int $chat_id = null,
        public ?string $message = null,
        public ?string $lat = null,
        public ?string $long = null,
        public ?string $attachment = null,
        public ?int $reply_to = null,
        public ?string $forward_messages = null,
        public ?array $forward = null,
        public ?int $sticker_id = null,
        public ?int $group_id = null,
        public ?array $keyboard = null,
        public ?array $template = null,
        public ?array $payload = null,
        public ?array $content_source = null,
        public ?int $dont_parse_links = null,
        public ?int $disable_mentions = null,
        public ?string $intent = null,
        public ?int $subscribe_id = null
    )
    {
        $this->random_id = random_int(-2147483648, 2147483647);
    }

    public function toArray(): array
    {
        $mas = get_object_vars($this);
        $array_to_return = [];
        foreach ($mas as $name => $value){
            if ($value !== null){
                $array_to_return += [$name => $value];
            }
        }
        return $array_to_return;
    }
}