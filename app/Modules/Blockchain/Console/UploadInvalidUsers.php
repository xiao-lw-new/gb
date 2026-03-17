<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Services\InvalidUserUploadService;
use Illuminate\Console\Command;

class UploadInvalidUsers extends Command
{
    protected $signature = 'blockchain:upload-invalid-users
        {--limit= : 只上传前 N 个地址（不指定则上传所有）}';

    protected $description = 'Upload unqualified user addresses to MarketDAO.updateUserOldNewInvalid';

    public function handle(InvalidUserUploadService $service): int
    {
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $service->upload($limit);

        return self::SUCCESS;
    }
}
