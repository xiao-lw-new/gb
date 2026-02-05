<?php

namespace App\Console\Commands\Test;

use Elliptic\EC;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use kornrunner\Keccak;
use Endroid\QrCode\Writer\ConsoleWriter;

class TestAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:address';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ec = new EC('secp256k1');
        $keyPair = $ec->genKeyPair();
        $privateKey = $keyPair->getPrivate()->toString(16);

        // 计算公钥和地址
        $publicKey = $keyPair->getPublic()->encode('hex');
        $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

        $a =  [
            'private_key' => $privateKey,
            'address' => $address,
        ];

        var_dump($a);

        $qrcode = new QrCode($privateKey);

        $writer = new ConsoleWriter();
        $output = $writer->write($qrcode);
        Log::channel('test_address')->info($output->getString());
        Log::channel('test_address')->info(json_encode($a));
        $this->info($output->getString());
        
        return 0;
    }
}
