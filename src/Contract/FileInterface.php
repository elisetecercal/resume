<?php

namespace Cekurte\Resume\Contract;

use Cekurte\Resume\Exception\FileNotExistsException;
use Symfony\Component\Console\Input\InputInterface;

interface FileInterface
{
    /**
     * Constructor.
     *
     * @param  InputInterface $input
     *
     * @throws FileNotExistsException
     */
    public function __construct(InputInterface $input);

    /**
     * Get the filename.
     *
     * @return string
     */
    public function getFilename();
}
