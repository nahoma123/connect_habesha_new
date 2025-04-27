<?php

namespace Tests;

use Mockery;

class CategoryTest extends TestCase
{
    protected $categoryModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryModel = Mockery::mock();
    }

    public function testCategoryCreation()
    {
        $categoryData = [
            's_name' => 'Test Category',
            's_slug' => 'test-category',
            'i_expiration_days' => 30
        ];
        
        $this->categoryModel->shouldReceive('insert')
            ->with($categoryData)
            ->andReturn(true);
        
        // Test category creation
        $result = $this->categoryModel->insert($categoryData);
        $this->assertTrue($result);
    }

    public function testCategoryUpdate()
    {
        $category = ['pk_i_id' => 1, 's_name' => 'Test Category'];
        
        $this->categoryModel->shouldReceive('findByPrimaryKey')
            ->with(1)
            ->andReturn($category);
        $this->categoryModel->shouldReceive('update')
            ->with(['s_name' => 'Updated Category'], ['pk_i_id' => 1])
            ->andReturn(true);
        
        // Test category update
        $result = $this->categoryModel->update(['s_name' => 'Updated Category'], ['pk_i_id' => 1]);
        $this->assertTrue($result);
    }

    public function testCategoryDeletion()
    {
        $category = ['pk_i_id' => 1, 's_name' => 'Test Category'];
        
        $this->categoryModel->shouldReceive('findByPrimaryKey')
            ->with(1)
            ->andReturn($category);
        $this->categoryModel->shouldReceive('delete')
            ->with(['pk_i_id' => 1])
            ->andReturn(true);
        
        // Test category deletion
        $result = $this->categoryModel->delete(['pk_i_id' => 1]);
        $this->assertTrue($result);
    }

    public function testCategoryList()
    {
        $categories = [
            ['pk_i_id' => 1, 's_name' => 'Category 1'],
            ['pk_i_id' => 2, 's_name' => 'Category 2']
        ];
        
        $this->categoryModel->shouldReceive('listAll')
            ->andReturn($categories);
        
        // Test category listing
        $results = $this->categoryModel->listAll();
        $this->assertCount(2, $results);
        $this->assertEquals('Category 1', $results[0]['s_name']);
    }
} 