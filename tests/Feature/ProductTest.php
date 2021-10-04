<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * A basic healthcheck.
     *
     * @return void
     */
    public function test_healthcheck()
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
    }

    /**
     * Test product add.
     *
     * @return void
     */
    public function test_product_add()
    {
        $response = $this->post('/products');

        $response->assertStatus(200);
    }

    /**
     * Test product update.
     *
     * @return void
     */
    public function test_product_update()
    {
        $response = $this->post('/products/1');

        $response->assertStatus(200);
    }

    /**
     * Test product list.
     *
     * @return void
     */
    public function test_product_list()
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
    }

    /**
     * Test product addStock.
     *
     * @return void
     */
    public function test_product_add_stock()
    {
        $response = $this->get('/products/1/add-stock');

        $response->assertStatus(200);
    }

    /**
     * Test product show.
     *
     * @return void
     */
    public function test_product_show()
    {
        $response = $this->get('/products/1');

        $response->assertStatus(200);
    }

    /**
     * Test product delete.
     *
     * @return void
     */
    public function test_product_delete()
    {
        $response = $this->delete('/products/1');

        $response->assertStatus(204);
    }
}
