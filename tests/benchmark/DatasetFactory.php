<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark;

use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Indexer\IndexerInterface;

class DatasetFactory
{
    /**
     * @var string
     */
    private $dataDir;

    /**
     * @param string $dataDir
     */
    public function __construct($dataDir)
    {
        $this->dataDir = $dataDir;
    }

    /**
     * @param int $size
     */
    public function getFacetedIndex(int $size): Index\ArrayIndex
    {
        $dataFile = $this->dataDir . $size . '/data.json';
        if (!file_exists($dataFile)) {
            $this->createDataset($size, $dataFile);
        }
        $index = new Index\ArrayIndex();
         $this->loadData($index, $dataFile);
         return $index;
    }

    /**
     * @param int $size
     */
    public function getFixedFacetedIndex(int $size): Index\FixedArrayIndex
    {
        $dataFile = $this->dataDir . $size . '/data.json';
        if (!file_exists($dataFile)) {
            $this->createDataset($size, $dataFile);
        }
        $index = new Index\FixedArrayIndex();
        $index->writeMode();
        $this->loadData($index, $dataFile);
        $index->commitChanges();
        return $index;
    }


    /**
     * @param int $size
     * @param string $file
     */
    private function createDataset(int $size, string $file): void
    {
        $colors = ['red', 'green', 'blue', 'yellow', 'black', 'white'];
        $brands = [
            'Nike',
            'H&M',
            'Zara',
            'Adidas',
            'Louis Vuitton',
            'Cartier',
            'Hermes',
            'Gucci',
            'Uniqlo',
            'Rolex',
            'Coach',
            'Victoria\'s Secret',
            'Chow Tai Fook',
            'Tiffany & Co.',
            'Burberry',
            'Christian Dior',
            'Polo Ralph Lauren',
            'Prada',
            'Under Armour',
            'Armani',
            'Puma',
            'Ray-Ban'
        ];

        $warehouses = [1, 10, 23, 345, 43, 5476, 34, 675, 34, 24, 789, 45, 65, 34, 54, 511, 512, 520];
        $type = ['normal', 'middle', 'good'];

        if(!is_dir(dirname($file))){
            if(!mkdir(dirname($file),0777,true)){
                throw new \RuntimeException('Cannot create dataset directory ' . dirname($file));
            }
        }

        $f = fopen($file, 'w');
        if (!$f) {
            throw new \RuntimeException('Cannot write dataset file ' . $file);
        }

        for ($i = 1; $i <= $size; $i++) {
            $countWh = rand(0, count($warehouses));
            $wh = [];
            for ($k = 0; $k < $countWh; $k++) {
                $wh[] = $warehouses[rand(0, count($warehouses) - 1)];
            }

            $record = [
                'id' => $i,
                'color' => $colors[rand(0, 5)],
                'back_color' => $colors[rand(0, 5)],
                'size' => rand(34, 50),
                'brand' => $brands[rand(0, count($brands) - 1)],
                'price' => rand(1000, 10000),
                'discount' => rand(0, 10),
                'combined' => rand(0, 1),
                'quantity' => rand(0, 100),
                'warehouse' => array_unique($wh),
                'type' => $type[rand(0, count($type) - 1)]
            ];

            fwrite($f, json_encode($record) . PHP_EOL);
        }
        fclose($f);
    }

    private function loadData(Index\IndexInterface $index, string $file) : void
    {
        $f = fopen($file, 'r');
        if (empty($f)) {
            throw new \RuntimeException('Cannot open file ' . $file);
        }
        while ($line = fgets($f)) {
            if (empty($line)) {
                continue;
            }
            $row = \json_decode($line, true);
            $id = $row['id'];
            unset($row['id']);
            $index->addRecord((int)$id, $row);
        }
    }
}