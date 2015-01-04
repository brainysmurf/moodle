Christmas
=========

Add this to <head> in general.php
====
```php
    <link rel="stylesheet" href="/theme/decaf/style/christmas.css?v=2"></link>
    <?php if (empty($_COOKIE['nosnow'])) { ?>
    <!-- Snow -->
    <link rel="stylesheet" href="/jquery-snowfall/styles.css"></link>
    <script src="/jquery-snowfall/snowfall.min.jquery.js"></script>
    <script>
        $(function(){
            $('#page-header').css('overflow','hidden');
            $('#page-header').snowfall({flakeCount : 50, maxSpeed :1, maxSize : 4, round:true});
        });
    </script>
    <!-- end snow -->
    <? } ?>
```

Change heading to
===
```php
<?php echo str_replace('DragonNet','<img src="/theme/decaf/pix/dragonnet-hat.png" alt="DragonNet" style="height:40px; vertical-align:middle; margin-top:-5px;" />', $PAGE->heading); ?>
```

Change header image
===
```php
$headerBg = '/theme/decaf/pix/redchristmasheader.jpg';
```
