<?php
namespace MageDirect\CleanDb\Console\Command;

use MageDirect\CleanDb\Cleaner\CleanerInterface;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class SomeCommand
 */
class Clean extends Command
{
    const DATA_TYPE = 'data_type';

    /**
     * @var InputInterface
     */
    private $_input;

    /**
     * @var OutputInterface
     */
    private $_output;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var State
     */
    private $_state;

    /**
     * @var array
     */
    private $_cleaners;

    /**
     * Clean constructor.
     * @param StoreManagerInterface $storeManager
     * @param State $state
     * @param null $name
     * @param array $cleaners
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        State $state,
        $name = null,
        $cleaners = []
    ) {
        parent::__construct($name);
        $this->_storeManager = $storeManager;
        $this->_state = $state;
        $this->_cleaners = $cleaners;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('magedirect:db:clean');
        $this->setDescription('Clean Database.');
        $this->setDefinition([
            new InputArgument(
                self::DATA_TYPE,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Space-separated list of date types to clean.'
            ),
        ]);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_input = $input;
        $this->_output = $output;

        foreach ($this->_getCleanDataTypes() as $cleanDataType) {
            /** @var CleanerInterface $cleaner */
            $cleaner = $this->_cleaners[$cleanDataType];
            $cleaner->setOutput($this->_output)->clean();
        }
    }

    /**
     * @return array
     */
    private function _getCleanDataTypes()
    {
        $enteredDataTypes = $this->_input->getArgument(self::DATA_TYPE);
        if (empty($enteredDataTypes)) {
            return array_keys($this->_cleaners);
        }

        $dataTypesToClean = [];

        foreach (array_keys($this->_cleaners) as $cleaner) {
            if (in_array($cleaner, $enteredDataTypes)) {
                $dataTypesToClean[] = $cleaner;
            }
        }

        return $dataTypesToClean;
    }

}
