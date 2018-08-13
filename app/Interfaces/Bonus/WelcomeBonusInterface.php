<?php
namespace App\Interfaces\Bonus;

interface WelcomeBonusInterface
{
	/**
     * Get CW Bonus Report
     *
     * @return \Illuminate\Support\Collection
     */
    public function runDailyPayout();

    public function welcomeBonusClawback();
}