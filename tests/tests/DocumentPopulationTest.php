<?php

class DocumentPopulationTest extends PHPUnit_Framework_TestCase {

    private static $collection;

    public static function setUpBeforeClass() {
        $database = new \MongoLite\Database();
        
        $database->createCollection("categories");
        
        // Categories
        $collection = $database->selectCollection("categories");
        $category1 = ["name" => "Laptops"];
        $category2 = ["name" => "Monitors"];
        $collection->insert($category1);
        $collection->insert($category2);
        
        // Manufacturers
        $collection = $database->selectCollection("manufacturers");
        $manufacturer1 = ["name" => "Apple", "country" => "United States"];
        $manufacturer2 = ["name" => "Philips", "country" => "Belgium" ];
        $collection->insert($manufacturer1);
        $collection->insert($manufacturer2);
        
        // Products
        $database->createCollection("prodcuts");
        $collection = $database->selectCollection("products");
        $entry1 = ["name" => "Super cool Product", "price" => 20, "in_stock" => true, "categories" => array($category1["_id"]), "manufacturer" => $manufacturer1["_id"]];
        $entry2 = ["name" => "Another cool Product", "price" => 15, "in_stock" => false, "categories" => array($category1["_id"], $category2["_id"])];
        $entry3 = ["name" => "Awesome Product", "price" => 50, "in_stock" => false, "manufacturer" => $manufacturer2["_id"]];
        $collection->insert($entry1);
        $collection->insert($entry2);
        $collection->insert($entry3);

        self::$collection = $collection;
    }

    public function testOneToManyPopulation() {
        $result = self::$collection->find()->populate("categories", "categories")->toArray();
        
        $this->assertEquals("Laptops", $result[0]["categories"][0]["name"]);
        $this->assertEquals("Laptops", $result[1]["categories"][0]["name"]);
        $this->assertEquals("Monitors", $result[1]["categories"][1]["name"]);
        
        $this->assertArrayNotHasKey("categories", $result[2]);
    }
    
    public function testOneToOnePopulation() {
        $result = self::$collection->find()->populate('manufacturer', 'manufacturers')->toArray();
        
        $this->assertEquals("Apple", $result[0]["manufacturer"]["name"]);
        
        $this->assertArrayNotHasKey("manufacturer", $result[1]);
        
        $this->assertEquals("Philips", $result[2]["manufacturer"]["name"]);
    }
    
    public function testMultiplePopulations() {
        $result = self::$collection
                    ->find(["price" => 20])
                    ->populate('categories', 'categories')
                    ->populate('manufacturer', 'manufacturers')
                    ->toArray();
        
        $this->assertEquals("Laptops", $result[0]["categories"][0]["name"]);
        $this->assertEquals("Apple", $result[0]["manufacturer"]["name"]);
    }

}