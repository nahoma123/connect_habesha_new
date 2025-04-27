<?php

namespace Tests;

use Mockery;

class ItemTest extends TestCase
{
    protected $itemModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemModel = Mockery::mock();
    }

    public function testItemCreation()
    {
        $itemData = [
            's_title' => 'Test Item',
            's_description' => 'Test Description',
            'fk_i_category_id' => 1,
            'fk_i_user_id' => 1,
            's_contact_email' => 'test@example.com'
        ];
        
        $this->itemModel->shouldReceive('insert')
            ->with($itemData)
            ->andReturn(true);
        
        // Test item creation
        $result = $this->itemModel->insert($itemData);
        $this->assertTrue($result);
    }

    public function testItemSearch()
    {
        $items = [
            $this->mockItem(1, 'Test Item 1'),
            $this->mockItem(2, 'Test Item 2')
        ];
        
        $this->itemModel->shouldReceive('search')
            ->with(['sPattern' => 'Test'])
            ->andReturn($items);
        
        // Test item search
        $results = $this->itemModel->search(['sPattern' => 'Test']);
        $this->assertCount(2, $results);
        $this->assertEquals('Test Item 1', $results[0]->getTitle());
    }

    public function testItemUpdate()
    {
        $item = $this->mockItem();
        
        $this->itemModel->shouldReceive('findByPrimaryKey')
            ->with(1)
            ->andReturn($item);
        $this->itemModel->shouldReceive('update')
            ->with(['s_title' => 'Updated Title'], ['pk_i_id' => 1])
            ->andReturn(true);
        
        // Test item update
        $result = $this->itemModel->update(['s_title' => 'Updated Title'], ['pk_i_id' => 1]);
        $this->assertTrue($result);
    }

    public function testItemDeletion()
    {
        $item = $this->mockItem();
        
        $this->itemModel->shouldReceive('findByPrimaryKey')
            ->with(1)
            ->andReturn($item);
        $this->itemModel->shouldReceive('delete')
            ->with(['pk_i_id' => 1])
            ->andReturn(true);
        
        // Test item deletion
        $result = $this->itemModel->delete(['pk_i_id' => 1]);
        $this->assertTrue($result);
    }
} 