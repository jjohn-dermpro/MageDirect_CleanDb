<?php

namespace MageDirect\CleanDb\Cleaner;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Source of option values in a form of value-label pairs
 *
 * @api
 */
interface CleanerInterface
{
    /**
     * @return mixed
     */
    public function clean();

    /**
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput(OutputInterface $output);
}
