<?php
require __DIR__ . '/../vendor/autoload.php';
 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "ux2r+507FdAh17ZOuzppu4U+OLQJTRe6lzJhi6M92218D72VcHCX2TerJKUDEA+rz6hizCE2Hl5qfTN0+ewJYG8BjljrIxyR4ig2as3bh23nn03C20pxLykzsdRHJhKHeFvgjgq1cjuWYNXav2OzrgdB04t89/1O/w1cDnyilFU=";
$channel_secret = "7801f8077daf4e86014a2c4aa6d69127";
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
$app = AppFactory::create();
$app->setBasePath("/public");
 
$app->get('/', function (Request $request, Response $response, $args) {
    $userId = $event['source']['userId'];
    $getprofile = $bot->getProfile($userId);
    $profile = $getprofile->getJSONDecodedBody();
    $greetings = new TextMessageBuilder("Halo selamat datang di Virtual Restaurant, saya adalah aplikasi belanja online makanan langsung ke tempat tinggal anda. Gunakan saya sebaik mungkin ya.\n\nJangan lupa setiap hari senin dan rabu akan ada promo, lho! Dan akan ada hadiah menarik bagi kamu yang paling banyak menyebarkan aplikasi ini. Jadi tunggu apalagi, yuk pakai Virtual Restaurant.\n\nKetik keyword di bawah ini untuk menggunakan aplikasi Virtual Restaurant:\n\n1. Buka menu\n2. Pesan sekarang\n3. Help");

    $result = $bot->replyMessage($event['replyToken'], $greetings);
    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});
 
// buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    // get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);
 
    if ($pass_signature === false) {
        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }
    
    // kode aplikasi nanti disini
    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if($event['type'] == 'join'){
                $userId = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile = $getprofile->getJSONDecodedBody();
                        $greetings = new TextMessageBuilder("Halo semuanya, terima kasih sudah menambahkan saya ke ruangan ini.");

                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                        return $response
                            ->withHeader('Content-Type', 'application/json')
                            ->withStatus($result->getHTTPStatus());
            }

            if ($event['type'] == 'message')
            {
                //reply message
                if ($event['message']['type'] == 'text') {
                    if (strtolower($event['message']['text']) == 'user id') {

                        $result = $bot->replyText($event['replyToken'], $event['source']['userId']);

                    } 
                    elseif (strtolower($event['message']['text']) == 'buka menu') {

                        $flexTemplate = file_get_contents("../flex_message.json"); // template flex message
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Test Flex Message',
                                    'contents' => json_decode($flexTemplate)
                                ]
                            ],
                        ]);

                    } 
                    elseif (strtolower($event['message']['text']) == 'pesan sekarang') {
                        $userId = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile = $getprofile->getJSONDecodedBody();
                        $greetings = new TextMessageBuilder("Silakan klik link di bawah ini:\n\nhttps://liff.line.me/1655338109-RpZmDMGN");

                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                        return $response
                            ->withHeader('Content-Type', 'application/json')
                            ->withStatus($result->getHTTPStatus());
                    } 
                    elseif (strtolower($event['message']['text']) == 'help') {
                        $userId = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile = $getprofile->getJSONDecodedBody();
                        $greetings = new TextMessageBuilder("Halo selamat datang di Virtual Restaurant, " . $profile['displayName'] . "\n\nKetik keyword di bawah ini untuk menggunakan aplikasi Virtual Restaurant:\n\n1. Buka menu\n2. Pesan sekarang\n3. Help");

                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                        return $response
                            ->withHeader('Content-Type', 'application/json')
                            ->withStatus($result->getHTTPStatus());
                    } 
                    else {
                        // send same message as reply to user
                        $userId = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile = $getprofile->getJSONDecodedBody();
                        $greetings = new TextMessageBuilder("Keyword yang anda masukkan tidak tersedia, silakan ketik 'Help' untuk bantuan.");

                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                        return $response
                            ->withHeader('Content-Type', 'application/json')
                            ->withStatus($result->getHTTPStatus());
                    }


                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);


                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                } 
                //group room
                elseif (
                    $event['source']['type'] == 'group' or
                    $event['source']['type'] == 'room'
                ) {
                    //message from group / room
                    if ($event['source']['userId']) {

                        $userId = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile = $getprofile->getJSONDecodedBody();
                        $greetings = new TextMessageBuilder("Halo, " . $profile['displayName']);

                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                        return $response
                            ->withHeader('Content-Type', 'application/json')
                            ->withStatus($result->getHTTPStatus());
                    }
                } 
                else {
                    //message from single user
                    $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                    $response->getBody()->write((string)$result->getJSONDecodedBody());
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                }
            }
        }
        return $response->withStatus(200, 'for Webhook!'); //buat ngasih response 200 ke pas verify webhook
    }
    return $response->withStatus(400, 'No event sent!');
});

$app->get('/pushmessage', function ($req, $response) use ($bot) {
    // send push message to user
    $userId = 'U39fddbb54061b4edab80c28ff055858a';
    $textMessageBuilder = new TextMessageBuilder('Maaf, hari ini toko sedang tutup, terima kasih.');
    $result = $bot->pushMessage($userId, $textMessageBuilder);

    $response->getBody()->write("Pesan push berhasil dikirim!");
    return $response
        //->withHeader('Content-Type', 'application/json') //baris ini dapat dihilangkan karena hanya menampilkan pesan di browser
        ->withStatus($result->getHTTPStatus());
});

$app->get('/multicast', function($req, $response) use ($bot)
{
    // list of users
    $userList = [
        'U39fddbb54061b4edab80c28ff055858a',
        'U4eca900f06239bdc9a117c0e37cc0347',
        'U79338fe62b08a48895d6b2dbcf6aba5e'];
 
    // send multicast message to user
    $textMessageBuilder = new TextMessageBuilder('Halo, hari ini sedang ada promo lho. Yuk buruan sebelum kehabisan!');
    $result = $bot->multicast($userList, $textMessageBuilder);
 
 
    $response->getBody()->write("Pesan multicast berhasil dikirim!");
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

$app->get('/profile', function ($req, $response) use ($bot)
{
    // get user profile
    $userId = 'U39fddbb54061b4edab80c28ff055858a';
    $result = $bot->getProfile($userId);
 
    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

$app->run();
 