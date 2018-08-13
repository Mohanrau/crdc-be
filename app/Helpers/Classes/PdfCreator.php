<?php
namespace App\Helpers\Classes;

use \Mpdf\Mpdf;

/**
 * PdfCreator extend mPDF to get total page number.
 */
class PdfCreator extends Mpdf
{
	/**
	 * Constructor
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);
	}

	/**
     * Get current total page number.
     *
     * @return int
     */
	public function getTotalPage(){
		return count($this->pages);
	}
}