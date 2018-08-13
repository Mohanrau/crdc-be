<?php
namespace App\Repositories\Bonus;

use App\{
    Interfaces\Bonus\BonusInterface, Models\Bonus\BonusSummary, Models\Bonus\BonusWelcomeBonusDetails, Models\Bonus\BonusTeamBonusDetails, Models\Bonus\BonusMentorBonusDetails, Models\Bonus\BonusQuarterlyDividendDetails, Models\Bonus\BonusMemberTreeDetails, Models\Locations\Country, Models\Users\User, Models\Members\Member, Models\Members\MemberTree, Models\General\CWSchedule, Models\General\CWDividendSchedule, Models\Stockists\StockistCommission, Repositories\BaseRepository
};
use \Mpdf\Mpdf;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use \PhpOffice\PhpSpreadsheet\{
    Spreadsheet,
    Writer\Xlsx
};
use App\Helpers\Classes\MemberAddress;
use App\Helpers\Classes\Uploader;
use App\Interfaces\Members\MemberTreeInterface;

class BonusRepository extends BaseRepository implements BonusInterface
{
    private $welcomeBonusObj,
            $teamBonusObj,
            $mentorBonusObj,
            $quarterlyDividendBonusObj,
            $userObj,
            $cwScheduleObj,
            $cWDividendScheduleObj,
            $memberObj,
            $memberTreeObj,
            $memberTreeDetailsObj,
            $stockistCommissionObj,
            $pdfConfig,
            $memberAddressHelper,
            $uploader,
            $memberTreeRepository,
            $countryObj;

    /**
     * BonusRepository constructor.
     *
     * @param BonusSummary $model
     * @param BonusWelcomeBonusDetails $welcomeBonus
     * @param BonusTeamBonusDetails $teamBonus
     * @param BonusMentorBonusDetails $mentorBonus
     * @param BonusQuarterlyDividendDetails $quarterlyDividendBonus
     * @param BonusMemberTreeDetails $memberTreeDetailsObj
     * @param User $user
     * @param CWSchedule $cwSchedule
     * @param CWDividendSchedule $cWDividendSchedule
     * @param Member $member
     * @param MemberTree $memberTree
     * @param MemberAddress $memberAddress
     * @param Uploader $uploader
     * @param MemberTreeInterface $memberTreeRepository
     * @param StockistCommission $stockistCommission
     * @param Country $country
     */
    public function __construct(
        BonusSummary $model,
        BonusWelcomeBonusDetails $welcomeBonus,
        BonusTeamBonusDetails $teamBonus,
        BonusMentorBonusDetails $mentorBonus,
        BonusQuarterlyDividendDetails $quarterlyDividendBonus,
        BonusMemberTreeDetails $memberTreeDetailsObj,
        User $user,
        CWSchedule $cwSchedule,
        CWDividendSchedule $cWDividendSchedule,
        Member $member,
        MemberTree $memberTree,
        MemberAddress $memberAddress,
        Uploader $uploader,
        MemberTreeInterface $memberTreeRepository,
        StockistCommission $stockistCommission,
        Country $country
    )
    {
        parent::__construct($model);

        $this->welcomeBonusObj = $welcomeBonus;

        $this->teamBonusObj = $teamBonus;

        $this->mentorBonusObj = $mentorBonus;

        $this->quarterlyDividendBonusObj = $quarterlyDividendBonus;

        $this->memberTreeDetailsObj = $memberTreeDetailsObj;

        $this->userObj = $user;

        $this->cwScheduleObj = $cwSchedule;

        $this->cWDividendScheduleObj = $cWDividendSchedule;

        $this->pdfConfig = [
            'mode' => 'utf-8', 
            'format' => 'A4', 
            'margin_left' => 20, 
            'margin_right' => 10, 
            'margin_top' => 10, 
            'margin_bottom' => 10
        ];

        $this->memberAddressHelper = $memberAddress;

        $this->uploader = $uploader;

        $this->memberObj = $member;

        $this->memberTreeObj = $memberTree;

        $this->memberTreeRepository = $memberTreeRepository;

        $this->stockistCommissionObj = $stockistCommission;

        $this->countryObj = $country;
    }
    
    /**
     * Get CW Bonus Report
     *
     * @param int $cwId
     * @param array $userIds
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCwBonusReport(int $cwId, array $userIds)
    {
        $spreadsheet = new Spreadsheet();

        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 600); // 10 mins

        $countries = $this->countryObj->all()->keyBy('id');
        $cwName = $this->cwScheduleObj->where('id', $cwId)->get()->first()->cw_name;

        // Creating header
        $header = ['No', 'Member Code', 'Name', 'Address1', 'Address2', 'Registered Country', 'Nationality', 'CW', 'RP(USD)', 'WB(USD)', 'TB(USD)', 'MB(USD)', 'QD(USD)', 'Incentive(USD)', 'Bonus Adjustment', 'Total Bonus(USD)', 'Currency Rate', 'Local Bonus', 'WHTax', 'TWSI(Social Insurance) 1.91%', 'Payment Adjustment', 'GST', 'Net Bonus(E-wallet)', 'f_idno', 'f_gst_company_name', 'f_gst_no', 'Taiwan enrollment form', 'Permanent Addr1', 'Permanent Addr2', 'PostCode', 'Country', 'City', 'State', 'Member IC'];

        $col = "A";

        foreach ($header as $value)
        {
            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }
        $styleArray = [
            'font' => [
                'bold' => true,
            ]
        ];

        $spreadsheet->getActiveSheet()->getStyle("A1:AH1")->applyFromArray($styleArray);

        $bonusSummary = $this->modelObj->where('bonuses_summary.cw_id', '=', $cwId);

        if (count($userIds) > 0)
        {
            $bonusSummary = $bonusSummary->whereIn('bonuses_summary.user_id', $userIds);
        }

        $bonusSummary = $bonusSummary->select(
            'bonuses_summary.*',
            'users.name',
            'users.old_member_id',
            'members.ic_passport_number',
            'members.nationality_id',
            'members.country_id as member_country_id'
        )   ->leftJoin('users', 'users.id', '=', 'bonuses_summary.user_id')
            ->leftJoin('members', 'members.user_id', '=', 'bonuses_summary.user_id')
           // ->leftJoin('members_addresses_data', 'members_addresses_data.user_id', '=', 'bonuses_summary.user_id')
//               ->take(40000)
//            ->orderBy('id', 'desc')
            ->get();

        $row = 2;

        $activeSheetObj = $spreadsheet->getActiveSheet();

        $bonusSummary->each(function($summary) use (&$row, $countries, $cwName, $activeSheetObj) {
            $permanentAddress = [];
            $addressData = [];
            $summary->address_data = [];

            $summary->name = (empty($summary->tax_company_name)) ? $summary->name : $summary->tax_company_name;
//
//            if($summary->address_data)
//            {
//                $addressData = $this->memberAddressHelper->getAddressAsYYStructure($summary->address_data, "Correspondence");
//
//                $permanentAddress = $this->memberAddressHelper->getAddressAsYYStructure($summary->address_data, "Permanent");
//            }
//            else
//            {
//                $summary->address_data = [];
//            }

            if($summary->currency_rate == 0)
            {
                $currencyRateFromUsd = 1;
            }
            else
            {
                $currencyRateFromUsd = 1 / $summary->currency_rate;
            }

            $activeSheetObj->fromArray(
              [
                  $row-1,
                  $summary->old_member_id,
                  $summary->name,
                  '',
                  '',
                  $countries->get($summary->country_id)->code_iso_2,
                  $countries->get($summary->nationality_id)->code_iso_2,
                  $cwName,
                  '0.00',
                  $summary->welcome_bonus,
                  $summary->team_bonus_diluted,
                  $summary->mentor_bonus_diluted,
                  $summary->quarterly_dividend,
                  $summary->incentive,
                  '0.00', //bonus adjustment
                  $summary->total_gross_bonus,
                  $currencyRateFromUsd,
                  $summary->total_gross_bonus_local_amount,
                  '0.00', //WHTax
                  '0.00',//TWSI(Social Insurance) 1.91%
                  '0.00',//Payment Adjustment
                  $summary->total_tax_amount,
                  $summary->total_net_bonus_payable,
                  $summary->ic_passport_number,
                  $summary->tax_company_name,
                  $summary->tax_no,
                  'N',
                  '',
                  '',
                  '',
                  '',
                  '',
                  '',
                  $summary->ic_passport_number
              ],
              null,
              'A'.$row
            );
/*
            $activeSheetObj->setCellValue("A".$row, $row-1);
            $activeSheetObj->setCellValue("B".$row, $summary->old_member_id);
            $activeSheetObj->setCellValue("C".$row, $summary->name);

//            $spreadsheet->setActiveSheetIndex(0)->setCellValue("D".$row, isset($addressData['addr1']) ? $addressData['addr1'] : '');
//            $spreadsheet->setActiveSheetIndex(0)->setCellValue("E".$row, isset($addressData['addr2']) ? $addressData['addr2'] : '');
            $activeSheetObj->setCellValue("D".$row,  '');
            $activeSheetObj->setCellValue("E".$row, '');

            $activeSheetObj->setCellValue("F".$row, $countries->get($summary->country_id)->code_iso_2);
            $activeSheetObj->setCellValue("G".$row, $countries->get($summary->nationality_id)->code_iso_2);
            $activeSheetObj->setCellValue("H".$row, $cwName);
            $activeSheetObj->setCellValue("I".$row, 0.00); //retail profit
            $activeSheetObj->setCellValue("J".$row, $summary->welcome_bonus);
            $activeSheetObj->setCellValue("K".$row, $summary->team_bonus_diluted);
            $activeSheetObj->setCellValue("L".$row, $summary->mentor_bonus_diluted);
            $activeSheetObj->setCellValue("M".$row, $summary->quarterly_dividend);
            $activeSheetObj->setCellValue("N".$row, $summary->incentive);
            $activeSheetObj->setCellValue("O".$row, 0.00); //bonus adjustment
            $activeSheetObj->setCellValue("P".$row, $summary->total_gross_bonus);
            $activeSheetObj->setCellValue("Q".$row, $currencyRateFromUsd);
            $activeSheetObj->setCellValue("R".$row, $summary->total_gross_bonus_local_amount);
            $activeSheetObj->setCellValue("S".$row, 0.00);//WHTax
            $activeSheetObj->setCellValue("T".$row, 0.00);//TWSI(Social Insurance) 1.91%
            $activeSheetObj->setCellValue("U".$row, 0.00);//Payment Adjustment
            $activeSheetObj->setCellValue("V".$row, $summary->total_tax_amount);
            $activeSheetObj->setCellValue("W".$row, $summary->total_net_bonus_payable);
            $activeSheetObj->getCell("X".$row)->setValueExplicit($summary->ic_passport_number,"s");//f_idno
            $activeSheetObj->setCellValue("Y".$row, $summary->tax_company_name);//f_gst_company_name
            $activeSheetObj->setCellValue("Z".$row, $summary->tax_no);//f_gst_no
            $activeSheetObj->setCellValue("AA".$row, 'N');//Taiwan enrollment form

            $activeSheetObj->setCellValue("AB".$row,  '');
            $activeSheetObj->setCellValue("AC".$row, '');
            $activeSheetObj->setCellValue("AD".$row,  '');
            $activeSheetObj->setCellValue("AE".$row, '');
            $activeSheetObj->setCellValue("AF".$row,  '');
            $activeSheetObj->setCellValue("AG".$row, '');
//            $activeSheetObj->setCellValue("AB".$row, isset($permanentAddress['addr1']) ? $permanentAddress['addr1'] : '');
//            $activeSheetObj->setCellValue("AC".$row, isset($permanentAddress['addr2']) ? $permanentAddress['addr2'] : '');
//            $activeSheetObj->setCellValue("AD".$row, isset($permanentAddress['postcode']) ? $permanentAddress['postcode'] : '');
//            $activeSheetObj->setCellValue("AE".$row, isset($permanentAddress['country']) ? $permanentAddress['country'] : '');
//            $activeSheetObj->setCellValue("AF".$row, isset($permanentAddress['city']) ? $permanentAddress['city'] : '');
//            $activeSheetObj->setCellValue("AG".$row, isset($permanentAddress['state']) ? $permanentAddress['state'] : '');

            $activeSheetObj->getCell("AH".$row)->setValueExplicit($summary->ic_passport_number,"s");
*/
            $row++;
        });

        unset($bonusSummary); // clear memory
        $activeSheetObj->getStyle("I2:P".($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
        $activeSheetObj->getStyle("R2:W".($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
        $activeSheetObj->getStyle('F1:G'.($row-1))->getFill()->setFillType("solid")->getStartColor()->setARGB('FFFFFF00');
        $activeSheetObj->getStyle('S1:T'.($row-1))->getFill()->setFillType("solid")->getStartColor()->setARGB('FFFFFF00');
        $activeSheetObj->getStyle('W1:W'.($row-1))->getFill()->setFillType("solid")->getStartColor()->setARGB('FF92D050');
        $activeSheetObj->getStyle('AA1:AA'.($row-1))->getFill()->setFillType("solid")->getStartColor()->setARGB('FFFF0000');

//        // Output excel file
        $outputPath = Config::get('filesystems.subpath.bonuses.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.bonuses.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('bonus_statement') . '.xlsx';

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($outputPath . $fileName);

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect([['download_link' => $fileUrl]]);
    }

    /**
     * Get bonus statement based on CW name and distributor code
     *
     * @param int $cwId
     * @param array $userIds
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBonusStatement(int $cwId, array $userIds)
    {
        $result = collect([]);

        foreach ($userIds as $userId)
        {
            $bonus = $this->getBonusStatementData($cwId, $userId);

            $link = $this->outputPdf("bonus.bonus_statement", ['bonus' => $bonus]);

            $result->push(['user_id' => $userId, 'download_link' => $link]);
        }

        return $result;
    }

    /**
     * Get Yearly Income statement
     *
     * @return \Illuminate\Support\Collection
     */
    public function getYearlyIncomeStatement()
    {
        $data = $this->getYearlyIncomeStatementData();

        $config = $this->pdfConfig;

        return $this->outputPdf("bonus.yearly_income_statement", ['data' => $data], $config);
    }

    /**
     * Get Yearly Income summary
     * @param int $year
     * @param int $countryId
     * @param array $userIds
     *
     * @return \Illuminate\Support\Collection
     */
    public function getYearlyIncomeSummary(int $year, int $countryId, array $userIds)
    {
        $spreadsheet = new Spreadsheet();

        // Creating header
        $header = ['f_code', 'f_name','f_gst_company_name', 'f_tax_no_gst'];

        $cwList = $this->cwScheduleObj->whereRaw("year(date_from) = ?", [$year])->orderBy('date_from')->get();

        $col = "A";

        foreach ($header as $value)
        {
            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }

        $query = "users.old_member_id, users.name, bonuses_summary.tax_company_name as tax_company, bonuses_summary.tax_no, ";

        foreach ($cwList as $cw)
        {
            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $cw->cw_name);

            $query .="sum(case when cw_id= $cw->id then total_gross_bonus_local_amount end) as '$cw->cw_name',";

            $col++;
        }

        $cell = $col."1";
        
        $query .= "sum(total_gross_bonus_local_amount) as total ";
        
        $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, "total");

        if (count($userIds) == 1 && array_key_exists("*", $userIds))
        {
            $modelObj = $this->modelObj;
        }
        else if(count($userIds) >= 1)
        {
            $modelObj = $this->modelObj->whereIn('user_id', $userIds);
        }

        $list = $modelObj->select(\DB::raw($query))
                         ->join('users', 'bonuses_summary.user_id', 'users.id')
                         ->join('cw_schedules', 'bonuses_summary.cw_id', 'cw_schedules.id')
                         ->whereRaw("year(cw_schedules.date_from) = ? ", [$year])
                         ->where('country_id', '=', $countryId)
                         ->orderBy('users.name')
                         ->groupBy('users.name', 'users.old_member_id', 'bonuses_summary.tax_company_name', 'bonuses_summary.tax_no')
                         ->get();

        $row = 2;

        foreach ($list as $data)
        {
            $col = "A";

            $total = 0;

            foreach($data->getAttributes() as $attribute)
            {
                $cell = $col.$row;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $attribute);

                if ($col >= "E" || strlen($col)>1)
                {
                    $spreadsheet->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');

                    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth(12);
                }

                $col++;
            }

            $row++;
        }

        // Output excel file

        $outputPath = Config::get('filesystems.subpath.bonuses.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.bonuses.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('yearly_income_summary') . '.xlsx';
        
        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($outputPath . $fileName);

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect([['download_link' => $fileUrl]]);
    }

    /**
     * Get CP-58 form
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCP58Form()
    {
        $pdf = new Mpdf();

        $pdf->SetImportUse();

        if (!Storage::disk('local')->exists("Borang_CP58_1.pdf"))
        {
            Storage::disk('local')->put(
                "Borang_CP58_1.pdf", 
                Storage::disk('s3')->get("bonus/lhdn_form/Borang_CP58_1.pdf")
            );
        }

        $pagecount = $pdf->SetSourceFile(storage_path()."/app/Borang_CP58_1.pdf");

        // Import the last page of the source PDF file
        $tplId = $pdf->ImportPage($pagecount);
        $pdf->UseTemplate($tplId);
        $pdf->SetDefaultBodyCSS( "font-size", "8pt" );
        $pdf->SetDefaultBodyCSS( "font-family", "Arial" );

        $pdf->WriteFixedPosHTML('2017', 130, 36, 10, 10);

        //PART A: PAYER COMPANY'S PARTICULARS
        $pdf->WriteFixedPosHTML('ELKEN GLOBAL SDN BHD', 42, 50, 160, 100);
        $pdf->WriteFixedPosHTML('20, BANGUNAN ELKEN JALAN 1/137C, BATU 5 JALAN KELANG LAMA KUALA LUMPUR', 42, 60, 160, 100);
        $pdf->WriteFixedPosHTML('61901291929200', 105, 70, 100, 100);
        $pdf->WriteFixedPosHTML('18957646332', 105, 75, 100, 100);

        //PART B: RECIPIENT'S PARTICULARS
        $pdf->WriteFixedPosHTML('CHONG BOON KIAT', 42, 86, 160, 100);
        $pdf->WriteFixedPosHTML('699, JALAN 29, SALAK SELATAN BARU, 57100 KUALA LUMPUR', 42, 97, 160, 100);
        $idHtml = "<div style='font-size:7pt;background-color:white'>
                No. Pendaftaran / Kad Pengenalan / Polis / Tentera / Pasport *<br/>
                <span style='font-size:6pt'><i>Registration / Identity Card / Police / Army / Passport No. *</i></span><br/>
                <span style='font-size:5pt;color:#555555'>( * Potong yang tidak berkenaan / Delete whichever is not applicable)</span>
                </div>";
        $pdf->WriteFixedPosHTML($idHtml, 17,106, 80,20);
        $idHtml = "<div style='font-size:8pt;background-color:white'>
                No. Pendaftaran / Kad Pengenalan / Polis / Tentera / Pasport *<br/>
                <span style='font-size:7pt'><i>Registration / Identity Card / Police / Army / Passport No. *</i></span><br/>
                <span style='font-size:5pt;color:#555555'>( * Potong yang tidak berkenaan / Delete whichever is not applicable)</span>
                </div>";
        $pdf->WriteFixedPosHTML($idHtml, 14,201, 80,20);
        $pdf->WriteFixedPosHTML('888888-14-9990', 105,106, 50,20);
        //$pdf->WriteFixedPosHTML('SG', 71,117, 50,20);
        //$pdf->WriteFixedPosHTML('12338888800', 86,117, 50,20);
        //$pdf->WriteFixedPosHTML('1', 86,126, 50,20);

        //PART C: PARTICULARS OF INCENTIVE PAYMENT
        $pdf->WriteFixedPosHTML('<div style="text-align:right;">99,995.25</div>', 171,141, 30,20);
        $pdf->WriteFixedPosHTML('<div style="text-align:right;">0.00</div>', 171,166, 30,40);
        $pdf->WriteFixedPosHTML('Voucher', 50,170, 50,20);
        $pdf->WriteFixedPosHTML('<div style="text-align:right;">3,846.00</div>', 171,171, 30,20);
        $pdf->WriteFixedPosHTML('<div style="text-align:right;">103,841.25</div>', 171,179, 30,20);

        //PART D: PAYER'S DECLARATION
        $pdf->WriteFixedPosHTML('Computer generated document. No signature is required', 30,191, 160,20);
        $pdf->WriteFixedPosHTML('16 / 1 / 2018', 175,254, 30,20);
        
        $absoluteUrlPath = Config::get('filesystems.subpath.bonuses.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('cp58') . '.pdf';

        $fileUrl = $this->uploader->createS3File($absoluteUrlPath . $fileName, $pdf->Output($fileName, "S"), true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * Get Yearly Bonus Statement - LHDN excel
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLHDNsheet()
    {
        $data = ['cw' => []];

        return $this->outputSingleSheetExcel('bonus.lhdn', $data);
    }

    /**
     * Get Yearly Bonus Statement - CP-37F form
     *
     * @return \Illuminate\Support\Collection
     * @throws \Mpdf\MpdfException
     */
    public function getCp37fForm()
    {
        $pdf = new Mpdf();

        $pdf->SetImportUse();

        if (!Storage::disk('local')->exists("cp37f.pdf"))
        {
            Storage::disk('local')->put(
                "Borang_CP58_1.pdf", 
                Storage::disk('s3')->get("bonus/lhdn_form/cp37f.pdf")
            );
        }

        $pagecount = $pdf->SetSourceFile(storage_path()."/app/cp37f.pdf");

        $import_page = $pdf->ImportPage();
        $pdf->UseTemplate($import_page);

        $pdf->SetDefaultBodyCSS( "font-size", "10pt" );
        $pdf->SetDefaultBodyCSS( "font-family", "Arial" );

        //PART A: PARTICULAR OF PAYER
        $pdf->WriteFixedPosHTML('1120093V', 140, 70, 30, 10);
        $pdf->WriteFixedPosHTML('C23648827-09', 140, 79, 30, 10);
        $pdf->WriteFixedPosHTML('ELKEN GLOBAL SDN BHD', 53, 87, 140, 10);
        $pdf->WriteFixedPosHTML('NO.20,JALAN 1/137C,JALAN KLANG LAMA,58000 KUALA LUMPUR', 53, 94, 140, 20);

        //PART B: PARTICULAR OF PERSON TO WHOM INCOME CHARGED
        $pdf->WriteFixedPosHTML('S1244247E', 140, 115, 30, 10);
        $pdf->WriteFixedPosHTML('MANOGARAN S/O GOPAL NAIDU', 53, 134, 140, 10);
        $pdf->WriteFixedPosHTML('BLK 235 ANG MO KIO AVE 3, #02-1104, 560235, SINGAPORE', 53, 140, 140, 20);
        $pdf->WriteFixedPosHTML('SINGAPORE', 53, 152, 140, 10);

        //PART C: PARTICULARS OF DEDUCTIONS
        $pdf->WriteFixedPosHTML('W21-2017', 20, 195, 30, 10);
        $pdf->WriteFixedPosHTML('09/11/2017', 54, 195, 30, 10);
        $pdf->WriteFixedPosHTML('RM 203.99', 82, 195, 50, 10);
        $pdf->WriteFixedPosHTML('RM 20.40', 118, 195, 50, 10);
        $pdf->WriteFixedPosHTML('RM 183.59', 160, 195, 50, 10);

        $pdf->SetDefaultBodyCSS( "font-size", "8pt" );
        $pdf->WriteFixedPosHTML('000868', 80, 220, 30, 10);
        $pdf->WriteFixedPosHTML('RM 20.40', 122, 220, 50, 10);
        $pdf->WriteFixedPosHTML('TAN SOO KIEW', 125, 230, 50, 10);
        $pdf->WriteFixedPosHTML('SENIOR FINANCE MANAGER', 126, 237, 50, 10);
        $pdf->WriteFixedPosHTML('03-7985 8826', 130, 245, 50, 10);
        $pdf->WriteFixedPosHTML('13/11/2017', 27, 255, 50, 10);

        $pdf->AddPage();

        for ($i=2; $i<=$pagecount; $i++) {
            $import_page = $pdf->ImportPage($i);
            $pdf->UseTemplate($import_page);

            if ($i < $pagecount)
                $pdf->AddPage();
        }

        $absoluteUrlPath = Config::get('filesystems.subpath.bonuses.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('lhdn') . '.pdf';

        $fileUrl = $this->uploader->createS3File($absoluteUrlPath . $fileName, $pdf->Output($fileName, "S"), true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * Get Self Billed Invoice
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSelfBilledInvoice()
    {
        $data = $this->getYearlyIncomeStatementData();

        $config = $this->pdfConfig;

        return $this->outputPdf("bonus.self_billed_invoice", ['data' => $data], $config);
    }

    /**
     * Get Self Billed Invoice - Stockist
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSelfBilledInvoiceStockist()
    {
        $data = $this->getYearlyIncomeStatementData();

        $config = $this->pdfConfig;

        return $this->outputPdf("bonus.self_billed_invoice_stockist", ['data' => $data], $config);
    }

    /**
     * Get Stockist Commission statement
     *
     * @param int $cwId
     * @param int $stockistId
     * @return \Illuminate\Support\Collection
     */
    public function getStockistCommissionStatement($cwId, $stockistId)
    {
        $data = $this->stockistCommissionObj->where('cw_id', $cwId)->where('stockist_id', $stockistId)->first();

        $config = $this->pdfConfig;

        return $this->outputPdf("bonus.stockist_commission_statement", ['data' => $data], $config);
    }

    /**
     * Get Sponsor Tree report
     *
     * @param int $cwId
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getSponsorTree(int $cwId, int $userId)
    {
        $childrenId = $this->memberTreeRepository->getAllSponsorDescendant($userId);
        
        $ids = $childrenId->pluck('user_id');
        $ids->push($userId);
        $list = collect();

        foreach ($ids->chunk(10) as $value)
        {
            $obj = $this->modelObj->where('cw_id', '=', $cwId)->whereIn('user_id', $value->toArray())->get();

            if (count($obj) > 0)
            {
                foreach ($obj as $bonusSummary)
                {
                    $list->push($bonusSummary);
                }
            }
        }

        $spreadsheet = new Spreadsheet();

        //create header
        $header = ["Member Code", "Sponsor Code", "Placement Code", "Level", "Sequence", "LPOS", "HPOS", "EPOS", "PCV", "OPS", "IPS", "GCV", "Pair CV", "TB",  "LBA", "RBA", "Percent", "Flush Amount", "Power Leg", "Pay Leg", "Tri-formation qualifier", "Power VLeg", "Pay VLeg", "VPL payout", "Original payout value (excl capping)", "Remark"];

        $col = "A";

        foreach ($header as $value)
        {
            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }

        //insert data row by row
        $row = 2;

        foreach ($list as $bonusSummary)
        {

            $memberTree = $this->memberTreeDetailsObj
                                ->where('user_id', '=', $bonusSummary->user_id)
                                ->where('cw_id', '=', $bonusSummary->cw_id)
                                ->first();

            $teamBonusDetails = $this->teamBonusObj
                                    ->where('bonuses_summary_id', '=', $bonusSummary->id)
                                    ->first();
            //Member Code
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("A".$row, $memberTree->user->old_member_id);
            //Sponsor Code
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("B".$row, $memberTree->sponsorParent->old_member_id);
            //Placement Code
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("C".$row, $memberTree->placementParent->old_member_id);
            //Level
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("D".$row, $memberTree->sponsor_depth_level);
            //Sequence
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("E".$row, 0);
            //LPO - @TODO: pending add new column in bonuses_summary
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("F".$row, '');
            //HPO
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("G".$row, $bonusSummary->highestRank->rank_code);
            //EPO
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("H".$row, $bonusSummary->effectiveRank->rank_code);
            //PKG
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("I".$row, $bonusSummary->enrollmentRank->rank_code);
            //PCV
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("J".$row, $memberTree->personal_sales_cv);
            //MCV
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("K".$row, $memberTree->member_sales_cv);
            //OPS
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("L".$row, $teamBonusDetails->optimising_personal_sales);
            //IPS - @TODO: N/A
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("M".$row, 0);
            //GCV
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("N".$row, $teamBonusDetails->gcv);
            //Pair GCV 
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("O".$row, $teamBonusDetails->gcv_calculation);
            //TB
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("P".$row, $bonusSummary->team_bonus_diluted);
            //LBA
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("Q".$row, $memberTree->sponsor_total_active_ba_left);
            //RBA
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("R".$row, $memberTree->sponsor_total_active_ba_right);
            //Percent
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("S".$row, $teamBonusDetails->team_bonus_percentage);
            //Flush Amount
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("T".$row, $teamBonusDetails->gcv_flush);
            //Power Leg
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("U".$row, 0);
            //Pay Leg
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("V".$row, 0);
            //Tri-formation qualifier
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("W".$row, $memberTree->is_tri_formation);
            //Original payout value (excl capping)
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("X".$row, $teamBonusDetails->team_bonus_cv);

            // $spreadsheet->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');

            // $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth(12);
               

            $row++;
        }

        // Output excel file

        $outputPath = Config::get('filesystems.subpath.bonuses.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.bonuses.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('sponsor_tree') . '.xlsx';
        
        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($outputPath . $fileName);

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect([['download_link' => $fileUrl]]);
    }

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getIncentiveSummary()
    {
        $data = [];

        return $this->outputSingleSheetExcel('bonus.incentive_summary', $data);
    }

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWelcomeBonusSummary()
    {
        $data = [];

        return $this->outputSingleSheetExcel('bonus.welcome_bonus_summary', $data);
    }

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWelcomeBonusDetail()
    {
        $data = [];

        return $this->outputSingleSheetExcel('bonus.welcome_bonus_details', $data);
    }

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBonusAdjustmentListing()
    {
        $data = [];

        return $this->outputSingleSheetExcel('bonus.bonus_adjustment_listing', $data);
    }

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function get77kReport()
    {
        $fileName = $this->uploader->getRandomFileName('TW_77k_Report');

        $outputPath = Config::get('filesystems.subpath.bonuses.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.bonuses.absolute_url_path');

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        \Excel::create($fileName, function($excel) {

            $excel->sheet('Yearly Purchase Summary', function($sheet) {

                $data = [];

                $sheet->loadView('bonus.taiwan_77k_report_sheet1')->with($data);

            });

            $excel->sheet('Yearly Purchase Detail Listing', function($sheet) {

                $data = [];

                $sheet->loadView('bonus.taiwan_77k_report_sheet2')->with($data);

            });

        })->store('xlsx', $outputPath);

        $fileName = $fileName . "xlsx";

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * Get Incentive summary report
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWhtReport()
    {
        $fileName = $this->uploader->getRandomFileName('Taiwan_WHT_Tax');

        $outputPath = Config::get('filesystems.subpath.bonuses.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.bonuses.absolute_url_path');

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        \Excel::create($fileName, function($excel) {

            $excel->sheet('Yearly WHT Summary', function($sheet) {

                $data = [];

                $sheet->loadView('bonus.taiwan_wht_report_sheet1')->with($data);

            });

            $excel->sheet('Weekly WHT Summary', function($sheet) {

                $data = [];
                
                $sheet->loadView('bonus.taiwan_wht_report_sheet2')->with($data);

            });

        })->store('xlsx', $outputPath);

        $fileName = $fileName . "xlsx";

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * Extract bonus statement data from bonus tables based on CW name and distributor code
     *
     * @param int $cwId
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    private function getBonusStatementData($cwId, $userId)
    {
        // Get bonus summary and transform tax data
        $summary = $this->modelObj->where('user_id', '=', $userId)->where('cw_id', '=', $cwId)->first();

        $summary->name = $summary->user->name;

        if(!empty($summary->tax_company_name))
        {
            $summary->name = $summary->tax_company_name;
        }
        // Get address data from json format
        if($summary->user->member->address)
        {
            $summary->address_data = $this->memberAddressHelper->getCorrespondenceAddress($summary->user->member->address->address_data);
        } else {
            $summary->address_data = "";
        }

        // Get bonus details
        $welcomeBonus = $this->welcomeBonusObj->where('bonuses_summary_id', '=', $summary->id)->get();
        
        $teamBonus = $this->teamBonusObj->where('bonuses_summary_id', '=', $summary->id)->get();

        $mentorBonus = $this->mentorBonusObj->where('bonuses_summary_id', '=', $summary->id)->get();

        $cWDividendSchedule = $this->cWDividendScheduleObj->where('to_cw_id', '=', $cwId)->first();

        if ($cWDividendSchedule)
        {
            $cwRange = array($cWDividendSchedule->from_cw_id, $cWDividendSchedule->to_cw_id);

            $quarterlyDividendBonus = $this->quarterlyDividendBonusObj
                                        ->whereBetween('cw_id', $cwRange)
                                        ->where('user_id', '=', $summary->user_id)
                                        ->where('country_id', '=', $summary->country_id)
                                        ->orderBy('cw_id')
                                        ->get();
        }
        else
        {
            $quarterlyDividendBonus = collect([]);
        }

        if($summary->currency_rate == 0){
            $currencyRateFromUsd = 1;
        }else{
            $currencyRateFromUsd = 1 / $summary->currency_rate;
        }
        
        return collect(
            [
                'summary' => $summary,
                'welcomeBonus' => $welcomeBonus,
                'teamBonus' => $teamBonus,
                'mentorBonus' => $mentorBonus,
                'quarterlyDividendBonus' => $quarterlyDividendBonus,
                'usdConversionRate' => $currencyRateFromUsd
            ]
        );
    }

    /**
     * Extract yearly income statement data from bonus tables based on CW name and distributor code
     *
     * @param string $cwName
     * @param string $memberCode
     * @return \Illuminate\Support\Collection
     */
    private function getYearlyIncomeStatementData()
    {
        return collect([]);
    }

    /**
     * Shared function to be used by request that create pdf and return download link
     *
     * @param string $view
     * @param array $data
     * @param array $pdfConfig
     * @return \Illuminate\Support\Collection
     */
    private function outputPdf($view, $data = [], $pdfConfig = null)
    {
        if ($pdfConfig)
        {
            $config = $pdfConfig;
        }
        else
        {
            $config = $this->pdfConfig;
        }

        $pdf = new Mpdf($config);

        $pdf->autoLangToFont = true;

        $html = \View::make($view, $data)->render();

        $pdf->WriteHTML($html);

        $absoluteUrlPath = Config::get('filesystems.subpath.bonuses.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName($view) . '.pdf';

        $fileUrl = $this->uploader->createS3File($absoluteUrlPath . $fileName, $pdf->Output($fileName, "S"), true);

        return collect([['download_link' => $fileUrl]]);
    }

    /**
     * Shared function to be used by request that create excel and return download link
     *
     * @param string $view
     * @param array $data
     * @return \Illuminate\Support\Collection
     */
    private function outputSingleSheetExcel($view, $data)
    {
        $fileName = $this->uploader->getRandomFileName($view);

        $outputPath = Config::get('filesystems.subpath.bonuses.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.bonuses.absolute_url_path');

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        \Excel::create($fileName, function($excel) use ($data, $view) {

            $excel->sheet('sheet1', function($sheet) use ($data, $view) {

                $sheet->loadView($view)->with($data);

            });

        })->store('xlsx', $outputPath);

        $fileName = $fileName . "xlsx";

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect(['download_link' => $fileUrl]);
    }
}