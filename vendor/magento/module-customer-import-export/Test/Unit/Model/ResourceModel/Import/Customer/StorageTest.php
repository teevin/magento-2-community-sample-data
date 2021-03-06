<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\Unit\Model\ResourceModel\Import\Customer;

use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Storage
     */
    private $_model;

    /**
     * @var CollectionByPagesIterator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iteratorMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    protected function setUp()
    {
        $this->iteratorMock = $this->getMockBuilder(
            CollectionByPagesIterator::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|CollectionByPagesIteratorFactory $iteratorFactoryMock */
        $iteratorFactoryMock = $this->getMockBuilder(
            CollectionByPagesIteratorFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $iteratorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->iteratorMock);
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject $collectionFactoryMock */
        $collectionFactoryMock = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->collectionMock);
        /** @var \PHPUnit_Framework_MockObject_MockObject $selectMock */
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->any())
            ->method('getPart')
            ->with(Select::FROM)
            ->willReturn(['e' => []]);
        $this->collectionMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($selectMock);

        $this->_model = new Storage(
            $collectionFactoryMock,
            $iteratorFactoryMock,
            []
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testGetCustomerId()
    {
        $existingEmail = 'test@magento.com';
        $existingWebsiteId = 0;
        $existingId = 1;
        $nonExistingEmail = 'test1@magento.com';
        $nonExistingWebsiteId = 2;

        $this->iteratorMock->expects($this->at(0))
            ->method('iterate')
            ->willReturnCallback(
                function (...$args) use (
                    $existingId,
                    $existingEmail,
                    $existingWebsiteId
                ) {
                    /** @var callable $callable */
                    foreach ($args[2] as $callable) {
                        $callable(
                            new DataObject([
                                'id' => $existingId,
                                'email' => $existingEmail,
                                'website_id' => $existingWebsiteId,
                            ])
                        );
                    }
                }
            );

        $this->assertEquals(
            $existingId,
            $this->_model->getCustomerId($existingEmail, $existingWebsiteId)
        );
        $this->assertFalse(
            $this->_model->getCustomerId(
                $nonExistingEmail,
                $nonExistingWebsiteId
            )
        );
    }
}
