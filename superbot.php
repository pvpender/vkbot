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
                    message: "Список доступных команд:\n!help - список всех команд\n!film - получить случайный фильм
                    !filmsettings - выставить критерии поиска случайного фильма\n!book - случайная книга\n!booksettings - выставить критерии поиска случайной книги"
                ))->toArray());
                break;
            case "!film":
                $response = file_get_contents("https://www.kinopoisk.ru/chance/?token=YO7-kO7KQxekyyv2nVgG_2cqQj-Zgj5nm0NlrClAru8&item=true&not_show_rated=false&count=5&genre%5B%5D=1750&genre%5B%5D=22&genre%5B%5D=3&genre%5B%5D=13&genre%5B%5D=19&genre%5B%5D=17&genre%5B%5D=456&genre%5B%5D=12&genre%5B%5D=8&genre%5B%5D=23&genre%5B%5D=6&genre%5B%5D=15&genre%5B%5D=16&genre%5B%5D=7&genre%5B%5D=21&genre%5B%5D=14&genre%5B%5D=9&genre%5B%5D=10&genre%5B%5D=11&genre%5B%5D=24&genre%5B%5D=4&genre%5B%5D=1&genre%5B%5D=2&genre%5B%5D=18&genre%5B%5D=5&max_years=2023&min_years=1920");
                $obj = json_decode($response, associative: true);
                $filmName = [];
                $rating = [];
                preg_match("#filmName: '[\S\s]+?',#u", $obj[0], $filmName);
                preg_match("#rating: [\S\s]+?,#u", $obj[0], $rating);
                $this->vk->messages()->send(API_KEY, (new Message(
                    peer_id: $object["message"]["from_id"],
                    message: "Название: ".substr(
                        $filmName[0],
                        strpos($filmName[0],"'")+1,
                        strrpos($filmName[0], "'") - strpos($filmName[0],"'") - 1
                    )."\nРейтинг:".substr(
                        $rating[0],
                        7,
                        6
                    )
                ))->toArray());
                break;
            case "!filmsettings":
                break;
            case "!book":
                $response = file_get_contents("https://readly.ru/books/i_am_lucky/?show=1&genre=180");
                $title = [];
                $author = [];
                $rating = [];
                preg_match('#<a href="/book/\d+/">[ a-zA-Zа-яА-Я0-9:,\-.\(\)!?]+?</a>#u', $response, $title);
                preg_match('#<a href="/author/\d+/">[ а-яА-Я\-]+</a>#u', $response, $author);
                preg_match('#<span class="book-profile--rate">[\d,]+</span>#u', $response, $rating);
                $this->vk->messages()->send(API_KEY, (new Message(
                    peer_id: $object["message"]["from_id"],
                    message: "Название: ".substr(
                        $title[0],
                        strpos($title[0], ">") + 1,
                        strrpos($title[0], "<") - strpos($title[0], ">") - 1
                    )."\nАвтор: ".substr(
                        $author[0],
                        strpos($author[0], ">") + 1,
                        strrpos($author[0], "<") - strpos($author[0], ">") - 1
                    )."\nРейтинг: ".substr(
                        $rating[0],
                        strpos($rating[0], ">") + 1,
                        strrpos($rating[0], "<") - strpos($rating[0], ">") - 1
                    )
                ))->toArray());
                break;
            case "!booksettings":
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
