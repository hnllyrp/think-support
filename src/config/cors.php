<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Thinkphp CORS Options
    |--------------------------------------------------------------------------
    |
    | The allowed_methods and allowed_headers options are case-insensitive.
    |
    | You don't need to provide both allowed_origins and allowed_origins_patterns.
    | If one of the strings passed matches, it is considered a valid origin.
    |
    | If ['*'] is provided to allowed_methods, allowed_origins or allowed_headers
    | all methods / origins / headers are allowed.
    |
    */

    /*
     * You can enable CORS for 1 or multiple paths.
     * Example: ['api/*']
     */
    'paths' => [

    ],

    /**
     * Matches the request method. `['*']` allows all methods.
     * GET, POST, PATCH, PUT, OPTIONS, DELETE
     */
    'allowed_methods' => [
        // '*',
        'GET', 'POST', 'OPTIONS'
    ],

    /*
     * Matches the request origin. `['*']` allows all origins. Wildcards can be used, eg `*.mydomain.com`
     */
    'allowed_origins' => [
        // '*',
        'http://localhost',
    ],

    /*
     * Patterns that can be used with `preg_match` to match the origin.
     * exp: localhost:80, localhost:8080
     */
    'allowed_origins_patterns' => ['/localhost:\d/'],

    /*
     * Sets the Access-Control-Allow-Headers response header. `['*']` allows all headers.
     * exp: Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN, token
     * Accept-Platform,DNT,X-Mx-ReqToken,token,Authorization,Keep-Alive,User-Agent,Fetch-Mode,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding,accept
     */
    'allowed_headers' => [
        '*',
    ],

    /*
     * Sets the Access-Control-Expose-Headers response header with these headers.
     */
    'exposed_headers' => [
        'Authorization', 'authenticated'
    ],

    /*
     * Sets the Access-Control-Max-Age response header when > 0.
     * 预检请求 缓存时间，0 禁止缓存  单位:秒 此处 7200 取 Chromium 上限 2小时
     * https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Headers/Access-Control-Max-Age
     */
    'max_age' => 7200,

    /*
     * Sets the Access-Control-Allow-Credentials header.
     */
    'supports_credentials' => true,

];
