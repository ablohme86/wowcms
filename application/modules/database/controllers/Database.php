<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property General_model  $wowgeneral
 * @property Realm_model    $wowrealm
 * @property Module_model   $wowmodule
 * @property Template       $template
 * @property Armory_model   $armory_model
 * @property Database_model $Database_model
 */
class Database extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Database_model');

        if ($this->wowgeneral->getMaintenance()) {
            redirect(base_url('maintenance'), 'refresh');
        }

        if (! $this->wowmodule->getArmoryStatus()) {
            redirect(base_url(), 'refresh');
        }
    }

    public function index()
    {
        $this->load->helper('cookie');
        $patch = 1;

        if ($this->input->method() == 'post' && $this->input->post('glorealmid') >= 0 && $this->input->post('glorealmid') <= 10) {
            set_cookie("glob_realmid", $this->input->post('glorealmid'), 86400);
            //$_COOKIE['glob_patch'] = $this->input->post('globpatch'); // -- Keep it here, just incase.
            redirect($this->uri->uri_string());
        }

        if (get_cookie('glob_patch') !== null && get_cookie('glob_patch') >= 0 && get_cookie('glob_patch') <= 10) {
            $patch = (int)get_cookie('glob_patch');
        }

        $data = [
            'pagetitle' => 'YesilCMS Database',
            'lang'      => $this->lang->lang(),
            'realmid'     => $patch
        ];

        $this->template->build('index', $data);
    }

    public function result()
    {
        $this->load->config("shared_dbc");
        $this->load->helper('cookie');
        $patch = 10;



        // Need to set here too to keep query strings while we are changing the global patch.
        if ($this->input->method() == 'post' && $this->input->post('glorealmid') >= 0 && $this->input->post('glorealmid') <= 10) {
            set_cookie("glob_realmid", $this->input->post('glorealmid'), 86400);
            redirect($this->uri->uri_string() . ($_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : ''));
        }

        if (get_cookie('glob_realmid') !== null && get_cookie('glob_realmid') >= 0 && get_cookie('glob_realmid') <= 10) {
            $patch = (int)get_cookie('glob_realmid');
        }
		$realm = $this->input->get('realm');

        $data = [
            'pagetitle' => 'Armory Search',
            'lang'      => $this->lang->lang(),
            'realms'    => $this->wowrealm->getRealms()->result(),
            'patch'     => $patch,
            'search'    => $this->input->get('search'),
            'items'     => [],
            'spells'    => []
        ];

        $search = $this->input->get('search');

        if (! empty($search) && strlen($search) >= 3) 
		{
			//echo "SEARCHING.... SEARCHING...";
            $data['items']  = $this->Database_model->searchItem($search, $realm,$patch);


            $data['spells'] = $this->Database_model->searchSpell($search,$realm, $patch);

		}

        $this->template->build('result', $data);
    }

    public function item(int $entry, int $realmid)
    {

        if ($entry <= 0) 
		{
            redirect(base_url(), 'refresh');
        }

        if (! empty($realmid) && $realmid > 10) {
            redirect(base_url('404'), 'refresh');
        }

        $item = $this->Database_model->getItem($entry, $realmid);
        $this->load->config("shared_dbc");
        if ($item) {
            // Additional data - start
            $item['icon'] = $this->Database_model->getIconName($entry, $realmid);
            
            if (in_array($item['class'], [2, 4, 6])) {
                $item['iclass']    = itemClass($item['class']) ?? '';
                $item['isubclass'] = itemSubClass($item['class'], $item['subclass']) ?? '';
            }

            $inv_type         = itemInventory($item['InventoryType']);
            $item['inv_type'] = $inv_type ?: '';

            if ($item['requiredspell'] > 0) {
                $item['req_spell'] = $this->Database_model->getReqSpellName($item['requiredspell'], $realmid);
            }

            if ($item['RequiredReputationFaction']) {
                $item['req_fact_rep'] = $this->Database_model->getFactionName($item['RequiredReputationFaction'], $realmid);
            }

            if (isWeapon($item['class'])) {
                $dmg_min = $dmg_max = 0;
                for ($i = 1; $i <= 5; $i++) {
                    if ($item['dmg_min' . $i] <= 0 || $item['dmg_max' . $i] <= 0) {
                        continue;
                    }

                    $item['dmg_list'][] = weaponDamage($item['dmg_min' . $i], $item['dmg_max' . $i], $item['dmg_type' . $i], $i);

                    $dmg_min += $item['dmg_min' . $i];
                    $dmg_max += $item['dmg_max' . $i];
                }
                $item['dps'] = weaponDPS($dmg_min, $dmg_max, $item['delay']);
            }

            $item['stats'] = [];
            for ($j = 1; $j <= 10; $j++) {
                if ($item['stat_type' . $j] < 0 || ! $item['stat_value' . $j]) { //val can be negative
                    continue;
                }

                $item['stats'][] = itemStat($item['stat_type' . $j], $item['stat_value' . $j]);
            }

            $resistance_list = [
                'holy_res',
                'fire_res',
                'nature_res',
                'frost_res',
                'shadow_res',
                'arcane_res'
            ];

            $item['resistances'] = [];
            foreach ($resistance_list as $key => $resistance) {
                if ($resistance && $item[$resistance] != 0) {
                    $item['resistances'][] = itemResistance($item[$resistance], $key);
                }
            }
            $item['allowed_classes'] = getAllowableClass($item['AllowableClass']);
            $item['allowed_races']   = getAllowableRace($item['AllowableRace']);
            $item['trigger_text']    = $config['trigger'] = ["Use: ", "Equip: ", "Chance on hit: ", "", "", "", ""];

            $item['item_spells_trigger'] = [];
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
                    if ($ppm = $item['spellppmRate_' . $k]) {
                        $extra[] = sprintf('%s procs per minute', $ppm);
                    }
                }

                $item['item_spells_trigger'][$item['spellid_' . $k]] = [$item['spelltrigger_' . $k], $extra ? ' (' . implode(', ', $extra) . ')' : ''];
            }

            // ERROR IS HERE SOMEWHERE:::
            // Item Set
            $pieces = [];
            if ($item['itemset'] && $this->config->item('item_set')[$item['itemset']]) {
                $item['itemset_set_name'] = $this->config->item('item_set')[$item['itemset']][0];

                for ($i = 0; $i < 10; $i++) {
                    if ($this->config->item('item_set')[$item['itemset']][1][$i]) {
                        $item['itemset_item_list'][$this->config->item('item_set')[$item['itemset']][1][$i]]
                            = $this->Database_model->getItemName($this->config->item('item_set')[$item['itemset']][1][$i], $realmid);
                    }
                }

                $set_key   = [];
                $set_spell = [];
                for ($j = 0; $j < 8; $j++) {
                    if ($this->config->item('item_set')[$item['itemset']][2][$j]) {
                        $set_spell[] = [
                            $this->Database_model->getSpellDetails($this->config->item('item_set')[$item['itemset']][2][$j],$realmid),
                            $this->config->item('item_set')[$item['itemset']][2][$j]
                        ];
                    }

                    if ($this->config->item('item_set')[$item['itemset']][3][$j]) {
                        $set_key[] = $this->config->item('item_set')[$item['itemset']][3][$j];
                    }
                }

                $tmp = array_combine($set_key, $set_spell);
                unset($i, $j, $set_key, $set_spell);
                ksort($tmp);

                if ($tmp) 
                {
                    foreach ($tmp as $sid => $spell) 
                    {
                        $item['itemset_set_list'][$sid] = $spell;
                    }
                }
                unset($tmp);
            }


            //$item['patch_list']  = $this->Database_model->getPatchList($entry);
            $item['obtainable']  = (bool)($item['ExtraFlags'] & 0x04);
            $item['openable']    = (bool)($item['Flags'] & 0x00000004);
            $item['partyloot']   = (bool)($item['Flags'] & 0x00000800);
            $item['rtduration']  = (bool)($item['Flags'] & 0x00010000);

            //Dropped by, Pickpocket-ed, Skinned
            $item['creature_list'] = $this->Database_model->getCreatureRelatedList($entry, $realmid);
            // GO List (gathered, mined, contained)
            $item['go_list'] = $this->Database_model->getGORelatedList($entry, $realmid);
            // Fished in
            $item['fished_list'] = $this->Database_model->getFishedInList($entry, $realmid);
            // Contains, Disenchanted
            $item['item_list'] = $this->Database_model->getItemRelatedList($entry, $realmid);
            // Contains (this item contains those items)
            $contains = $this->Database_model->getContainsList($entry, $realmid);
            if ($contains) {
                $item['item_list']['contains'] = $contains;
            }
            // Disenchant List (output of disenchant)
            if ((int)$item['DisenchantID'] > 0) {
                $item['item_list']['disenchanting'] = $this->Database_model->getDisenchantList($item['DisenchantID'], $realmid);
            }
            // Reward from
            $item['reward_list'] = $this->Database_model->getRewardList($entry, $realmid);
            // Sold by
            $item['vendor_list'] = $this->Database_model->getVendorList($entry,$realmid);
            // Starts Quest
            $item['start_q_info'] = [];
            if ($item['startquest']) {
                $item['start_q_info'] = $this->Database_model->getQuest($item['startquest'], $realmid);

                for ($j = 1; $j <= 4; ++$j) {
                    if (($item['start_q_info']['RewItemId' . $j] != 0) && ($item['start_q_info']['RewItemCount' . $j] != 0)) {
                        $item['start_q_info']['itemrewards'][] = array_merge(
                            ['entry' => (int)$item['start_q_info']['RewItemId' . $j], 'icon' => $this->Database_model->getIconName($item['start_q_info']['RewItemId' . $j], $realmid)],
                            array('count' => (int)$item['start_q_info']['RewItemCount' . $j])
                        );
                    }
                }
            }
            // Additional data - end

            $data = [
                'item'      => $item,
                'realmid'     => $realmid,
                'pagetitle' => 'Item > ' . $item['name'],
                'lang'      => $this->lang->lang(),
                'realms'    => $this->wowrealm->getRealms()->result(),
            ];
        } else {
            $data = [
                'item'      => false,
                'lang'      => $this->lang->lang(),
                'pagetitle' => 'Item not found',

            ];
        }
        $this->template->build('item', $data);
    }

    public function spell(int $entry = 0, int $realmid)
    {
        if ($entry <= 0) {
            redirect(base_url(), 'refresh');
        }

        if (! empty($realmid) && $realmid > 10) {
            redirect(base_url('404'), 'refresh');
        }

        $this->load->model('Armory/armory_model');
        $spell = $this->Database_model->getSpell($entry, $realmid);
        $this->load->config("shared_dbc");
        $this->load->config("shared_dbc_enchants");

        if ($spell) {
            // Additional data - start
            $spell['icon']     = $this->config->item('spell_icons')[$spell['SpellIconID']] ?? 'Trade_Engineering';
            $spell['range']    = spellRange($this->config->item('range_index')[$spell['RangeIndex']]);
            $spell['range_t']  = spellRange($this->config->item('range_index')[$spell['RangeIndex']], true);
            $spell['cost']     = spellPowerCost($spell['PowerType'], $spell['ManaCost'], $spell['ManaCostPerlevel'], $spell['ManaPerSecond']);
            $spell['cast']     = spellCastTime($this->config->item('cast_index')[$spell['CastingTimeIndex']], $spell['AttributesEx'], $spell['PowerType']);
            $spell['cooldown'] = spellCD($spell['RecoveryTime']);
            $spell['cat']      = $this->config->item('spell_categories')[$spell['Category']] ?? '';
            $spell['gcd']      = formatTime($spell['StartRecoveryTime']);
            $spell['gcd_cat']  = $this->config->item('spell_categories')[$spell['StartRecoveryCategory']] ?? '';

            // Duration
            $spell['duration'] = '';
            if ($spell['DurationIndex']) {
                $spell['duration'] = $this->config->item('duration')[$spell['DurationIndex']];
                if ($spell['duration'] < 0) {
                    $spell['duration'] = 'Until cancelled'; //or n/a
                } else {
                    $spell['duration'] = formatTime($spell['duration']);
                }
            }

            // Effects
            $spell['effects'] = [];
            for ($i = 1; $i < 4; $i++) {
                $effect = (int)$spell['Effect' . $i];

                if (! $effect) {
                    continue;
                }

                $spell['effects'][$i - 1] = []; //default

                $aura      = (int)$spell['EffectApplyAuraName' . $i];
                $trigger   = (int)$spell['EffectTriggerSpell' . $i];
                $item      = (int)$spell['EffectItemType' . $i];
                $misc      = (int)$spell['EffectMiscValue' . $i];
                $base      = (int)$spell['EffectBasePoints' . $i];
                $radius    = (int)$spell['EffectRadiusIndex' . $i];
                $amplitude = (int)$spell['EffectAmplitude' . $i];
                $mechanic  = (int)$spell['EffectMechanic' . $i];

                // Where we don't want value to be displayed
                if (in_array($aura, [11, 12, 36, 77]) || $effect == 132) {
                    $value = '';
                } else {
                    if ($spell['EffectDieSides' . $i] > 1) {
                        $value = $base + 1 . ' to ' . ($base + $spell['EffectDieSides' . $i]);
                    } else {
                        $value = $base + 1;
                    }
                }

                $spell['effects'][$i - 1] = [
                    'id'       => $effect,
                    'type'     => 0, //default                                    //if it's not aura, check effect attributes and add if exists
                    'eff_name' => $this->config->item('effect_names')[$effect] . (($value || $misc) && effectAttributes($effect, $misc, $realmid) !== null ? ': (' . effectAttributes($effect, $misc, $realmid) . ') ' : ''),
                    'value'    => $value,
                    'radius'   => $this->config->item('radius')[$radius] ?? '',
                    'interval' => $amplitude ? formatTime($amplitude) : '',
                    'mechanic' => $this->config->item('spell_mechanics')[$mechanic] ?? ''
                ];

                // Enchant
                if (in_array($effect, [53, 54])) {
                    $value = $this->config->item('enchants')[$misc] ?? '';

                    $spell['effects'][$i - 1]['type']  = 1;
                    $spell['effects'][$i - 1]['value'] = $value;
                }

                // Auras
                if ($aura) {
                    $aura_d = [
                        'a_id'    => $aura,
                        'type'    => 2,
                        'eff_val' => auraAttributes($aura, $misc, $realmid),
                        'a_name'  => $this->config->item('aura_names')[$aura],
                    ];

                    foreach ($aura_d as $key => $data) {
                        $spell['effects'][$i - 1][$key] = $data;
                    }
                }

                // Triggers
                if ($trigger) {
                    $trigger_spell = $this->Database_model->getSpell($trigger, $realmid, 'SpellName, SpellIconID');
                    $trigger       = [
                        'id'    => $trigger,
                        'type'  => 3,
                        'name'  => $trigger_spell['SpellName'],
                        'icon'  => $this->config->item('spell_icons')[$trigger_spell['spellIconId']] ?? 'Trade_Engineering',
                    ];

                    foreach ($trigger as $key => $data) {
                        $spell['effects'][$i - 1][$key] = $data;
                    }
                }

                // Creates
                if ($item && $aura === 86) {
                    $created_item = $this->Database_model->getItem($item, $realmid);
                    $item         = [
                        'id'      => $item,
                        'type'    => 4,
                        'name'    => $created_item['name'],
                        'Quality' => $created_item['Quality'],
                        'icon'    => $this->Database_model->getIconName($item, $realmid),
                    ];

                    foreach ($item as $key => $data) {
                        $spell['effects'][$i - 1][$key] = $data;
                    }
                }

                // Affects (check spell 20937 for example)
                if (in_array($aura, [107, 108, 109, 112])) {
                    $affected_spells = $this->Database_model->getAffectedSpellList($entry, $realmid);
                    if ($affected_spells) {
                        foreach ($affected_spells as $key => $data) {
                            $affected_spells[$key]['icon'] = $this->config->item('spell_icons')[$data['spellIconId']] ?? 'Trade_Engineering';
                        }

                        $affected = [
                            'type' => 5,
                            'list' => $affected_spells
                        ];

                        foreach ($affected as $key => $data) {
                            $spell['effects'][$i - 1][$key] = $data;
                        }
                    }
                }
            }
            unset($effect, $aura, $trigger, $trigger_spell, $misc, $base, $radius, $amplitude, $mechanic, $key, $data, $value);

            // Flags
            $masks          = getMasks();
            $spell['Flags'] = [];

            for ($i = 0; $i < 4; $i++) {
                $attribute = $spell['Attributes' . ($i ? ($i === 1 ? 'Ex' : 'Ex' . $i) : '')];
                if (! $attribute) {
                    continue;
                }

                foreach ($masks as $key => $mask) {
                    if ($attribute & $mask) {
                        $spell['Flags'][] = $this->config->item('attributes')[$i][$key];
                    }
                }
            }

            //Mechanics
            $spell['mechanics'] = $this->config->item('spell_mechanics')[$spell['Mechanic']] ?? '';
            for ($i = 1; $i <= 2; $i++) {
                if (! $spell['EffectMechanic' . $i]) {
                    continue;
                }
                if (strpos($spell['mechanics'], $this->config->item('spell_mechanics')[$spell['EffectMechanic' . $i]]) === false) {
                    $spell['mechanics'] .= (empty($spell['mechanics']) ? '' : ', ') . $this->config->item('spell_mechanics')[$spell['EffectMechanic' . $i]];
                }
            }

            $spell['school_name'] = schoolType($spell['School']);
            $spell['dispel_type'] = dispelType($spell['Dispel']);
            $spell['desc']        = $this->Database_model->getSpellDetails($spell['Id'], $realmid);

            //Tools
            $spell['tools'] = [];
            for ($i = 1; $i <= 2; $i++) {
                // Tools
                if (! $spell['Totem' . $i]) {
                    continue;
                }

                $spell['tools'][$i - 1] = [
                    'id'      => $spell['Totem' . $i],
                    'name'    => $this->Database_model->getItemName($spell['Totem' . $i], $realmid),
                    'icon'    => $this->Database_model->getIconName($spell['Totem' . $i], $realmid),
                    'quality' => $this->armory_model->getCharEquipmentQualityPatch($spell['Totem' . $i], $realmid)['quality']
                ];
            }

            //Reagents
            $spell['reagents'] = [];

            for ($i = 1; $i <= 8; $i++) {
                if ($spell['Reagent' . $i] > 0 && $spell['ReagentCount' . $i]) {
                    $spell['reagents'][$i - 1] = [
                        'id'      => $spell['Reagent' . $i],
                        'count'   => $spell['ReagentCount' . $i],
                        'name'    => $this->Database_model->getItemName($spell['Reagent' . $i], $realmid),
                        'icon'    => $this->Database_model->getIconName($spell['Reagent' . $i], $realmid),
                        'quality' => $this->armory_model->getCharEquipmentQualityPatch($spell['Reagent' . $i], $realmid)['quality']
                    ];
                }
            }
            // Additional data - end

            $data = [
                'spell'     => $spell,
                'patch'     => $realmid,
                'pagetitle' => 'Spell > ' . $spell['SpellName'],
                'lang'      => $this->lang->lang(),
                'realms'    => $this->wowrealm->getRealms()->result(),
            ];

            $this->template->build('spell', $data);
        } else {
            $data = [
                'spell'     => false,
                'pagetitle' => 'Spell not found',

            ];

            $this->template->build('spell', $data);
        }
    }
}
