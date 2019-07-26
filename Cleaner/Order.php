<?php

namespace MageDirect\CleanDb\Cleaner;

use MageDirect\CleanDb\Cleaner\CleanerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Math\Random;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CustomerEmail
 * @package MageDirect\CleanDb\Cleaner
 */
class Order implements CleanerInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $_customerRepository;

    /**
     * @var CustomerCollectionFactory
     */
    private $_customerCollectionFactory;

    /**
     * @var Random
     */
    private $_random;

    /**
     * @var State
     */
    private $_state;

    /**
     * @var OutputInterface
     */
    private $_output = false;

    /**
     * CustomerEmail constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Random $random
     * @param State $state
     */
    public function __construct (
        CustomerRepositoryInterface $customerRepository,
        CustomerCollectionFactory $customerCollectionFactory,
        Random $random,
        State $state
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->_random = $random;
        $this->_state = $state;
    }

    /**
     * @return mixed|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function clean()
    {
        $this->_state->setAreaCode('adminhtml');

        $this->_outputMessage(
            __('Starting changing orders.')
        );

        //todo change from OM
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var CollectionFactory $orderCollectionFactory */
        $orderCollectionFactory = $objectManager->create(CollectionFactory::class);
        $orderCollection = $orderCollectionFactory->create();
        foreach ($orderCollection->getAllIds() as $orderId) {
            $this->_outputMessage($orderId);
        }
    }

    /**
     * @param OutputInterface $output
     * @return $this|mixed
     */
    public function setOutput(OutputInterface $output)
    {
        $this->_output = $output;
        return $this;
    }

    /**
     * @param string $message
     */
    private function _outputMessage(string $message)
    {
        if (false !== $this->_output) {
            $this->_output->writeln($message);
        }
    }
}
