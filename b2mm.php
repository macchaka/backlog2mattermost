<?php
$mattermostUrl = "https://REPLACE_TO_YOUR_WEBHOOKURL";
$backlogUrl    = "https://example.backlog.jp/view/";

// 受信処理
$json_string = file_get_contents('php://input');
$backlog = json_decode($json_string);

// 送信処理
$mattermost = array();
$issue_id   = $backlog->project->projectKey . '-' . $backlog->content->key_id;
$tag_id     = $backlog->project->projectKey . '_' . $backlog->content->key_id;
$title      = $backlog->content->summary;
$comment_id = $backlog->content->comment->id;

//メッセージ
switch ($backlog->type) {
    //課題の追加
    case 1:
        $mattermost['text'] = "新しい課題を追加しました。 #{$tag_id}";
        $mattermost['attachments'][] = array(
            'title'      => $title,
            'title_link' => "{$backlogUrl}{$issue_id}",
            'text'       => $backlog->content->description
        );
        break;

    //コメント
    case 3:
        $mattermost['text'] = "新しいコメントを登録しました。 #{$tag_id}";
        $mattermost['attachments'][] = array(
            'title'      => $title,
            'title_link' => "{$backlogUrl}{$issue_id}#comment-{$comment_id}",
            'text'       => $backlog->content->comment->content
        );
        break;

    //未定義
    default:
        die('undefined');
        break;
}

//Mattermostに投稿するBOTユーザ名
$mattermost['username'] = "{$backlog->createdUser->name}@Backlog";
$mattermost['icon_url'] = "https://assets.backlog.jp/R20170801/images/_newUI/icon/project_icons/01@2x.png";

$options = array(
  'http' => array(
    'method'  => 'POST',
    'content' => json_encode($mattermost),
    'header'  =>  "Content-Type: application/json\r\n" .
                  "Accept: application/json\r\n"
    )
);
 
$context  = stream_context_create($options);
$result   = json_decode(file_get_contents($mattermostUrl, false, $context));
 
var_dump($result);
