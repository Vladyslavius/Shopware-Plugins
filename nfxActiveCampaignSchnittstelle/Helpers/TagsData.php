<?php

namespace nfxActiveCampaignSchnittstelle\Helpers;

use nfxActiveCampaignSchnittstelle\ApiAuthentication\ApiAuthentication;


class TagsData
{

    //Get all tags on system
    public function getAll()
    {
        $apiAuthentication = new ApiAuthentication();
        $data = $apiAuthentication->get(array(), 'tags?limit=' . PHP_INT_MAX);
        $data = json_decode($data, true);
        $tags = array();
        foreach ($data['tags'] as $tag) {
            if (!empty($tag['tag']) && isset($tag['tag'])) {
                $tags[$tag['tag']] = $tag;
            }
        }
        return $tags;
    }

    /** @author NFX @commentary Not neccessary */
//
//    public function getOne()
//    {
//        $apiAuthentication = new ApiAuthentication();
//        $data = $apiAuthentication->get(array(), 'tags/' . $id);
//        $data = json_decode($data, true);
//        return $data['tag'];
//    }

    public function getCustomer($id)
    {
        $apiAuthentication = new ApiAuthentication();
        $data = $apiAuthentication->get(array(), 'contacts/' . $id . '/contactTags');
        $data = json_decode($data, true);
        $tags = array();
        foreach ($data['contactTags'] as $tag) {
            if (!empty($tag['tag']) && isset($tag['tag'])) {
                $tags[$tag['tag']] = $tag;
            }
        }
        return $tags;
    }

}