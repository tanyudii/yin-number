<?php

namespace tanyudii\YinNumber\Contracts;

/**
 * Interface WithNumberSetting
 * @package tanyudii\YinNumber\Contracts
 */
interface WithNumberSetting
{
    /**
     * @return string
     */
    public function getDateColumn();

    /**
     * @return string
     */
    public function getNumberColumn();
}
