<?php

namespace MageDirect\CleanDb\Cleaner;

use MageDirect\CleanDb\Cleaner\CleanerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Math\Random;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CustomerEmail
 * @package MageDirect\CleanDb\Cleaner
 */
class CustomerEmail implements CleanerInterface
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

        /** @var CustomerCollection $customerCollection */
        $customerCollection = $this->_customerCollectionFactory->create();
        $customerCollectionIds = $customerCollection->getAllIds();
        $customerCollectionIdsCount = count($customerCollectionIds);

        $this->_outputMessage(
            __('Starting changing customer emails.')
        );

        $counter = 1;
        $failedCustomerIds = [];
        foreach ($customerCollectionIds as $customerId) {
            $customer = $this->_customerRepository->getById($customerId);

            try {
                $this->_updateCustomerEmail($customer);
                $this->_outputMessage(
                    $counter . '/' . $customerCollectionIdsCount
                );
            } catch (\Exception $e) {
                $failedCustomerIds[] = $customerId;
            }
            $counter++;
        }

        $successfullCustomersEmailsChanged = $customerCollectionIdsCount - count($failedCustomerIds);
        $this->_outputMessage(
            __('Changed ' . $successfullCustomersEmailsChanged . ' customer emails.')
        );
        if (!empty($failedCustomerIds)) {
            $this->_outputMessage(
                __('Was not able to change emails for these customer ids:')
            );
            $this->_outputMessage(
                implode(',', $failedCustomerIds)
            );
        }
    }

    /**
     * @param $customer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    private function _updateCustomerEmail($customer)
    {
        $customer->setEmail(
            $this->_generateNewEmail($customer->getEmail())
        );

        $this->_customerRepository->save($customer);
    }

    /**
     * @param $oldEmail
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _generateNewEmail($oldEmail)
    {
        return $this->_random->getRandomString(2) . $oldEmail;
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
