<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

$development = false;

/**
 * @property General_model  $wowgeneral
 * @property Armory_model   $armory_model
 * @property Database_model $Database_model
 */
class Api_v1 extends REST_Controller
{

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        parent::__construct();
        $this->load->model('Database/Database_model');
    }

    /**
     * Hello
     */
    public function index_get()
    {
        $data = 'Ready for action.';
        $this->response([
            'status'  => true,
            'message' => $data
        ], REST_Controller::HTTP_OK);
    }

    // This list can also be installed to own database by downloading it from WoWTools
    public function classic_displayid_get($id = 0)
    {
        $build = '1.14.3.44403';
        if ($id > 0) {
            $classicDisplayCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemClassicDisplayID_' . $id) : false;
            if ($classicDisplayCache && $development == false) {
                $status = REST_Controller::HTTP_OK;
                $data   = ['ItemDisplayInfoID' => (int)$classicDisplayCache];
            } else {
                $appearanceId = json_decode($this->getUrlContents('https://wow.tools/dbc/api/peek/itemmodifiedappearance?build=' . $build . '&col=ItemID&val=' . $id))->values->ItemAppearanceID ?? 0;
                if ($appearanceId > 0) {
                    $displayId = json_decode($this->getUrlContents('https://wow.tools/dbc/api/peek/itemappearance?build=' . $build . '&col=ID&val=' . $appearanceId))->values->ItemDisplayInfoID ?? 0;
                    if ($displayId > 0) {
                        $status = REST_Controller::HTTP_OK;
                        $data   = ['ItemDisplayInfoID' => (int)$displayId];
                        if ($this->wowgeneral->getRedisCMS() && $development == false) {
                            // Cache for 1 day
                            $this->cache->redis->save('itemClassicDisplayID_' . $id, $displayId, 86400);
                        }
                    } else {
                        $status = REST_Controller::HTTP_NOT_FOUND;
                        $data   = [
                            'status'       => $status,
                            'errorMessage' => 'Item display info ID not found.'
                        ];
                    }
                } else {
                    $status = REST_Controller::HTTP_NOT_FOUND;
                    $data   = [
                        'status'       => $status,
                        'errorMessage' => 'Item appearance ID not found.'
                    ];
                }
            }
        } else {
            $status = REST_Controller::HTTP_BAD_REQUEST;
            $data   = [
                'status'       => $status,
                'errorMessage' => $id . ' is not supported for item_id field.'
            ];
        }
        $this->response($data, $status);
    }


	public function realminfo_get(int $realmid)
	{
	   $exp = $this->wowrealm->getRealmInformation($realmid,true);
	   echo json_encode($exp);

	   
	}
	public function charsearch_get( $realmid, $charsearch)
	{

		if ($this->wowrealm->getRealmInformation($realmid) != false) 
		{
		 echo json_encode(	$data_table = $this->Database_model->searchForCharacter($charsearch,$realmid));
		}
		else
		{
			return false;
		}

	}
	
	public function mailman_send_post()
	{	

		   $data = [ 

				'realm_id' => $this->input->post('realm_id'),
				'message' => $this->input->post('message'),
				'playername' => $this->input->post('recipient'),
				'itemlist' => $this->input->post('items'),
				'gold' => $this->input->post('gold'),
				'silver' => $this->input->post('silver'),
				'copper' => $this->input->post('copper')
			];
         
            // Dette nedenfor skal etterhvert flyttes over til en egen module kalt "ingame-services" eller noe iden duren..

			$this->load->model('Database/Database_model');

                     
			$charSearch = $this->Database_model->searchForCharacter($data['playername'],$data['realm_id']);
			if (!$charSearch) 
			{
				header("HTTP/1.1 404 Character does not exist on this realm!");
				return false;
			}
            $command = "send mail sjallabais hello hell";
            $soapConnectionInfo = $this->wowrealm->getRealm($data['realm_id'])->result();
            
           $client = new SoapClient(NULL,
           array(
               "location" => "http://".$soapConnectionInfo[0]->console_hostname.":".$soapConnectionInfo[0]->console_port."/",
               "uri" => "urn:".$soapConnectionInfo[0]->emulator,
               "style" => SOAP_RPC,
               'login' => $soapConnectionInfo[0]->console_username,
               'password' => $soapConnectionInfo[0]->console_password
           ));
           
           try {
               $result = $client->executeCommand(new SoapParam($command, "command"));
           
               echo $result;
               return;
           }
           catch (Exception $e)
           {
            header("HTTP/1.1 404 Cannot connect to realm: ".$e->getMessage());
               
           }
            
			// Lets do a SOAP check! (Dont drop it!)
		}


	// for the rest API item get
    public function item_get(int $id, $realmid,int $patch = 10)
    {

        $this->load->config("shared_dbc");

        if ($id <= 0) {
            $status = REST_Controller::HTTP_BAD_REQUEST;
            $data   = [
                'status'       => $status,
                'errorMessage' => $id . ' is not supported for item_id field.'
            ];
        } elseif ($patch < 0 || $patch > 10) {
            $status = REST_Controller::HTTP_BAD_REQUEST;
            $data   = [
                'status'       => $status,
                'errorMessage' => 'Patch ' . $patch . ' is not supported for patch field.'
            ];
        } else {
            $item = $this->Database_model->getItem($id, $realmid);

            if ($item) {
                $item_info = [];

                $item_info['id']          = $item['entry'];
                $item_info['name']        = $item['name'];
                $item_info['description'] = $item['description'] === '' ? null : $item['description'];
                $item_info['realm']       = $realmid;
                $item_info['icon']        = $this->Database_model->getIconName($id,$realmid, $patch);
                $item_info['quality']     = ['id' => $item['Quality'], 'name' => itemQuality($item['Quality'])];
                $item_info['flags']       = $item['flags'];

                $item_info['buy_count']  = $item['BuyCount'];
                $item_info['buy_price']  = $item['BuyPrice'];
                $item_info['sell_price'] = $item['SellPrice'];


                if (! empty($item['maxcount']) && strlen($item['maxcount']) > 0) {
                    $item_info['maxcount'] = itemCount($item['maxcount']);
                }

                $item_info['item_class']    = ['id' => $item['class']];
                $item_info['item_subclass'] = ['id' => $item['subclass']];

                if (in_array($item['class'], [2, 4, 6])) {
                    $item_info['item_class']['name']    = itemClass($item['class']);
                    $item_info['item_subclass']['name'] = itemSubClass($item['class'], $item['subclass']);
                }

                $inv_type                   = itemInventory($item['InventoryType']);
                $item_info['item_inv_type'] = ['id' => $item['InventoryType'], 'name' => $inv_type ?: null];

                if (isWeapon($item['class'])) {
                    $dmg_min = $dmg_max = 0;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($item['dmg_min' . $i] <= 0 || $item['dmg_max' . $i] <= 0) {
                            continue;
                        }

                        $item_info['weapon_damage_list'][$i] = ['min' => $item['dmg_min' . $i], 'max' => $item['dmg_max' . $i], 'type' => $item['dmg_type' . $i]];

                        $dmg_min += $item['dmg_min' . $i];
                        $dmg_max += $item['dmg_max' . $i];
                    }
                    $dps                     = number_format(($dmg_min + $dmg_max) / (2 * ($item['delay'] / 1000)), 1);
                    $item_info['weapon_dps'] = $dps ?? null;
                }

                $item_info['range_mod'] = $item['RangedModRange'];
                $item_info['ammo_type'] = $item['ammo_type'];

                if ($item['armor']) {
                    $item_info['item_armor'] = $item['armor'];
                }

                if ($item['block']) {
                    $item_info['item_block'] = $item['block'];
                }

                for ($j = 1; $j <= 10; $j++) {
                    if ($item['stat_type' . $j] < 0 || ! $item['stat_value' . $j]) { //val can be negative
                        continue;
                    }

                    $item_info['stat_list'][$j] = ['stat_id' => $item['stat_type' . $j], 'value' => $item['stat_value' . $j]];
                }

                $resistance_list = [
                    'holy_res',
                    'fire_res',
                    'nature_res',
                    'frost_res',
                    'shadow_res',
                    'arcane_res'
                ];

                foreach ($resistance_list as $resistance) {
                    if ($resistance && $item[$resistance] != 0) {
                        $item_info['resistance_list'][$resistance] = $item[$resistance];
                    }
                }

                if ($item['RandomProperty']) {
                    $item_info['random_ench'] = '&lt;Random Enchantment&gt';
                }

                if ($item['MaxDurability']) {
                    $item_info['durability'] = $item['MaxDurability'];
                }

                if ($item['RequiredLevel'] > 1) {
                    $item_info['req_level'] = $item['RequiredLevel'];
                }

                if (in_array($item['class'], [2, 4])) {
                    $item_info['item_level'] = $item['item_level'];
                }

                if ($item['requiredspell'] > 0) {
                    $item_info['required_spell'] = $item['requiredspell'];
                }

                if ($item['RequiredSkill'] > 0) {
                    $item_info['required_skill'] = ['skill' => $item['RequiredSkill'], 'rank' => ['RequiredSkillRank']];
                }

                $allowedClass = getAllowableClass($item['AllowableClass'], false);
                if ($item['AllowableClass'] > 0 && $allowedClass) {
                    $item_info['allowed_class_list'] = $allowedClass;
                }

                $allowedRace = getAllowableRace($item['AllowableRace']);
                if ($item['AllowableRace'] > 0 && $allowedRace) {
                    $item_info['allowed_race_list'] = $allowedRace;
                }

                // 24 aug 24, ALEXANDER BLOHMÈ: - MAKE TBC FIXES LATER FOR THIS HONOR SYSTEM
                if ($item['requiredhonorrank']) {
                    $item_info['required_honor'] = $item['requiredhonorrank'];
                }

                if ($item['RequiredReputationFaction']) {
                    $item_info['required_rep'] = ['faction' => $item['RequiredReputationFaction'], 'rank' => $item['RequiredReputationRank']];
                }

                $itemSpellsAndTrigger = [];
                for ($k = 1; $k <= 5; $k++) {
                    if ($item['spellid_' . $k] == 0) {
                        continue;
                    }

                    $cd = $item['spellcooldown_' . $k];
                    if ($cd < $item['spellcategorycooldown_' . $k]) {
                        $cd = $item['spellcategorycooldown_' . $k];
                    }

                    $extra = [];
                    if ($cd >= 5000) {
                        $extra[] = sprintf('%s cooldown', formatTime($cd, true));
                    }
                    if ($item['spelltrigger_' . $k] == 2) {
                        if ($ppm = $item['spellppmrate_' . $k]) {
                            $extra[] = sprintf('%s procs per minute', $ppm);
                        }
                    }

                    $itemSpellsAndTrigger[$item['spellid_' . $k]] = [$item['spelltrigger_' . $k], $extra ? ' (' . implode(', ', $extra) . ')' : ''];
                }

                if ($itemSpellsAndTrigger) {
                    $itemSpells = array_keys($itemSpellsAndTrigger);

                    foreach ($itemSpells as $sid) {
                        $output[] = $this->config->item('trigger')[$itemSpellsAndTrigger[$sid][0]] . $this->Database_model->getSpellDetails($sid, $realmid,$patch) . $itemSpellsAndTrigger[$sid][1];
                    }
                    $item_info['spell_list'] = $output ?? [];
                }

                // Item Set
                if ($item['itemset'] && $this->config->item('item_set')[$item['itemset']]) {
                    $item_info['item_set']['name'] = $this->config->item('item_set')[$item['itemset']][0];

                    for ($i = 0; $i < 10; $i++) {
                        if ($this->config->item('item_set')[$item['itemset']][1][$i]) {
                            $item_info['item_set']['item_list'][$this->config->item('item_set')[$item['itemset']][1][$i]]
                                = $this->Database_model->getItemName($this->config->item('item_set')[$item['itemset']][1][$i],$realmid, $patch);
                        }
                    }

                    $set_key   = [];
                    $set_spell = [];
                    for ($j = 0; $j < 8; $j++) {
                        if ($this->config->item('item_set')[$item['itemset']][2][$j]) {
                            $set_spell[] = $this->Database_model->getSpellDetails($this->config->item('item_set')[$item['itemset']][2][$j],$realmid);
                        }

                        if ($this->config->item('item_set')[$item['itemset']][3][$j]) {
                            $set_key[] = $this->config->item('item_set')[$item['itemset']][3][$j];
                        }
                    }

                    $tmp = array_combine($set_key, $set_spell);
                    unset($i, $j, $set_key, $set_spell);
                    ksort($tmp);

                    if ($tmp) {
                        foreach ($tmp as $sid => $spell) {
                            $item_info['item_set']['set_list'][$sid] = $spell;
                        }
                    }
                    unset($tmp);
                }

                $item_info['start_quest'] = $item['startquest'];

                // Convert string - int
                $item_info = json_encode($item_info, JSON_NUMERIC_CHECK);
                $item_info = json_decode($item_info);

                $status = REST_Controller::HTTP_OK;
                $data   = $item_info;
            } 
			else 
			{
                $status = REST_Controller::HTTP_NOT_FOUND;
                $data   = [
                    'status'       => $status,
                    'errorMessage' => 'Item not found. Item ' . $id . ' does not exists in the database or not implemented in Patch (item_get)' . getPatchName($patch)
                ];
            }
        }
        $this->response($data, $status);
    }





	// Used by database's item.php and amory index.php, in general: on mouse over an item popup!
    public function tooltip_item_get(int $id, $realmid,int $patch = 10)
    {
        $this->load->config("shared_dbc");

        if ($id <= 0) {
            $status = REST_Controller::HTTP_BAD_REQUEST;
            $data   = [
                'status'       => $status,
                'errorMessage' => $id . ' is not supported for item_id field.'
            ];
        } elseif ($patch < 0 || $patch > 10) {
            $status = REST_Controller::HTTP_BAD_REQUEST;
            $data   = [
                'status'       => $status,
                'errorMessage' => 'Patch ' . $patch . ' is not supported for patch field.'
            ];
        } 
		else 
		{
            $itemTooltipCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemTooltipID_' . $id . '-P_10') : false;

            if ($itemTooltipCache && $development == false) 
			{
                $status = REST_Controller::HTTP_OK;
                $data   = $itemTooltipCache;
            } 
			else 
			{
                $item = $this->Database_model->getItem($id, $realmid,$patch);

                if ($item) {
                    $item_info = [];

                    $item_info['id']      = $item['entry'];
                    $item_info['type']    = 'item';
                    $item_info['name']    = $item['name'];
                    $item_info['icon']    = $this->Database_model->getIconName($id,$realmid, $patch);
                    $item_info['quality'] = itemQuality($item['Quality']);

                    $item_info['tooltip'] = '<div class="yesilcms-dyn" style="max-width:20rem;">';
                    $item_info['tooltip'] .= '<span class="q' . $item['Quality'] . '" style="font-size: 16px">' . $item['name'] . '</span><br />';

                    if (in_array($item['class'], [2, 4])) {
                        $item_info['tooltip'] .= '<span class="q">' . sprintf('Item Level %d', $item['ItemLevel']) . '</span><br />';
                    }

                    if ($item['bonding']) {
                        $item_info['tooltip'] .= itemBonding($item['bonding']) . '<br />';
                    }

                    if (! empty($item['maxcount']) && strlen($item['maxcount']) > 0) {
                        $item_info['tooltip'] .= itemCount($item['maxcount'] ?? '') . '<br />';
                    }

                    $inv_type             = itemInventory($item['InventoryType']);
                    $item_info['tooltip'] .= $inv_type ? '<div style="float:left;">' . $inv_type . '</div>' : '';

                    if (in_array($item['class'], [2, 4, 6])) {
                        if ($item['class'] == 2 && $item['subclass'] > 0) {
                            $item_info['tooltip'] .= '<div style="float:right;">' . itemSubClass($item['class'], $item['subclass']) . '</div>';
                        }
                        $item_info['tooltip'] .= '<div style="clear:both;"></div>';
                    }

                    if ($item['armor']) {
                        $item_info['tooltip'] .= $item['armor'] . ' Armor<br />';
                    }

                    if ($item['block']) {
                        $item_info['tooltip'] .= $item['block'] . ' Block<br />';
                    }

                    if (isWeapon($item['class'])) {
                        $dmg_min = $dmg_max = 0;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($item['dmg_min' . $i] <= 0 || $item['dmg_max' . $i] <= 0) {
                                continue;
                            }

                            $dmg_list[] = weaponDamage($item['dmg_min' . $i], $item['dmg_max' . $i], $item['dmg_type' . $i], $i);

                            $dmg_min += $item['dmg_min' . $i];
                            $dmg_max += $item['dmg_max' . $i];
                        }
                        $dps = weaponDPS($dmg_min, $dmg_max, $item['delay']);

                        foreach ($dmg_list as $key => $dmg) {
                            if ($key === 0) {
                                $item_info['tooltip'] .= '<div style="float:left;">' . $dmg . '</div>';
                                $item_info['tooltip'] .= '<div style="float:right;margin-left:15px;">Speed ' . number_format($item['delay'] / 1000, 2) . '</div><br />';
                            } else {
                                $item_info['tooltip'] .= $dmg . '<br />';
                            }
                        }
                        $item_info['tooltip'] .= '(' . $dps . ')<br />';
                    }

                    for ($j = 1; $j <= 10; $j++) {
                        if ($item['stat_type' . $j] < 0 || ! $item['stat_value' . $j]) { //val can be negative
                            continue;
                        }

                        $item_info['tooltip'] .= itemStat($item['stat_type' . $j], $item['stat_value' . $j]) . '<br />';
                    }

                    $resistance_list = [
                        'holy_res',
                        'fire_res',
                        'nature_res',
                        'frost_res',
                        'shadow_res',
                        'arcane_res'
                    ];

                    foreach ($resistance_list as $key => $resistance) {
                        if ($resistance && $item[$resistance] != 0) {
                            $item_info['tooltip'] .= itemResistance($item[$resistance], $key) . '<br />';
                        }
                    }

                    if ($item['RandomProperty']) {
                        $item_info['tooltip'] .= '<span class="q2">&lt;Random Enchantment&gt;</span><br/>';
                    }

                    $item_info['tooltip'] .= '<div class="q2" id="tooltip-item-enchantments"></div>';

                    if ($item['MaxDurability']) {
                        $item_info['tooltip'] .= sprintf('Durability %d / %d', $item['MaxDurability'], $item['MaxDurability']) . '<br />';
                    }

                    if ($item['RequiredLevel'] > 1) {
                        $item_info['tooltip'] .= sprintf('Requires Level: %d', $item['RequiredLevel']) . '<br />';
                    }

                    if ($item['requiredspell'] > 0) {
                        $item_info['tooltip'] .= 'Requires ' . $this->Database_model->getReqSpellName($item['requiredspell'],$realmid, $patch) . '<br />';
                    }

			// search global for these: required_skill_rank, required_skill
                    if ($item['RequiredSkill'] > 0) {
                        $item_info['tooltip'] .= requiredSkill($item['RequiredSkill'], $item['RequiredSkillRank']) . '<br />';
                    }

                    $allowedClass = getAllowableClass($item['AllowableClass']);
                    if ($item['AllowableClass'] > 0 && $allowedClass) {
                        $item_info['tooltip'] .= 'Classes: ' . $allowedClass . '<br />';
                    }

                    $allowedRace = getAllowableRace($item['AllowableRace']);
                    if ($item['AllowableRace'] > 0 && $allowedRace) {
                        $item_info['tooltip'] .= 'Races: ' . $allowedRace . '<br />';
                    }

                    if ($item['requiredhonorrank']) {
                        $item_info['tooltip'] .= '<span class="q10">Requires ' . getRankByFaction($item['name'], $item['requiredhonorrank']) . '</span><br />';
                    }

                    if ($item['RequiredReputationFaction']) {
                        $item_info['tooltip'] .= '<span class="q10">Requires ' . $this->Database_model->getFactionName($item['RequiredReputationFaction']) .
                                                 ' - ' . getRepRank($item['RequiredReputationRank']) . '</span><br />';
                    }

                    $itemSpellsAndTrigger = [];
                    for ($k = 1; $k <= 5; $k++) {
                        if ($item['spellid_' . $k] == 0) {
                            continue;
                        }

                        $cd = $item['spellcooldown_' . $k];
                        if ($cd < $item['spellcategorycooldown_' . $k]) {
                            $cd = $item['spellcategorycooldown_' . $k];
                        }

                        $extra = [];
                        if ($cd >= 5000) {
                            $extra[] = sprintf('%s cooldown', formatTime($cd, true));
                        }
                        if ($item['spelltrigger_' . $k] == 2) {
                            if ($ppm = $item['spellppmrate_' . $k]) {
                                $extra[] = sprintf('%s procs per minute', $ppm);
                            }
                        }

                        $itemSpellsAndTrigger[$item['spellid_' . $k]] = [$item['spelltrigger_' . $k], $extra ? ' (' . implode(', ', $extra) . ')' : ''];
                    }

                    if ($itemSpellsAndTrigger) {
                        $itemSpells = array_keys($itemSpellsAndTrigger);

                        foreach ($itemSpells as $sid) {
                            $item_info['tooltip'] .= '<a class="q2" href="' . base_url() . '/spell/' . $sid . '/' . $realmid . '" target="_blank">' . $this->config->item('trigger')[$itemSpellsAndTrigger[$sid][0]] . $this->Database_model->getSpellDetails($sid, $realmid,$patch) . $itemSpellsAndTrigger[$sid][1] . '</a><br />';
                        }
                    }

                    // Item Set
                    if ($item['itemset'] && $this->config->item('item_set')[$item['itemset']]) 
					{

                        $itemset_name = $this->config->item('item_set')[$item['itemset']][0];

                        for ($i = 0; $i < 10; $i++) 
						{
                            if ($this->config->item('item_set')[$item['itemset']][1][$i]) 
							{
                                $itemset_item_list[$this->config->item('item_set')[$item['itemset']][1][$i]]
                                    = $this->Database_model->getItemName($this->config->item('item_set')[$item['itemset']][1][$i],$realmid, $patch);
                            }
                        }

                        $set_key   = [];
                        $set_spell = [];
                        for ($j = 0; $j < 8; $j++) {
                            if ($this->config->item('item_set')[$item['itemset']][2][$j]) {
                                $set_spell[] = $this->Database_model->getSpellDetails($this->config->item('item_set')[$item['itemset']][2][$j],$realmid);
                            }

                            if ($this->config->item('item_set')[$item['itemset']][3][$j]) {
                                $set_key[] = $this->config->item('item_set')[$item['itemset']][3][$j];
                            }
                        }

                        $tmp = array_combine($set_key, $set_spell);
                        unset($i, $j, $set_key, $set_spell);
                        ksort($tmp);

                        if ($tmp) {
                            foreach ($tmp as $sid => $spell) {
                                $itemset_set_list[$sid] = $spell;
                            }
                        }
                        unset($tmp);

                        $item_info['tooltip'] .= '<div id="tooltip-item-set" style="padding-top: 10px;">';
                        $item_info['tooltip'] .= '<div class="q" id="tooltip-item-set-name">' . $itemset_name . ' (<span id="tooltip-item-set-count">0</span>/' . count($itemset_item_list) . ')</div>';
                        $item_info['tooltip'] .= '<div id="tooltip-item-set-pieces" style="padding-left: .6em">';
                        $item_info['tooltip'] .= '<div class="q0 indent">';
                        foreach ($itemset_item_list as $item_id => $piece) {
                            $item_info['tooltip'] .= '<span class="item-set-piece" data-itemset-item-entry="' . $item_id . '" data-possible-entries="' . $item_id . '">' . $piece . '</span> <br/>';
                        }
                        $item_info['tooltip'] .= '</div></div><div id="tooltip-item-set-bonuses" style="padding-top: 10px;"><div class="q0">';
                        

						foreach ($itemset_set_list as $threshold => $set) 
						{

							$item_info['tooltip'] .= '<span class="item-set-bonus" data-bonus-required-items="' . $threshold . '">(' . $threshold . ') Set: <span id="set-bonus-text">' . $set . '</span></span> <br/>';
                        }
                        $item_info['tooltip'] .= '</div></div></div>';
                    }
					else
					{
						file_put_contents("/home/wowragnaros/DEBUG_CMS","No item set found!\n", FILE_APPEND);
					}

                    if ($item['description'] !== '') {
                        $item_info['tooltip'] .= '<span class="q">JAU:"' . $item['description'] . '"</span>';
                    }

                    $item_info['tooltip'] .= '</div>';

                    $status = REST_Controller::HTTP_OK;
                    $data   = $item_info;

                    if ($this->wowgeneral->getRedisCMS() && $item_info['tooltip'] && $development == false) {
                        // Cache for 30 day
                        $this->cache->redis->save('itemTooltipID_' . $id . '-P_10', $data, 60 * 60 * 24 * 30);
                    }
                }
				else 
				{
                    $status = REST_Controller::HTTP_NOT_FOUND;
                    $data   = [
                        'status'       => $status,
                        'errorMessage' => 'Item not found. Item ' . $id . ' does not exists in the database or not implemented in Patch (tooltip_item_get) '
                    ];
                }
            }
        }
        $this->response($data, $status);
    }



    // 25.AUG.24: FORTSETT HER I MORGEN DEN 26 AUG OG SJEKK HVORFOR VI FÅR 500 HER FRA RESULT.PHP I DATABASE (ER MEST SANSYNLIG AT D ER DENNE SOM KALLES PÅ !!)
    public function tooltip_spell_get(int $id = 0, $realmid,int $patch = 10)
    {
        $this->load->config("shared_dbc");
        $this->load->model("Armory/armory_model");

        if ($id <= 0) {
            $status = REST_Controller::HTTP_BAD_REQUEST;
            $data   = [
                'status'       => $status,
                'errorMessage' => $id . ' is not supported for spell_id field.'
            ];
        } elseif ($patch < 0 || $patch > 10) {
            $status = REST_Controller::HTTP_BAD_REQUEST;
            $data   = [
                'status'       => $status,
                'errorMessage' => 'Patch ' . $patch . ' is not supported for patch field.'
            ];
        } 
        else 
        {
            $spellTooltipCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('spellTooltipID' . $id . '-P_10') : false;

            if ($spellTooltipCache && $development == false) 
            {
                $status = REST_Controller::HTTP_OK;
                $data   = $spellTooltipCache;
            } 
            else 
            {
                
                $spell = $this->Database_model->getSpell($id, $realmid,$patch);
                
                if ($spell) 
                {
                    $spell_info = [];

                    $spell_info['id']   = $spell['Id'];
                    $spell_info['type'] = 'spell';
                    $spell_info['name'] = $spell['SpellName'];
                    $spell_info['icon'] = $this->config->item('spell_icons')[$spell['SpellIconID']] ?? 'Trade_Engineering';
                    
                    
                    $spell['cost']      = spellPowerCost($spell['PowerType'], $spell['ManaCost'], $spell['ManaCostPerlevel'], $spell['ManaPerSecond']);
                    $spell['range']     = spellRange($this->config->item('range_index')[$spell['RangeIndex']]);
                    
                    $spell['cast']      = spellCastTime($this->config->item('cast_index')[$spell['CastingTimeIndex']], $spell['AttributesEx'], $spell['PowerType']);
                    $spell['cooldown']  = spellCD($spell['RecoveryTime']);
                    $spell['desc']      = $this->Database_model->getSpellDetails($spell['Id'],$realmid, $patch);

                    //Tools
                    $spell['tools'] = [];
                    for ($i = 1; $i <= 2; $i++) 
                    {
                        // Tools
                        if (! $spell['totem' . $i]) 
                        {
                            continue;
                        }

                        $spell['tools'][$i - 1] = [
                            'id'      => $spell['Totem' . $i],
                            'name'    => $this->Database_model->getItemName($spell['Totem' . $i], $realmid,$patch),
                            'icon'    => $this->Database_model->getIconName($spell['Totem' . $i], $realmid,$patch),
                            'quality' => $this->armory_model->getCharEquipmentQualityPatch($spell['Totem' . $i], $realmid,$patch)['Quality'] //$this->relItems->getField('quality')
                        ];
                    }

                    //Reagents
                    $spell['reagents'] = [];

                    for ($i = 1; $i <= 8; $i++) {
                        if ($spell['reagent' . $i] > 0 && $spell['ReagentCount' . $i]) 
                        {
                            //$spell['reagents'][$spell['reagent' . $i]] = [$spell['reagent' . $i], $spell['reagentCount' . $i]];

                            $spell['reagents'][$i - 1] = [
                                'id'      => $spell['Reagent' . $i],
                                'count'   => $spell['ReagentCount' . $i],
                                'name'    => $this->Database_model->getItemName($spell['Reagent' . $i], $realmid,$patch),
                                'icon'    => $this->Database_model->getIconName($spell['Reagent' . $i], $realmid,$patch),
                                'quality' => $this->armory_model->getCharEquipmentQualityPatch($spell['Reagent' . $i],$realmid, $patch)['Quality'] //$this->relItems->getField('quality')
                            ];
                        }
                    }

                    $spell_info['tooltip'] = '<div class="yesilcms-dyn" style="max-width:20rem;"><table><tr><td>';

                    if ($spell['nameSubtext']) {
                        $spell_info['tooltip'] .= '<table width="100%"><tr><td><b>' . $spell['SpellName'] . '</b></td><th style="float:right"><b class="q0">' . $spell['nameSubtext'] . '</b></th></tr></table>';
                    } else {
                        $spell_info['tooltip'] .= '<b>' . $spell['SpellName'] . '</b><br/>';
                    }

                    if ($spell['cost'] && $spell['range']) {
                        $spell_info['tooltip'] .= '<table width="100%"><tr><td>' . $spell['cost'] . '</td><th style="float:right">' . $spell['range'] . '</th></tr></table>';
                    } elseif ($spell['cost'] || $spell['range']) {
                        $spell_info['tooltip'] .= $spell['range'] . $spell['cost'];
                    }
                    if (($spell['cost'] xor $spell['range']) && ($spell['cast'] xor $spell['cooldown'])) {
                        $spell_info['tooltip'] .= '<br/>';
                    }
                    if ($spell['cast'] && $spell['cooldown']) {
                        $spell_info['tooltip'] .= '<table width="100%"><tr><td>' . $spell['cast'] . '</td><th style="float:right">' . $spell['cooldown'] . '</th></tr></table>';
                    } else {
                        $spell_info['tooltip'] .= $spell['cast'] . $spell['cooldown'];
                    }

                    if ($spell['tools'] || $spell['reagents']) {
                        $spell_info['tooltip'] .= '<table><tr><td>';

                        if ($spell['tools']) {
                            $spell_info['tooltip'] .= 'Tools: <br/><div class="indent q1">';
                            $numItems              = count($spell['tools']);
                            $i                     = 0;
                            foreach ($spell['tools'] as $t) {
                                if (isset($t['id'])) {
                                    $spell_info['tooltip'] .= '<a href="' . base_url() . '/item/' . $t['id'] . '/' . $realmid . '"><span class="q' . $t['quality'] . '">' . $t['name'] . '</span></a>';
                                } else {
                                    $spell_info['tooltip'] .= $t['name'];
                                }
                                $spell_info['tooltip'] .= empty(++$i !== $numItems) ? '<br />' : ', ';
                            }
                            $spell_info['tooltip'] .= '</div><br/>';
                        }
                        if ($spell['reagents']) {
                            $spell_info['tooltip'] .= 'Reagents: <br/><div class="indent q1">';
                            $numItems              = count($spell['reagents']);
                            $i                     = 0;
                            foreach ($spell['reagents'] as $r) {
                                $spell_info['tooltip'] .= '<a href="' . base_url() . '/item/' . $r['id'] . '/' . $realmid . '"><span class="q' . $r['quality'] . '">' . $r['name'] . '</span></a>';
                                if ($r['count'] > 1) {
                                    $spell_info['tooltip'] .= '(' . $r['count'] . ')';
                                }
                                $spell_info['tooltip'] .= empty(++$i !== $numItems) ? '<br />' : ', ';
                            }
                            $spell_info['tooltip'] .= '</div></td></tr></table>';
                        }
                    }

                    if ($spell['desc']) {
                        $spell_info['tooltip'] .= '<table><tr><td><span class="q">' . $spell['desc'] . '</span><br/></td></tr></table>';
                    }

                    $spell_info['tooltip'] .= '</td></tr></table></div>';

                    $status = REST_Controller::HTTP_OK;
                    $data   = str_replace(array("\n", "\r"), '', $spell_info); //get rid of nl2br

                    if ($this->wowgeneral->getRedisCMS() && $spell_info['tooltip']) {
                        // Cache for 30 day
                        $this->cache->redis->save('spellTooltipID' . $id . '-P_10' , $data, 60 * 60 * 24 * 30);
                    }
                } else {
                    $status = REST_Controller::HTTP_NOT_FOUND;
                    $data   = [
                        'status'       => $status,
                        'errorMessage' => 'Spell not found. Spell ' . $id . ' does not exists in the database or not implemented in Patch (tooltip_spell_get)' . getPatchName($patch)
                    ];
                }
            }
        }
        $this->response($data, $status);
    }

    public function search_db_post()
    {
        $this->load->config("shared_dbc"); // for icons
        $search       = $this->input->post('q');
        $realmid = $this->input->post('realm');
        $search_item  = [];
        $search_spell = [];

        if (isset($search) && strlen($search) >= 3 && ! preg_match("/[^A-Za-z0-9 '&,._-]/", $search)) {
            $search_item  = $this->Database_model->searchItem($search,$realmid, 10, 10);
            $search_spell = $this->Database_model->searchSpell($search, $realmid,10, 10);

            $status = REST_Controller::HTTP_OK;
            if ($search_item || $search_spell) {
                $data['result'] = array_merge($search_item, $search_spell);
            } else {
                $data['result'] = [];
            }
          //  $data['token'] = $this->security->get_csrf_hash();
        } else {
            $status = REST_Controller::HTTP_BAD_REQUEST;
            $data   = [
                'status'       => $status,
                'errorMessage' => $search . ' has illegal search characters (POST Search is: ' + $search,
                'token'        => $this->security->get_csrf_hash()
            ];
        }
        $this->response($data, $status);
    }

   public function search_db_itemsonly_get()
    {
        $this->load->config("shared_dbc"); // for icons
        $search       = $this->input->get('q');
        $realmid = $this->input->get('realm');
        $search_item  = [];
        $search_spell = [];
        
        if (isset($search) && strlen($search) >= 3 && ! preg_match("/[^A-Za-z0-9 '&,._-]/", $search)) 
        {
            $search_item  = $this->Database_model->searchItem($search,$realmid, 10, 10);
           // $search_spell = $this->Database_model->searchSpell($search, $realmid,10, 10);
           // die("Searching db....");
            $status = REST_Controller::HTTP_OK;
            if ($search_item || $search_spell) {
                $data['result'] = array_merge($search_item, $search_spell);
            } 
            else 
            {
                $data['result'] = [];
            }
            //$data['token'] = $this->security->get_csrf_hash();
        } 
        else 
        {
            $status = REST_Controller::HTTP_BAD_REQUEST;
            $data   = [
                'status'       => $status,
                'errorMessage' => $search . ' has illegal search characters. GET Search is: ' . $search,
                'token'        => $this->security->get_csrf_hash()
            ];
        }
       // echo "q is: " . $search . " and realm is: " . $realmid;

        $this->response($data, $status);
    }



    // Need to use this instead of file_get_contents thanks to OpenSSL bug (0A000126:SSL)
    private function getUrlContents($url)
    {
        if (! function_exists('curl_init')) {
            die('CURL is not installed!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}
