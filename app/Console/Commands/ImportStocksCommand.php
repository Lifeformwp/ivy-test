<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class ImportStocksCommand
 *
 * @package App\Console\Commands
 */
class ImportStocksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:import-stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Persist import from csv file.';

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
        $csv = array_map('str_getcsv', file('./resources/files/primex-stock-test.csv'));

        /** @var \PDO $pdo */
        $pdo = DB::getPdo();
        $iterator = 0;

        $productCodes = [];

        foreach ($csv as $row) {
            if ($iterator === 0) {
                $iterator++;
                continue;
            }

            $productCodes[] = "'" . $row[0] . "'";
        }

        $sqlSelectProducts = 'SELECT id, code FROM products WHERE code IN (' . implode(', ', $productCodes) . ');';
        $mappedProductCodesToIds = [];

        foreach ($pdo->query($sqlSelectProducts) as $row) {
            $mappedProductCodesToIds[$row['code']] = $row['id'];
        }

        $sqlInsertStocks = "INSERT INTO stocks (product_id, on_hand, taken, production_date, created_at) VALUES ";

        foreach ($csv as $row) {
            if ($iterator === 0) {
                $iterator++;
                continue;
            }

            if (!isset($mappedProductCodesToIds[$row[0]])) {
                continue;
            }

            $sqlInsertStocks .= '(';
            $dateTime = \DateTimeImmutable::createFromFormat('d/m/Y', $row[2]);

            $sqlInsertStocks .= implode(',', [
                $mappedProductCodesToIds[$row[0]],
                $row[1],
                0,
                "'" . $dateTime->format('Y-m-d') . "'",
                "'" . (new \DateTimeImmutable('now'))->format('Y-m-d') . "'"
            ]);

            $sqlInsertStocks .= '),';
        }

        $sqlInsertStocks = substr($sqlInsertStocks, 0, -1);
        $sqlInsertStocks .= ';';

        $stmt = $pdo->prepare($sqlInsertStocks);

        try {
            $stmt->execute();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            Log::critical($e->getMessage());
        }

        return 0;
    }
}
