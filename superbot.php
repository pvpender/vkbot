<?php
namespace src;
require_once "vendor/autoload.php";
require_once "./Message.php";
require_once "./Constants.php";
use VK;
use src\Message;


$vk = new VK\Client\VKApiClient();


$vk->groups()->setLongPollSettings(API_KEY, [
    "group_id" => GROUP_ID,
    "enabled" => 1,
    "message_new" => 1,
    "message_reply" => 1,
]);


class CallbackApiMyHandler extends VK\CallbackApi\VKCallbackApiHandler {
    private \VK\Client\VKApiClient $vk;

    public function __construct(VK\Client\VKApiClient $vkclient)
    {
        $this->vk = $vkclient;
    }

    public function messageNew(int $group_id, ?string $secret, array $object) {
        $array = [];
        if (!preg_match("[!\S+]", $object["message"]["text"], $array)){
            $this->vk->messages()->send(API_KEY, (new Message(
                peer_id: $object["message"]["from_id"],
                message: "Введена некоманда! Используйте !help чтобы посомотреть список доступных команд"
            ))->toArray());
            return;
        }
        switch ($array[0]){
            case "!help":
                $this->vk->messages()->send(API_KEY, (new Message(
                    peer_id: $object["message"]["from_id"],
                    message: "Список доступных команд:\n!help - список всех команд"
                ))->toArray());
                break;
            case "!Пока":
                $this->vk->messages()->send(API_KEY, (new Message(
                   peer_id: $object["message"]["from_id"],
                   message: "Покеда!"
                ))->toArray());
                break;
            default:
                $this->vk->messages()->send(API_KEY, (new Message(
                    peer_id: $object["message"]["from_id"],
                    message: "Неизвестная команда! Используйте команду !help чтобы посмотреть список доступных команд"
                ))->toArray());
                break;
        }
    }
}


$handler = new CallbackApiMyHandler($vk);
$executor = new VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor($vk, API_KEY, GROUP_ID, $handler);
while (true) {
    $executor->listen();
}
