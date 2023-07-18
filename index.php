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

<html>
<head>
    <title>EthCC 2023 Talk Schedule</title>
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

    .event.selected {
        background: #d4abf6;
        border: solid 1px #333;
        padding: 3px;
        box-shadow: 1px 1px 1px #ccc;
    }
    .event.selected .time{
        color: #ffecfc;
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
        text-shadow: 2px 2px 2px #eee;
    }

    body {
        background: #eee;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
    }

    .container {
        margin: 0px auto;
        max-width: 1500px;
        position: relative;
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

    #timebar {
        height: 4px;
        background: red;
        position: absolute;
        top: 942px;
        width: 100%;
        z-index: -1;
    }
</style>
</head>
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



                    <div id="e-<?=sha1($event->speakers."|".$event->Title)?>" class="event"
                        style="min-height:<?=((int)$event->Time) * $px_per_minute?>px; top: <?=$event->start_minutes * $px_per_minute?>px;">
                        <div class="loc"><?=$event->stage_code?></div>
                        <div class="time">
                            <?=$event->start_hour > 12 ? $event->start_hour%12 : $event->start_hour?>:<?=($event->start_minute > 9) ? $event->start_minute:('0'.$event->start_minute)?><?=$event->start_hour > 11 ? 'pm' : 'am'?>
                        </div>
                        <div class="title"><?=htmlspecialchars($event->Title)?></div>
                        <?=htmlspecialchars($event->speakers)?>
                    </div>
                    <?php } ?>
                </td>
            </tr>
        </table>
        <div id="timebar">.</div>
    </div>
</body>
<script>
let selections = JSON.parse(window.localStorage.getItem('actives') || '{}')
// Click events
document.body.ontouch = document.body.onclick = function(evt){
  let event;
  if(evt.target == null || !evt.target.getAttribute('class')){
    return
  }
  if(evt.target.getAttribute('class').includes("event")){
    event = evt.target
  } else if(evt.target.parentNode.getAttribute('class').includes("event")) {
    event = evt.target.parentNode
  } else {
    return
  }

  const oldClass = event.getAttribute('class')
  const key = event.getAttribute('id')
  if(oldClass.includes("selected")){
    event.setAttribute('class', oldClass.replace(' selected',''))
    selections[key] = false
    window.localStorage.setItem('actives', JSON.stringify(selections))
  }else{
    event.setAttribute('class', oldClass + ' selected')
    selections[key] = true
    window.localStorage.setItem('actives', JSON.stringify(selections))
  }

}
// Show events
for(const event of document.querySelectorAll(".event")){
  if(selections[event.getAttribute('id')]){
    event.setAttribute('class', event.getAttribute('class') + ' selected')
  }
}
// Timebar
let faketime = 100
const getDateStuff = function(time){
  const firstEvents = document.querySelectorAll("tr td:first-child .event:nth-child(2)")
  const confTime = time - Math.floor((new Date(Date.UTC(2023,6,17,8,0,0))).getTime() / 1000)
  const confDay = Math.floor(confTime / (24 * 60 * 60))
  const daySeconds = confTime % (24 * 60 * 60)
  if(daySeconds > 8*60*60 || confDay >= 4 || confDay < 0){
    return null;
  }
  const dayEvent = firstEvents[confDay]
  window.dayEvent = dayEvent
  const dayOffset = dayEvent.offsetParent.offsetParent.offsetTop + dayEvent.offsetParent.offsetTop + 33
  console.log(
    dayEvent.offsetParent.offsetParent.offsetTop,
    dayEvent.offsetParent.offsetTop,
    dayEvent.offsetTop
  )
  console.log(dayEvent.offsetTop)
  return {
    daySeconds: daySeconds,
    dayOffset: dayOffset
  }
}
const timebarUpdate = function(){
  const timebar = document.getElementById('timebar')
  let now = Math.floor((new Date()).getTime() / 1000)
  let stuff = getDateStuff(now+0)
  if(stuff==null){
    timebar.style.top = "-9999px"
  }
  timebar.style.top = (stuff.dayOffset + stuff.daySeconds * <?=$px_per_minute?> / 60)+'px';
}
timebarUpdate()
setInterval(timebarUpdate, 1000)
</script>
