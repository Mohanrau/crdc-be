<?php
namespace App\Interfaces\Members;

use App\Models\Users\User;

interface MemberInterface
{
    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param int $icPassportVerified
     * @param string $text
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @param int $sponsorId
     * @param string $exactSearch
     * @param int $treeFilter - to filter member from the tree
     * @return mixed
     */
    public function getMembersByFilters(
        int $countryId = 0,
        int $icPassportVerified = 3,
        string $text = '',
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0,
        int $sponsorId = 0,
        string $exactSearch = '',
        int $treeFilter = 0
    );

    /**
     * get member by id
     *
     * @param int $id
     * @param array $relations
     * @return mixed
     */
    public function find(int $id, array $relations = []);

    /**
     * get member details for a given userId
     *
     * @param int $userId
     * @param int $uplinedId
     * @return mixed
     */
    public function memberDetails(int $userId, int $uplinedId = 0);

    /**
     * create new member
     *
     * @param User $user
     * @param int $countryId
     * @param array $data
     * @return mixed
     */
    public function create(User $user, int $countryId, array &$data);

    /**
     * update members
     *
     * @param array $data
     * @param int $userId
     * @return mixed
     */
    public function update(array $data, int $userId);

    /**
     * Update member rank
     *
     * @param array $data
     * @return mixed
     */
    public function updateMemberRank(array $data);

    /**
     * verify or reject the uploaded ic or passport for a given member(userId)
     *
     * @param array $data
     * @param int $userId
     * @return mixed
     */
    public function verifyMemberIcOrPassport(array $data, int $userId);

    /**
     * get member ranks records
     *
     * @param array $parameter
     * @return mixed
     */
    public function getMemberRanksList(array $parameter);

    /**
     * Get the specified member ranks resource.
     *
     * @param  int  $id
     * @return mixed
     */
    public function getMemberRanks($id);

    /**
     * Store a newly created member ranks resource.
     *
     * @param array $data
     * @return mixed
     */
    public function memberRanksStore(array $data);

    /**
     * get member status records
     *
     * @param array $parameter
     * @return mixed
     */
    public function getMemberStatusList(array $parameter);

    /**
     * Get the specified member status resource.
     *
     * @param  int  $id
     * @return mixed
     */
    public function getMemberStatus($id);

    /**
     * Store a newly created member status resource.
     *
     * @param array $data
     * @return mixed
     */
    public function memberStatusStore(array $data);

    /**
     * get member migrate records
     *
     * @param array $parameter
     * @return mixed
     */
    public function getMemberMigrateList(array $parameter);

    /**
     * Get the specified member migrate resource.
     *
     * @param  int  $id
     * @return mixed
     */
    public function getMemberMigrate($id);

    /**
     * Store a newly created member migrate resource.
     *
     * @param array $data
     * @return mixed
     */
    public function memberMigrateStore(array $data);

    /**
     * verify classic member given national ID
     *
     * @param string $nationalId
     * return mixed
     */
    public function verifyClassicMember(string $nationalId);

    /**
     * get placement network performance
     *
     * @param int $userId
     * @return mixed
     */
    public function getPlacementNetworkPerformance(int $userId);

    /**
     * get member campaign report data
     *
     * @param int $campaignId
     * @param int $userId
     * @return mixed
     */
    public function memberCampaignReport(int $campaignId, int $userId);

    /**
     * get member dashboard information
     *
     * @return mixed
     */
    public function memberDashboard();

    /**
     * Validate Member Email Address
     *
     * @param array $inputs
     * @return \Illuminate\Support\Collection|mixed
     */
    public function validateEmail(array $inputs);

    /**
     * Generate Email Verification Code
     *
     * @param array $inputs
     * @return mixed
     */
    public function generateEmailVerificationCode(array $inputs);
}