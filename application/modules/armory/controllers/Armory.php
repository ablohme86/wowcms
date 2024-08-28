<?php

// TODO: Fix correct icons and color on items

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property General_model $wowgeneral
 * @property Realm_model   $wowrealm
 * @property Module_model  $wowmodule
 * @property Template      $template
 * @property Armory_model  $armory_model
 */
class Armory extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('armory_model');

        if ($this->wowgeneral->getMaintenance()) {
            redirect(base_url('maintenance'), 'refresh');
        }

        if (! $this->wowmodule->getArmoryStatus()) {
            redirect(base_url(), 'refresh');
        }
    }

    public function index()
    {
        $data = [
            'pagetitle' => 'Armory',
            'lang'      => $this->lang->lang(),
            'realms'    => $this->wowrealm->getRealms()->result(),
        ];
    
        $this->template->build('search', $data);
    }
    public function search()
    {
        $data = [
            'pagetitle' => 'Armory Search',
            'lang'      => $this->lang->lang(),
            'realms'    => $this->wowrealm->getRealms()->result(),
        ];

        $this->template->build('search', $data);
    }



    public function character($realmid, $playerid, int $patch = null)
    {
		//printf("realID from character() is: %d", $realmid);

        if (empty($playerid) && empty($realmid)) {
            redirect(base_url(), 'refresh');
        }

        $currentRealm     = $this->wowrealm->getRealmConnectionData($realmid);
        
        $currentRealmName = $this->wowrealm->getRealmName($realmid);
        $character        = $this->armory_model->getPlayerInfo($currentRealm, $playerid);
        $equippedItemIDs  = [];


        if (! $currentRealm) {
			printf("404");
            redirect(base_url('404'), 'refresh');
        }
        if (! $character) {
			printf("404");
            redirect(base_url('404'), 'refresh');
        }
        if (! empty($patch) && $patch > 10) {
			printf("404");
            redirect(base_url('404'), 'refresh');
        }

        $guildInfo = $this->armory_model->getGuildInfoByPlayerID($currentRealm, $character['guid']);
        $slots     = [
            'L' => ['head', 'neck', 'shoulders', 'back', 'chest', 'shirt', 'tabard', 'wrists'],
            'R' => ['hands', 'waist', 'legs', 'feet', 'finger1', 'finger2', 'trinket1', 'trinket2'],
            'B' => ['mainhand', 'offhand', 'ranged']
        ];

        $raceRes = $this->wowrealm->getCharRace($playerid, $currentRealm);
        //echo "Her er race res: " . $raceRes;
        
        $character['faction']        = $this->wowgeneral->getFaction($raceRes) ?? '';
        $character['guildid']       = $guildInfo['guildid'] ?? '';
        
        $character['guild_name']     = $guildInfo['name'] ?? '<i>Guildless</i>';
        
        $character['race_name']      = $this->wowgeneral->getRaceName($character['race']) ?? '';
        $character['class_name']     = $this->wowgeneral->getClassName($character['class']) ?? '';
        
        $character['equipped_items'] = $this->armory_model->getCharEquipments($currentRealm, $realmid,$playerid, $patch);
		//print_r($character['equipped_items']);
        $character['character_realm_id'] = $realmid;    //A.Blohmé: set which realm this character belongs to for re-directing to correct item in database module

        foreach ($character['equipped_items'] as $items) {
			$equippedItemIDs[] = $items['item'];

        }

        if (! empty($equippedItemIDs)) 
		{
            sort($equippedItemIDs);

            $character['equipped_item_ids']      = $equippedItemIDs;

            $character['equipped_item_model']    = $this->armory_model->getCharEquipDisplayModel($playerid,$realmid, $character['equipped_item_ids'], $character['class'], false, $patch);
            $character['equipped_item_id_model'] = json_encode($this->armory_model->getCharEquipDisplayModel($playerid, $realmid, $character['equipped_item_ids'], $character['class'], true, $patch));
            $character['enchanted_items']        = $this->armory_model->getEnchantInfo($currentRealm, $playerid, $equippedItemIDs);
            
            
            
            // HVIS NOE ER ENCHANTA!
            if ($character['enchanted_items']) 
            {

                $enchantListCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('enchListArrStaticDBC') : false;

                if ($enchantListCache && $development == false) {
                    $enchantDBC = $enchantListCache;
                } 
                else 
                {
                    $this->load->config("shared_dbc_enchants");
                    $enchantDBC = $this->config->item('enchants');

                    if ($this->wowgeneral->getRedisCMS() && $enchantDBC) 
					{
                        // Cache for 30 day
						// A. Blohme, 22.08.24: DISABLED TEMPOARILY TO TEST ITEMS'N SHIT!
                       // $this->cache->redis->save('enchListArrStaticDBC', $enchantDBC, 60 * 60 * 24 * 30);
                    }
                }

                foreach ($character['equipped_items'] as $item) //TODO: get rid of this foreach
                {
                    if (array_key_exists($item['item_id'], $character['enchanted_items'])) 
					{
                        $enchData[$item['item_slot_id']] = ['enchant' => ['playerid' => $character['enchanted_items'][$item['item_id']], 'description' => ($enchantDBC[$character['enchanted_items'][$item['item_id']]] ?? '')]];
                    }
                }
            }
            $character['enchanted_items'] = $enchData ?? [];
        } 
		else 
		{
            $character['equipped_item_ids'] = [];
        }

                //echo "Har me noko her???";

        $character['stats']                = $this->armory_model->calculateAuras($currentRealm, $realmid,$playerid, $character['race'], $character['class'], $character['level'], $equippedItemIDs, $patch);
//		  echo "Stats OK!";
        $character['profession_primary']   = $this->armory_model->getCharProfessions($currentRealm ,$realmid  , $playerid, 'P');
        //echo "Proffs OK!";
		$character['profession_secondary'] = $this->armory_model->getCharProfessions($currentRealm, $realmid,$playerid, 'S');
       // echo "Proffs 2 OK!";
        
        
        // A. Blohme: 22.08.24:
        // This will just return 0 since we dont use pvp rank in TBC
		$character['honor_current_rank']   = $this->armory_model->getCurrentPVPRank($currentRealm,$realmid, $playerid);
    //    echo "HNOR WEEK OK!";
        // A. Blohme: 22.08.24:
        // And this should just show totalKills on TBC since thats most we got, and we must also add a new function to return Arena Rating, etc..!
		$character['honor_total_hk']       = $this->armory_model->getTotalHK($currentRealm,$realmid, $playerid);	// THis aint working in TBC!
      //          echo "WE ARE IN THE CLEAR NOW";

        
		$data = [
            'playerid'               => $playerid,
            'patch'            => $patch ?? '',
            'realmid'          => $realmid,
            'pagetitle'        => 'Character > ' . $character['name'],
            'currentRealm'     => $currentRealm,
            'currentRealmName' => $currentRealmName,
            'slots'            => $slots,
            'character'        => $character,
            'lang'             => $this->lang->lang(),
        ];
		

        $this->template->build('index', $data);
		
		

    }

    public function guild($realmid, $guildid)
    {
        if (empty($guildid)) {
            redirect(base_url(), 'refresh');
        }

        $data = [

            'guildid'   => $guildid,
            'realmid'   => $realmid,
            'pagetitle' => 'Guild Members',
            'lang'      => $this->lang->lang(),
            'realms'    => $this->wowrealm->getRealms()->result(),
        ];

        $this->template->build('guild', $data);
    }
	public function result()
    {
        $data   = [
            'pagetitle' => 'Armory Search',
            'lang'      => $this->lang->lang(),
            'realms'    => $this->wowrealm->getRealms()->result(),
            'search'    => $this->input->get('search'),
            'realm'     => $this->input->get('realm'),
            'chars'     => [],  // Start med tom array for characters
            'guild'     => []   // Start med tom array for guilds
        ];
        $search = $this->input->get('search');
        $realm  = $this->input->get('realm');
        
        if ($realm == "ALL")
        {

            foreach ($data['realms'] as $realm_data)
            {
    
                $MultiRealm = $this->wowrealm->getRealmConnectionData($realm_data->id);
                
                $chars = $this->armory_model->searchChar($MultiRealm, $search)->result_array(); // Returner som array
                $guilds = $this->armory_model->searchGuild($MultiRealm, $search)->result_array(); // Returner som array
              //  print_r($chars);
                // Slå sammen resultatene
                $data['chars'] = array_merge($data['chars'], $chars);
                $data['guild'] = array_merge($data['guild'], $guilds);
            }
        }
        else
        {
            if (! empty($search) && ! empty($realm)) 
            {
                $realmSqlData = $this->wowrealm->getRealmConnectionData($realm);
        
                $data['chars'] = $this->armory_model->searchChar($realmSqlData, $search)->result_array();
                $data['guild'] = $this->armory_model->searchGuild($realmSqlData, $search)->result_array();
            }
		}
        
       // print_r($data);
        $this->template->build('result', $data);
    }

}
