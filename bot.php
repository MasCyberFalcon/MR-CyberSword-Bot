<?php
/*
========================================
 MR CyberSword Telegram Bot (Polling)
 Author  : FALCIN
 Channel : https://t.me/+j8x9Tp4CGa80ZmM1
 Coded with ➲ by FALCIN
========================================
*/

// ===== CONFIG =====
$BOT_TOKEN      = "8400926070:AAF29yR-1EMM_o5uoWU7MQ3N-vxBr_hL3tc";     
$FORCE_GROUP_ID = "-1002831054894";              
$GROUP_LINK     = "https://t.me/yggginbvfj";     

$API_URL = "http://numbertoinfo.iceiy.com/blinfo.php?key=@sunny7695&msisdn=";

// ===== POLLING =====
$offset = 0;

while (true) {
    $updates = json_decode(@file_get_contents("https://api.telegram.org/bot$BOT_TOKEN/getUpdates?offset=$offset&timeout=30"), true);

    if (!empty($updates["result"])) {
        foreach ($updates["result"] as $update) {
            $offset = $update["update_id"] + 1;

            $chat_id = $update["message"]["chat"]["id"] ?? null;
            $user_id = $update["message"]["from"]["id"] ?? null;
            $text    = trim($update["message"]["text"] ?? "");

            if (!$chat_id || !$user_id) continue;

            // ===== FORCE JOIN CHECK =====
            if (!isJoined($user_id)) {
                sendJoinMessage($chat_id);
                continue;
            }

            // ===== START =====
            if ($text === "/start") {
                sendMessage($chat_id, "🦅🥷🦅স্বাগতম No one should use Banglalink for bad purposes just for education.!\n\n📱 একটি বাংলালিংক মোবাইল নাম্বার পাঠান\nXXXXXXX");
                continue;
            }

            // ===== NUMBER INPUT =====
            if (preg_match('/^01[0-9]{9}$/', $text)) {
                $response = @file_get_contents($API_URL . urlencode($text));
                if ($response === false) {
                    sendMessage($chat_id, "🥵 Network error কাজ করছে না", পরে চেষ্টা করুন");
                    continue;
                }

                $data = json_decode($response, true);
                $msg = "🦅😈🕵️😈🦅তথ্য পাওয়া গেছে\n\n📱 নাম্বার: $text\n\n";

                if (is_array($data)) {
                    foreach ($data as $k => $v) {
                        $msg .= "🔹 $k : $v\n";
                    }
                } else {
                    $msg .= $response;
                }

                sendMessage($chat_id, $msg);
                continue;
            }

            // ===== INVALID INPUT =====
            if ($text) {
                sendMessage($chat_id, "🥵🕵️🥵 সঠিক ১১ ডিজিটের নাম্বার দিন\nউদাহরণ: 019XXXXXXXX");
            }
        }
    }

    sleep(1); // CPU load কমানোর জন্য wait
}

// ===== FUNCTIONS =====
function isJoined($user_id) {
    global $BOT_TOKEN, $FORCE_GROUP_ID;
    $url = "https://api.telegram.org/bot$BOT_TOKEN/getChatMember?chat_id=$FORCE_GROUP_ID&user_id=$user_id";
    $res = json_decode(@file_get_contents($url), true);

    return isset($res["result"]["status"]) &&
        in_array($res["result"]["status"], ["member","administrator","creator"]);
}

function sendJoinMessage($chat_id) {
    global $GROUP_LINK;
    sendMessage(
        $chat_id,
        "🚫 🧑‍🎤বট ব্যবহার করতে হলে আগে গ্রুপে Join করুন",
        [
            "inline_keyboard" => [
                [
                    ["text" => "🔔 Join Group", "url" => $GROUP_LINK]
                ]
            ]
        ]
    );
}

function sendMessage($chat_id, $text, $keyboard = null) {
    global $BOT_TOKEN;
    $data = [
        "chat_id" => $chat_id,
        "text"    => $text
    ];
    if ($keyboard) {
        $data["reply_markup"] = json_encode($keyboard);
    }
    @file_get_contents("https://api.telegram.org/bot$BOT_TOKEN/sendMessage?" . http_build_query($data));
}
