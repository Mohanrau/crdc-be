<?php
namespace App\Interfaces\Members;

Interface MemberTreeInterface
{
    /**
     * get member's placement tree with given member id and depth
     *
     * @param int $userId
     * @param int $depth
     * @return mixed
     */
    public function getPlacementTree(int $userId, int $depth);

    /**
     * get member's placement tree outer with given member id , depth and outer left or right
     *
     * @param int $userId
     * @param int $depth
     * @param string $outer
     * @return mixed
     */
    public function getPlacementTreeOuter(int $userId, int $depth, string $outer);

    /**
     * get member's sponsor network with given member id and depth
     *
     * @param int $userId
     * @param int $depth
     * @return mixed
     */
    public function getSponsorNetwork(int $userId, int $depth);

    /**
     * get member's placement network with given member id and depth
     *
     * @param int $userId
     * @param int $depth
     * @return mixed
     */
    public function getPlacementNetwork(int $userId, int $depth);

    /**
     * Get all the placement network info
     *
     * @param int $userId
     * @param int $depth
     * @return array
     */
    public function getPlacementNetworkTuned(int $userId, int $depth);

    /**
     * Sponsor network traversal tuned version
     *
     * @param int $userId
     * @param int $depth
     * @return array
     */
    public function getSponsorNetworkTuned(int $userId, int $depth);

    /**
     * verify same member network with given tree type, first user id, and second user id
     *
     * @param string $treeType
     * @param int $firstUserId
     * @param int $secondUserId
     * @return array
     */
    public function verifySameMemberTreeNetwork(string $treeType, int $firstUserId, int $secondUserId);

    /**
     * verify member downline with given tree type, upline member id, and downline member id
     *
     * @param string $treeType
     * @param int $uplineUserId
     * @param int $downlineUserId
     * @return mixed
     */
    public function verifyMemberTreeDownline(string $treeType, int $uplineUserId, int $downlineUserId);

    /**
     * Get all Sponsors descendants
     *
     * @param $userId
     * @return Collection
     */
    public function getAllSponsorDescendant($userId);

    /**
     * Lightweight get all sponsors child user id (unlimited sponsor tree level)
     *
     * @param int $userId
     * @param bool $associateLevel
     * @return array
     */
    public function getAllSponsorChildUserId(int $userId, bool $associateLevel);

    /**
     * Lightweight function to get ibo_id, name, and user_id
     *
     * @param array $option
     * @return mixed
     */
    public function searchSponsorNetwork(Array $option);

    /**
     * validate placement position for the given placement userId and sponsorId
     *
     * @param int $sponsorUserId
     * @param int $placementOldMemberId
     * @param int $placementPosition
     * @return mixed
     */
    public function validatePlacement(int $sponsorUserId, int $placementOldMemberId, int $placementPosition);

    /**
     * Lightweight sponsor network traversal (unlimited sponsor tree level)
     *
     * @param int $userId
     * @param int $depth
     * @return array
     */
    public function getSponsorDownlineListing(int $userId, int $depth);

    /**
     * this will allow the temporary tree table record to be inserted to actual member tree
     *
     * @param $uniqueId
     * @param int $userId
     * @return bool
     */
    public function insertToMemberTreeFromTemp($uniqueId, int $userId);

    /**
     * @param array $information
     * @return array
     */
    public function assignMemberTree(array $information);

    /**
     * Insert enrolment temp tree
     *
     * leave third and forth parameter empty if isAuto
     *
     * @param $uniqueId
     * @param $sponsorUserId
     * @param int $placementUserId
     * @param int $placementPosition
     * @return bool
     */
    public function insertEnrollmentTempTree(
        $uniqueId,
        int $sponsorUserId = null,
        int $placementUserId = null,
        int $placementPosition = null
    );
}