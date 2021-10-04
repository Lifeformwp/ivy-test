<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class ImportProductsCommand
 *
 * @package App\Console\Commands
 */
class ImportProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:import-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from csv file.';

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
        $csv = array_map('str_getcsv', file('./resources/files/primex-products-test.csv'));

        /** @var \PDO $pdo */
        $pdo = DB::getPdo();
        $sql = "INSERT INTO products (code, name, description, created_at) VALUES ";
        $iterator = 0;

        foreach ($csv as $row) {
            if ($iterator === 0) {
                $iterator++;
                continue;
            }

            $sql .= '(';

            $sql .= implode(',', [
                $row[0],
                $pdo->quote($row[1]),
                $pdo->quote($row[2]),
                "'" . (new \DateTimeImmutable('now'))->format('Y-m-d') . "'"
            ]);

            $sql .= '),';
        }

        $sql = substr($sql, 0, -1);
        $sql .= ' ON CONFLICT DO NOTHING;';

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute();
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }

        return 0;
    }
}
