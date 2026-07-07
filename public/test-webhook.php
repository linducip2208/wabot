<?php
require __DIR__.'/../vendor/autoload.php';
\ = require_once __DIR__.'/../bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Http\Kernel::class);
\ = Illuminate\Http\Request::create('/webhook/whatsapp', 'POST', [], [], [], [], json_encode(['session_id'=>'test','phone'=>'123','message'=>'test']));
\->headers->set('Content-Type', 'application/json');
\ = \->handle(\);
echo 'Status: '.\->getStatusCode().PHP_EOL;
echo 'Body: '.mb_substr(\->getContent(),0,200).PHP_EOL;
