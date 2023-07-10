<?php

$px_per_minute = 7;

$room_order = array(
    'Main Stage'=>1,
    'Saint Victor'=>2,


    'Eiffel Stage'=>10,
    'Louvre Stage'=>11,
    'Notre Dame Stage'=>12,
    'Versailles Stage'=>13,
    'Bastille Room'=>14,


    'EthVC Room'=>999

);

$events = json_decode(file_get_contents('eth_cc_tracks.json'));
$events = $events;
foreach($events as $event){
    $event->confday = (int)(substr($event->Date,4,1));
    preg_match('/([0-9]{2}):([0-9]{2})/', $event->{"Starting Hour"}, $match);
    $event->start_minutes = ((int)$match[1])*60 + ((int)$match[2]) - 10 * 60 + 6;
    $event->start_hour = (int)$match[1];
    $event->start_minute = (int)$match[2];
    $event->stage_code = preg_replace('/[^A-Z]/','',$event->Room);
}

usort($events, function($a, $b) {return strcmp($a->{"Starting Hour"}, $b->{"Starting Hour"});});
usort($events, function($a, $b) use (&$room_order) {return $room_order[$a->Room] - $room_order[$b->Room];});
usort($events, function($a, $b) {return $a->confday - $b->confday;});

//usort($events, function($a, $b) {return strcmp($a->name, $b->name);});



?>


<style>
    td {
        vertical-align: top;
        font-size: 14px;
        padding-right: 4px;
        padding-left: 4px;
    }

    .title {
        font-weight: bold
    }

    .event {
        background: #fff;
        border-radius: 4px;
        margin-bottom: 2px;
        padding: 4px;
        position: absolute;
        width: 90%;
    }

    .time {
        color: #999;
        text-align: right;
        font-size: 0.9em;
    }

    .loc {
        margin-top: -21px;
        margin-bottom: 6px;
        text-align: right;
        font-size: 0.9em;
        color: #ccc;
    }

    body {
        background: #eee;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
    }

    .container {
        margin: 0px auto;
        max-width: 1500px;
    }

    a {
        color: #000;
    }

    h1.title {
        text-align: center;
    }

    .author {
        text-align: center;
        font-size: 14px;
        margin-top: -20px;
    }
</style>

<body>
    <div class="container">
        <h1 class="title">EthCC Talk Schedule</h1>

        <p class="author">By <a href="https://twitter.com/danielvf">DanielVF</a>
            of <a href="https://www.oeth.com">Origin Protocol</a>
        </p>

        <table style="min-width: 1100px;">
            <?php $last_day = "" ?>
            <?php $last_room = "" ?>
            <?php foreach($events as $event){?>
            <?php if($room_order[$event->Room]>50){ continue; }?>
            <?php if($event->confday != $last_day) {
        $last_day = $event->confday;
    ?>
            <tr>
                <td colspan="4">
                    <h1><?=htmlspecialchars($event->Date)?></h1>
                </td>
            </tr>
            <tr>
                <?php }?>


                <?php if($event->Room != $last_room) {
                    $last_room = $event->Room;
                ?>
                <td style="width:200px; position:relative; height: <?=8 * 60 * $px_per_minute?>px;">
                    <h4><?=htmlspecialchars($event->Room)?></h4>
                    <?php }?>



                    <div class="event"
                        style="min-height:<?=((int)$event->Time) * $px_per_minute?>px; top: <?=$event->start_minutes * $px_per_minute?>px;">
                        <div class="loc"><?=$event->stage_code?></div>
                        <div class="time">
                            <?=$event->start_hour > 12 ? $event->start_hour%12 : $event->start_hour?>:<?=($event->start_minute > 9) ? $event->start_minute:('0'.$event->start_minute)?><?=$event->start_hour > 11 ? 'pm' : 'am'?>
                        </div>
                        <span class="title"><?=htmlspecialchars($event->Title)?></span><br>
                        <?=htmlspecialchars($event->speakers)?>
                    </div>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
</body>
