<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tag Separator
    |--------------------------------------------------------------------------
    |
    | This value is used to explode and implode tags
    |
    */

    'tag_separator' => ';',

    /*
    |--------------------------------------------------------------------------
    | Image Option
    |--------------------------------------------------------------------------
    |
    | Define allowed maximum size and mime of uploaded image
    | This will occur on cover, content, listice, etc
    |
    */

    'img_opt' => [
        'max_size' => '1200', // kb
        'mime'     => [
            // J(E)PG
            'image/jpg',
            'image/jpeg',
            'image/pjpeg',

            // PNG
            'image/png',

            // BMP
            'image/bmp',

            // GIF
            'image/gif'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Maximum Size
    |--------------------------------------------------------------------------
    |
    | Define maximum size both height and width of resized cover in Pixel
    |
    */

    'thumbnails_size' => [
        [200,300],
        [600,800],
        [400,500],
    ],

    'covers_size' => [
      'size1' => '300x200',
      'size2' => '166x120',
      'size3'=> '480x274',
      'size4'=> '728x381',
      'size5'=> '480x251',
      'size6'=> '140x100',
      'size7'=>'336x176',
      'size8'=>'176x136',
      'size9'=>'236x124',
    ],

    'botman_factory' => [
      'hipchat_urls' => [
            'YOUR-INTEGRATION-URL-1',
            'YOUR-INTEGRATION-URL-2',
        ],
        'nexmo_key' => 'YOUR-NEXMO-APP-KEY',
        'nexmo_secret' => 'YOUR-NEXMO-APP-SECRET',
        'microsoft_bot_handle' => 'YOUR-MICROSOFT-BOT-HANDLE',
        'microsoft_app_id' => 'YOUR-MICROSOFT-APP-ID',
        'microsoft_app_key' => 'YOUR-MICROSOFT-APP-KEY',
        'slack_token' => 'YOUR-SLACK-TOKEN-HERE',
        'telegram_token' => 'YOUR-TELEGRAM-TOKEN-HERE',
        'facebook_token' => env('FB_TOKEN_CHAT'),
        'facebook_app_secret' => env('FB_APP_SECRET_KEY'),
        'wechat_app_id' => 'YOUR-WECHAT-APP-ID',
        'wechat_app_key' => 'YOUR-WECHAT-APP-KEY',
    ],

    /*
    |--------------------------------------------------------------------------
    | Meme Font Location
    |--------------------------------------------------------------------------
    |
    | Location of font used for Meme Feed/Issue
    | Font must be in .ttf
    |
    */
    'meme_font' => 'css/worksans.ttf',


    /*
    |--------------------------------------------------------------------------
    | Default Object File ID
    |--------------------------------------------------------------------------
    |
    | Default Object File ID if it was null
    |
    */
    'default_object_file_id' => 1,


    /*
    |--------------------------------------------------------------------------
    | Cache the Detail?
    |--------------------------------------------------------------------------
    |
    | If set true, every detail's API response will cached
    |
    */
    'cache_detail' => env('CACHE_DETAIL', false)

];
